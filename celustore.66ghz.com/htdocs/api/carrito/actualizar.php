<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Debes iniciar sesion']);
    exit;
}

$datos      = json_decode(file_get_contents('php://input'), true);
$carrito_id = intval($datos['carrito_id'] ?? 0);
$cantidad   = intval($datos['cantidad'] ?? 0);
$usuario_id = $_SESSION['usuario_id'];

if ($carrito_id <= 0 || $cantidad <= 0) {
    echo json_encode(['error' => 'Datos invalidos']);
    exit;
}

$conn = conectar();

// Verificar stock disponible
$stmt = $conn->prepare(
    'SELECT p.stock FROM carrito c
     JOIN productos p ON c.producto_id = p.id
     WHERE c.id = ? AND c.usuario_id = ?'
);
$stmt->bind_param('ii', $carrito_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

if ($resultado->num_rows === 0) {
    echo json_encode(['error' => 'Item no encontrado']);
    $conn->close();
    exit;
}

$item = $resultado->fetch_assoc();

if ($cantidad > $item['stock']) {
    echo json_encode(['error' => 'Stock insuficiente. Disponible: ' . $item['stock']]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare('UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?');
$stmt->bind_param('iii', $cantidad, $carrito_id, $usuario_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'mensaje' => 'Cantidad actualizada']);
$conn->close();
?>