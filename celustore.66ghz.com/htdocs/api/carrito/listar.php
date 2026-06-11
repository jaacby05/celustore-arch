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

$stmt = $conn->prepare(
    'SELECT c.id, c.cantidad, p.id AS producto_id, p.nombre, p.marca,
            p.precio, p.imagen, p.stock
     FROM carrito c
     JOIN productos p ON c.producto_id = p.id
     WHERE c.usuario_id = ?'
);
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$items    = [];
$subtotal = 0;

while ($fila = $resultado->fetch_assoc()) {
    $fila['subtotal'] = $fila['precio'] * $fila['cantidad'];
    $subtotal        += $fila['subtotal'];
    $items[]          = $fila;
}

$impuesto = round($subtotal * 0.21, 2); // IVA 21%
$total    = round($subtotal + $impuesto, 2);

echo json_encode([
    'success'   => true,
    'items'     => $items,
    'subtotal'  => $subtotal,
    'impuesto'  => $impuesto,
    'total'     => $total
]);

$stmt->close();
$conn->close();
?>