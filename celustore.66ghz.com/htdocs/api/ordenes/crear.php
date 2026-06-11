<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';
require_once '../../libs/PHPMailer/PHPMailer.php';
require_once '../../libs/PHPMailer/SMTP.php';
require_once '../../libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Debes iniciar sesion']);
    exit;
}
if ($_SESSION['usuario_rol'] === 'admin') {
    echo json_encode(['error' => 'Los administradores no pueden realizar compras']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$datos             = json_decode(file_get_contents('php://input'), true);
$domicilio_envio   = trim($datos['domicilio_envio']   ?? '');
$telefono_contacto = trim($datos['telefono_contacto'] ?? '');
$tipo_envio        = trim($datos['tipo_envio']        ?? 'estandar');
$costo_envio       = floatval($datos['costo_envio']   ?? 0);
$metodo_pago       = trim($datos['metodo_pago']       ?? '');
$usuario_id        = $_SESSION['usuario_id'];

if (empty($domicilio_envio) || empty($telefono_contacto) || empty($metodo_pago)) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}
if (!in_array($metodo_pago, ['tarjeta', 'transferencia'])) {
    echo json_encode(['error' => 'Metodo de pago invalido']);
    exit;
}

$conn = conectar();

// Obtener datos del usuario
$stmtUser = $conn->prepare('SELECT nombre, email FROM usuarios WHERE id = ?');
$stmtUser->bind_param('i', $usuario_id);
$stmtUser->execute();
$resUser  = $stmtUser->get_result();
$usuario  = $resUser->fetch_assoc();
$stmtUser->close();

// Obtener items del carrito
$stmt = $conn->prepare(
    'SELECT c.id AS carrito_id, c.cantidad, p.id AS producto_id,
            p.nombre, p.precio, p.stock, p.imagen
     FROM carrito c
     JOIN productos p ON c.producto_id = p.id
     WHERE c.usuario_id = ?'
);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

if ($resultado->num_rows === 0) {
    echo json_encode(['error' => 'El carrito esta vacio']);
    $conn->close();
    exit;
}

$items    = [];
$subtotal = 0;
while ($fila = $resultado->fetch_assoc()) {
    if ($fila['cantidad'] > $fila['stock']) {
        echo json_encode(['error' => 'Stock insuficiente para: ' . $fila['nombre']]);
        $conn->close();
        exit;
    }
    $fila['subtotal'] = $fila['precio'] * $fila['cantidad'];
    $subtotal        += $fila['subtotal'];
    $items[]          = $fila;
}

$total        = round($subtotal + $costo_envio, 2);
$numero_orden = 'CS-' . strtoupper(uniqid());
$fecha        = date('d/m/Y H:i');

$tiposEnvio = [
    'estandar' => 'Envío Estándar (5-7 días)',
    'express'  => 'Envío Express (1-2 días)',
    'retiro'   => 'Retiro en local',
    'gratis'   => 'Envío gratis',
];
$tipoEnvioTexto  = $tiposEnvio[$tipo_envio] ?? $tipo_envio;
$metodoPagoTexto = $metodo_pago === 'tarjeta' ? '💳 Tarjeta de crédito/débito' : '🏦 Transferencia bancaria';

$conn->begin_transaction();
try {
    // Guardar orden incluyendo tipo_envio
    $stmt = $conn->prepare(
        'INSERT INTO ordenes (usuario_id, total, costo_envio, tipo_envio, domicilio_envio, telefono_contacto, metodo_pago, numero_orden)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('iddsssss', $usuario_id, $total, $costo_envio, $tipo_envio, $domicilio_envio, $telefono_contacto, $metodo_pago, $numero_orden);
    $stmt->execute();
    $orden_id = $conn->insert_id;
    $stmt->close();

    foreach ($items as $item) {
        $stmt = $conn->prepare(
            'INSERT INTO orden_detalle (orden_id, producto_id, cantidad, precio_unitario)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('iiid', $orden_id, $item['producto_id'], $item['cantidad'], $item['precio']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('UPDATE productos SET stock = stock - ? WHERE id = ?');
        $stmt->bind_param('ii', $item['cantidad'], $item['producto_id']);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare('DELETE FROM carrito WHERE usuario_id = ?');
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // Enviar factura por email
    enviarFactura(
        $usuario['email'],
        $usuario['nombre'],
        $numero_orden,
        $fecha,
        $items,
        $subtotal,
        $costo_envio,
        $total,
        $domicilio_envio,
        $telefono_contacto,
        $tipoEnvioTexto,
        $metodoPagoTexto
    );

    echo json_encode([
        'success'      => true,
        'mensaje'      => 'Orden creada correctamente',
        'numero_orden' => $numero_orden,
        'orden_id'     => $orden_id,
        'total'        => $total
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Error al procesar la orden. Intenta nuevamente.']);
}
$conn->close();

function enviarFactura($email, $nombre, $numero_orden, $fecha, $items, $subtotal, $costo_envio, $total, $domicilio, $telefono, $tipoEnvio, $metodoPago) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jaacby05@gmail.com';
        $mail->Password   = 'yxqbljxloffsewhc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('jaacby05@gmail.com', 'CeluStore');
        $mail->addAddress($email, $nombre);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = "✅ Compra confirmada — Orden {$numero_orden} | CeluStore";

        $filasProductos = '';
        foreach ($items as $item) {
            $precioFormateado   = '$' . number_format($item['precio'],   0, ',', '.');
            $subtotalFormateado = '$' . number_format($item['subtotal'], 0, ',', '.');
            $filasProductos .= "
                <tr>
                    <td style='padding:12px; border-bottom:1px solid #2a2a4a; color:#e0e0e0'>{$item['nombre']}</td>
                    <td style='padding:12px; border-bottom:1px solid #2a2a4a; text-align:center; color:#e0e0e0'>{$item['cantidad']}</td>
                    <td style='padding:12px; border-bottom:1px solid #2a2a4a; text-align:right; color:#e0e0e0'>{$precioFormateado}</td>
                    <td style='padding:12px; border-bottom:1px solid #2a2a4a; text-align:right; color:#6c63ff; font-weight:700'>{$subtotalFormateado}</td>
                </tr>";
        }

        $subtotalFormateado = '$' . number_format($subtotal,    0, ',', '.');
        $envioFormateado    = $costo_envio > 0 ? '$' . number_format($costo_envio, 0, ',', '.') : 'Gratis';
        $totalFormateado    = '$' . number_format($total,       0, ',', '.');
        $colorEnvio         = $costo_envio > 0 ? '#e0e0e0' : '#4caf50';

        $mail->Body = "
        <div style='font-family:Arial,sans-serif; background:#0f0f1a; padding:20px; min-height:100vh'>
            <div style='max-width:600px; margin:0 auto'>
                <div style='background:linear-gradient(135deg,#6c63ff,#4834d4); border-radius:12px 12px 0 0; padding:30px; text-align:center'>
                    <h1 style='color:white; margin:0; font-size:28px'>📱 CeluStore</h1>
                    <p style='color:rgba(255,255,255,0.85); margin:8px 0 0; font-size:16px'>Confirmación de compra</p>
                </div>
                <div style='background:#1a1a2e; padding:30px; border-radius:0 0 12px 12px; border:1px solid #2a2a4a'>
                    <p style='color:#e0e0e0; font-size:16px; margin-top:0'>
                        Hola <strong style='color:#6c63ff'>{$nombre}</strong>, ¡gracias por tu compra!
                    </p>
                    <div style='background:#0f0f1a; border:2px solid #6c63ff; border-radius:10px; padding:16px; text-align:center; margin:20px 0'>
                        <p style='color:#aaa; margin:0 0 6px; font-size:13px; text-transform:uppercase; letter-spacing:1px'>Número de orden</p>
                        <span style='color:#6c63ff; font-size:22px; font-weight:800; letter-spacing:3px'>{$numero_orden}</span>
                        <p style='color:#aaa; margin:6px 0 0; font-size:12px'>{$fecha}</p>
                    </div>
                    <h3 style='color:#e0e0e0; border-bottom:1px solid #2a2a4a; padding-bottom:10px; font-size:15px'>🛒 Detalle del pedido</h3>
                    <table style='width:100%; border-collapse:collapse; margin-bottom:20px'>
                        <thead>
                            <tr style='background:#0f0f1a'>
                                <th style='padding:10px 12px; text-align:left; color:#aaa; font-size:12px; text-transform:uppercase'>Producto</th>
                                <th style='padding:10px 12px; text-align:center; color:#aaa; font-size:12px; text-transform:uppercase'>Cant.</th>
                                <th style='padding:10px 12px; text-align:right; color:#aaa; font-size:12px; text-transform:uppercase'>Precio</th>
                                <th style='padding:10px 12px; text-align:right; color:#aaa; font-size:12px; text-transform:uppercase'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>{$filasProductos}</tbody>
                    </table>
                    <div style='background:#0f0f1a; border-radius:10px; padding:16px; margin-bottom:20px'>
                        <div style='display:flex; justify-content:space-between; padding:6px 0; color:#aaa; font-size:14px'>
                            <span>Subtotal</span><span style='color:#e0e0e0'>{$subtotalFormateado}</span>
                        </div>
                        <div style='display:flex; justify-content:space-between; padding:6px 0; color:#aaa; font-size:14px'>
                            <span>Envío ({$tipoEnvio})</span>
                            <span style='color:{$colorEnvio}'>{$envioFormateado}</span>
                        </div>
                        <div style='display:flex; justify-content:space-between; padding:12px 0 6px; border-top:1px solid #2a2a4a; margin-top:6px'>
                            <span style='color:#e0e0e0; font-size:16px; font-weight:700'>Total</span>
                            <span style='color:#6c63ff; font-size:20px; font-weight:800'>{$totalFormateado}</span>
                        </div>
                    </div>
                    <h3 style='color:#e0e0e0; border-bottom:1px solid #2a2a4a; padding-bottom:10px; font-size:15px'>📦 Datos de envío</h3>
                    <table style='width:100%; margin-bottom:20px'>
                        <tr><td style='padding:6px 0; color:#aaa; font-size:14px; width:40%'>Dirección</td><td style='padding:6px 0; color:#e0e0e0; font-size:14px'>{$domicilio}</td></tr>
                        <tr><td style='padding:6px 0; color:#aaa; font-size:14px'>Teléfono</td><td style='padding:6px 0; color:#e0e0e0; font-size:14px'>{$telefono}</td></tr>
                        <tr><td style='padding:6px 0; color:#aaa; font-size:14px'>Tipo de envío</td><td style='padding:6px 0; color:#e0e0e0; font-size:14px'>{$tipoEnvio}</td></tr>
                        <tr><td style='padding:6px 0; color:#aaa; font-size:14px'>Método de pago</td><td style='padding:6px 0; color:#e0e0e0; font-size:14px'>{$metodoPago}</td></tr>
                    </table>
                    <div style='background:#0f0f1a; border-radius:10px; padding:16px; text-align:center'>
                        <p style='color:#aaa; font-size:13px; margin:0 0 8px'>Podés hacer seguimiento de tu pedido en:</p>
                        <a href='https://celustore.infinityfreeapp.com/mis-pedidos.php'
                           style='background:#6c63ff; color:white; padding:10px 24px; border-radius:8px;
                                  text-decoration:none; font-weight:700; font-size:14px; display:inline-block'>
                            Ver mis pedidos
                        </a>
                        <p style='color:#555; font-size:12px; margin:16px 0 0'>
                            Este email fue enviado automáticamente por CeluStore.<br>
                            Por favor no respondas a este mensaje.
                        </p>
                    </div>
                </div>
            </div>
        </div>";

        $mail->AltBody = "Hola {$nombre}, tu compra fue confirmada. Orden: {$numero_orden}. Total: {$totalFormateado}.";
        $mail->send();
    } catch (Exception $e) {
        error_log('Error enviando factura: ' . $e->getMessage());
    }
}
?>