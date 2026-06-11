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
$usuario_id = $_SESSION['usuario_id'];

if ($carrito_id <= 0) {
    echo json_encode(['error' => 'ID invalido']);
    exit;
}

$conn = conectar();

$stmt = $conn->prepare('DELETE FROM carrito WHERE id = ? AND usuario_id = ?');
$stmt->bind_param('ii', $carrito_id, $usuario_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado del carrito']);
$conn->close();
?>