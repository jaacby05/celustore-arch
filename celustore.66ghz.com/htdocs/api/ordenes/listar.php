<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Debes iniciar sesion']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$conn       = conectar();

// Listar órdenes del cliente
$stmt = $conn->prepare(
    'SELECT o.id, o.numero_orden, o.total, o.estado,
            o.metodo_pago, o.domicilio_envio, o.fecha
     FROM ordenes o
     WHERE o.usuario_id = ?
     ORDER BY o.fecha DESC'
);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

$ordenes = [];
while ($orden = $resultado->fetch_assoc()) {
    // Buscar detalle de cada orden
    $stmt2 = $conn->prepare(
        'SELECT od.cantidad, od.precio_unitario, p.nombre, p.imagen
         FROM orden_detalle od
         JOIN productos p ON od.producto_id = p.id
         WHERE od.orden_id = ?'
    );
    $stmt2->bind_param('i', $orden['id']);
    $stmt2->execute();
    $detalle = $stmt2->get_result();
    $stmt2->close();

    $orden['productos'] = [];
    while ($item = $detalle->fetch_assoc()) {
        $orden['productos'][] = $item;
    }

    $ordenes[] = $orden;
}

echo json_encode(['success' => true, 'ordenes' => $ordenes]);
$conn->close();
?>