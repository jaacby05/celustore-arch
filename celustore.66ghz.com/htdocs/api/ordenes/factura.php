<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/db.php';
require_once '../../libs/tcpdf/tcpdf.php';

$orden_id   = intval($_GET['orden_id'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

if ($orden_id <= 0) die('Orden no válida');

$conn = conectar();

$stmt = $conn->prepare(
    'SELECT o.*, u.nombre AS usuario_nombre, u.email AS usuario_email
     FROM ordenes o
     JOIN usuarios u ON o.usuario_id = u.id
     WHERE o.id = ? AND o.usuario_id = ?'
);
$stmt->bind_param('ii', $orden_id, $usuario_id);
$stmt->execute();
$res   = $stmt->get_result();
$orden = $res->fetch_assoc();
$stmt->close();

if (!$orden) die('Orden no encontrada');

$stmt = $conn->prepare(
    'SELECT od.cantidad, od.precio_unitario, p.nombre
     FROM orden_detalle od
     JOIN productos p ON od.producto_id = p.id
     WHERE od.orden_id = ?'
);
$stmt->bind_param('i', $orden_id);
$stmt->execute();
$resProductos = $stmt->get_result();
$productos    = [];
while ($fila = $resProductos->fetch_assoc()) {
    $productos[] = $fila;
}
$stmt->close();
$conn->close();

$fecha           = date('d/m/Y', strtotime($orden['fecha']));
$tiposEnvio      = [
    'estandar' => 'Envío Estándar (5-7 días)',
    'express'  => 'Envío Express (1-2 días)',
    'retiro'   => 'Retiro en local',
    'gratis'   => 'Envío gratis',
];
$tipoEnvioTexto  = $tiposEnvio[$orden['tipo_envio'] ?? 'estandar'] ?? 'Estándar';
$metodoPagoTexto = $orden['metodo_pago'] === 'tarjeta' ? 'Tarjeta de crédito/débito' : 'Transferencia bancaria';
$subtotal        = $orden['total'] - floatval($orden['costo_envio']);
$subtotalGravado = round($subtotal / 1.21, 2);
$iva             = round($subtotal - $subtotalGravado, 2);
$nroFactura      = str_pad($orden_id, 8, '0', STR_PAD_LEFT);
$caeNum          = rand(10000000000000, 99999999999999);
$caeFecha        = date('d/m/Y', strtotime('+10 days'));

$empresa = [
    'nombre'    => 'CeluStore S.A.',
    'direccion' => 'San Lorenzo 1234 - Posadas - Misiones',
    'telefono'  => '03764739636',
    'email'     => 'jaacby05@gmail.com',
    'cuit'      => '30-71234567-8',
    'iibb'      => '30-71234567-8',
    'inicio'    => '01/01/2024',
];

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('CeluStore');
$pdf->SetAuthor('CeluStore S.A.');
$pdf->SetTitle('Factura B N°00001-' . $nroFactura);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

$negro     = [0,   0,   0  ];
$grisOsc   = [60,  60,  60 ];
$grisMedio = [120, 120, 120];
$grisClaro = [200, 200, 200];
$grisFondo = [245, 245, 245];
$blanco    = [255, 255, 255];

// ════════════════════════════════════════════════════════════
// ENCABEZADO
// Izquierda: 15-93 | Caja B: 93-117 | Derecha: 117-195
// ════════════════════════════════════════════════════════════

$pdf->SetDrawColor(...$grisClaro);
$pdf->SetLineWidth(0.5);
$pdf->Rect(15, 15, 180, 55, 'D');

$pdf->Line(93,  15, 93,  70);
$pdf->Line(117, 15, 117, 70);

// Caja B
$pdf->SetFillColor(...$blanco);
$pdf->SetDrawColor(...$grisClaro);
$pdf->Rect(93, 15, 24, 55, 'DF');

$pdf->SetFont('helvetica', 'B', 22);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(93, 24);
$pdf->Cell(24, 12, 'B', 0, 0, 'C');

$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(...$grisOsc);
$pdf->SetXY(93, 38);
$pdf->Cell(24, 5, 'COD.06', 0, 0, 'C');

// Columna izquierda: datos empresa
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(18, 18);
$pdf->Cell(72, 8, $empresa['nombre'], 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(...$grisOsc);

$datosEmpresa = [
    $empresa['direccion'],
    'Tel: ' . $empresa['telefono'],
    'Email: ' . $empresa['email'],
    'CUIT: ' . $empresa['cuit'],
    'IIBB: ' . $empresa['iibb'],
    'Inicio de Actividades: ' . $empresa['inicio'],
];

$yE = 27;
foreach ($datosEmpresa as $dato) {
    $pdf->SetXY(18, $yE);
    $pdf->Cell(72, 5, $dato, 0, 1, 'L');
    $yE += 5;
}

$pdf->SetXY(18, $yE);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(...$negro);
$pdf->Cell(72, 5, 'IVA RESPONSABLE INSCRIPTO', 0, 1, 'L');

// Columna derecha: datos factura
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(120, 18);
$pdf->Cell(72, 7, 'FACTURA B N°00001-' . $nroFactura, 0, 1, 'L');

$datosFact = [
    ['Fecha:',              $fecha],
    ['CUIT:',               $empresa['cuit']],
    ['IIBB:',               $empresa['iibb']],
    ['Inicio Actividades:', $empresa['inicio']],
    ['Razón social:',       $empresa['nombre']],
];

$yF = 27;
foreach ($datosFact as $dato) {
    $pdf->SetXY(120, $yF);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(...$grisOsc);
    $pdf->Cell(26, 5, $dato[0], 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(...$negro);
    $pdf->Cell(46, 5, $dato[1], 0, 1, 'L');
    $yF += 5;
}

// ════════════════════════════════════════════════════════════
// SECCIÓN CLIENTE Y CONDICIONES
// ════════════════════════════════════════════════════════════
$ySeccion = 75;

$pdf->SetDrawColor(...$grisClaro);
$pdf->SetLineWidth(0.5);
$pdf->Rect(15, $ySeccion, 180, 45, 'D');
$pdf->Line(105, $ySeccion, 105, $ySeccion + 45);

$pdf->SetFillColor(...$grisFondo);
$pdf->Rect(15,  $ySeccion, 90, 7, 'DF');
$pdf->Rect(105, $ySeccion, 90, 7, 'DF');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(18, $ySeccion + 1);
$pdf->Cell(84, 5, 'INFORMACION DEL CLIENTE', 0, 0, 'L');
$pdf->SetXY(108, $ySeccion + 1);
$pdf->Cell(84, 5, 'CONDICIONES DE VENTA', 0, 0, 'L');

$filasCliente = [
    ['Cliente:',   $orden['usuario_nombre']],
    ['Dirección:', mb_strimwidth($orden['domicilio_envio'], 0, 36, '...')],
    ['Teléfono:',  $orden['telefono_contacto']],
    ['Email:',     mb_strimwidth($orden['usuario_email'], 0, 30, '...')],
    ['Condición:', 'CONSUMIDOR FINAL'],
];

$yRow = $ySeccion + 9;
foreach ($filasCliente as $fila) {
    $pdf->SetXY(18, $yRow);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(...$grisOsc);
    $pdf->Cell(22, 5, $fila[0], 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(...$negro);
    $pdf->Cell(62, 5, $fila[1], 0, 1, 'L');
    $yRow += 6;
}

$filasVenta = [
    ['Condición:',       $metodoPagoTexto],
    ['Tipo:',            'Producto'],
    ['Orden de compra:', $orden['numero_orden']],
    ['Envío:',           $tipoEnvioTexto],
    ['Estado:',          ucfirst(str_replace('_', ' ', $orden['estado'] ?? 'pendiente'))],
];

$yRow = $ySeccion + 9;
foreach ($filasVenta as $fila) {
    $pdf->SetXY(108, $yRow);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(...$grisOsc);
    $pdf->Cell(26, 5, $fila[0], 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(...$negro);
    $pdf->Cell(60, 5, $fila[1], 0, 1, 'L');
    $yRow += 6;
}

// ════════════════════════════════════════════════════════════
// TABLA CONCEPTOS
// ════════════════════════════════════════════════════════════
$yTabla = $ySeccion + 50;
$pdf->SetY($yTabla);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(...$negro);
$pdf->SetTextColor(...$blanco);
$pdf->SetDrawColor(...$grisClaro);
$pdf->SetLineWidth(0.1);

$pdf->Cell(18, 7, 'Cantidad',    1, 0, 'C', true);
$pdf->Cell(28, 7, 'Código',      1, 0, 'C', true);
$pdf->Cell(72, 7, 'Descripción', 1, 0, 'C', true);
$pdf->Cell(20, 7, '% Bonif.',    1, 0, 'C', true);
$pdf->Cell(21, 7, 'P. Unitario', 1, 0, 'C', true);
$pdf->Cell(21, 7, 'Subtotal',    1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 8);
$fill = false;
foreach ($productos as $idx => $prod) {
    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
    $pdf->SetTextColor(...$negro);
    $subtotalProd = $prod['cantidad'] * $prod['precio_unitario'];
    $codigo       = str_pad($idx + 1, 8, '0', STR_PAD_LEFT);

    $pdf->Cell(18, 6, number_format($prod['cantidad'], 2, ',', '.'),              1, 0, 'C', true);
    $pdf->Cell(28, 6, $codigo,                                                     1, 0, 'C', true);
    $pdf->Cell(72, 6, $prod['nombre'],                                             1, 0, 'L', true);
    $pdf->Cell(20, 6, '0,00',                                                      1, 0, 'C', true);
    $pdf->Cell(21, 6, '$ ' . number_format($prod['precio_unitario'], 2, ',', '.'), 1, 0, 'R', true);
    $pdf->Cell(21, 6, '$ ' . number_format($subtotalProd,            2, ',', '.'), 1, 1, 'R', true);
    $fill = !$fill;
}

// ════════════════════════════════════════════════════════════
// TOTALES
// ════════════════════════════════════════════════════════════
$yTot   = $pdf->GetY() + 5;
$xLabel = 130;
$wLabel = 40;
$xVal   = 170;
$wVal   = 25;

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(...$grisOsc);
$pdf->SetXY($xLabel, $yTot);
$pdf->Cell($wLabel, 6, 'Subtotal Gravado', 0, 0, 'R');
$pdf->SetTextColor(...$negro);
$pdf->Cell($wVal, 6, '$ ' . number_format($subtotalGravado, 2, ',', '.'), 0, 1, 'R');

if ($orden['costo_envio'] > 0) {
    $pdf->SetTextColor(...$grisOsc);
    $pdf->SetX($xLabel);
    $pdf->Cell($wLabel, 6, 'Envío', 0, 0, 'R');
    $pdf->SetTextColor(...$negro);
    $pdf->Cell($wVal, 6, '$ ' . number_format($orden['costo_envio'], 2, ',', '.'), 0, 1, 'R');
}

$pdf->SetDrawColor(...$negro);
$pdf->SetLineWidth(0.5);
$yLinea = $pdf->GetY() + 1;
$pdf->Line($xLabel, $yLinea, 195, $yLinea);

$pdf->SetY($yLinea + 2);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(...$negro);
$pdf->SetX($xLabel);
$pdf->Cell($wLabel, 7, 'TOTAL', 0, 0, 'R');
$pdf->Cell($wVal,   7, '$ ' . number_format($orden['total'], 2, ',', '.'), 0, 1, 'R');

// ════════════════════════════════════════════════════════════
// IVA
// ════════════════════════════════════════════════════════════
$yIva = $pdf->GetY() + 6;
$pdf->SetFillColor(...$grisFondo);
$pdf->SetDrawColor(...$grisClaro);
$pdf->SetLineWidth(0.3);
$pdf->Rect(15, $yIva, 180, 14, 'DF');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(18, $yIva + 2);
$pdf->Cell(0, 5, 'Régimen de Transparencia Fiscal al Consumidor (Ley 27.743)', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(...$grisOsc);
$pdf->SetXY(18, $yIva + 8);
$pdf->Cell(30, 5, 'IVA CONTENIDO', 0, 0, 'L');
$pdf->SetTextColor(...$negro);
$pdf->Cell(0,  5, '$ ' . number_format($iva, 2, ',', '.'), 0, 1, 'L');

// ════════════════════════════════════════════════════════════
// OBSERVACIONES
// ════════════════════════════════════════════════════════════
$yObs = $pdf->GetY() + 5;
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(15, $yObs);
$pdf->Cell(0, 5, 'OBSERVACIONES', 0, 1, 'L');

$pdf->SetDrawColor(...$negro);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(...$grisOsc);
$pdf->SetX(15);
$pdf->Cell(0, 6, 'Comprobante generado por CeluStore para la orden ' . $orden['numero_orden'], 0, 1, 'L');

// ════════════════════════════════════════════════════════════
// CAE
// ════════════════════════════════════════════════════════════
$yCae = $pdf->GetY() + 5;
$pdf->SetFillColor(...$grisFondo);
$pdf->SetDrawColor(...$grisClaro);
$pdf->SetLineWidth(0.3);
$pdf->Rect(15, $yCae, 180, 16, 'DF');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(...$negro);
$pdf->SetXY(18, $yCae + 3);
$pdf->Cell(0, 5, 'CAE N°: ' . $caeNum, 0, 1, 'L');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(...$grisOsc);
$pdf->SetX(18);
$pdf->Cell(0, 5, 'Fecha de Vto. de CAE: ' . $caeFecha, 0, 1, 'L');

// ════════════════════════════════════════════════════════════
// FOOTER
// ════════════════════════════════════════════════════════════
$pdf->SetY(272);
$pdf->SetDrawColor(...$grisClaro);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 272, 195, 272);

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(...$grisMedio);
$pdf->SetXY(15, 275);
$pdf->Cell(180, 5, 'CeluStore S.A.  |  San Lorenzo 1234, Posadas, Misiones  |  Tel: 03764739636  |  celustore.infinityfreeapp.com', 0, 1, 'C');
$pdf->SetFont('helvetica', 'I', 7);
$pdf->Cell(180, 4, 'Comprobante generado automáticamente. Conservalo como respaldo de tu compra.', 0, 1, 'C');

$pdf->Output('Factura_' . $orden['numero_orden'] . '.pdf', 'D');
?>