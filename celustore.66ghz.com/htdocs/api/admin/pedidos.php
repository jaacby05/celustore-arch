<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

$conn   = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

// ── LISTAR TODOS LOS PEDIDOS ──────────────────────────────────
if ($metodo === 'GET') {
    $resultado = $conn->query(
        'SELECT o.*, u.nombre AS cliente, u.email
         FROM ordenes o
         JOIN usuarios u ON o.usuario_id = u.id
         ORDER BY o.fecha DESC'
    );

    $pedidos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $pedidos[] = $fila;
    }

    echo json_encode(['success' => true, 'pedidos' => $pedidos]);
}

// ── ACTUALIZAR ESTADO DE PEDIDO ───────────────────────────────
elseif ($metodo === 'PUT') {
    $datos  = json_decode(file_get_contents('php://input'), true);
    $id     = intval($datos['id'] ?? 0);
    $estado = trim($datos['estado'] ?? '');

    $estados_validos = ['pendiente', 'en_preparacion', 'en_camino', 'entregado', 'cancelado'];

    if ($id <= 0 || !in_array($estado, $estados_validos)) {
        echo json_encode(['error' => 'Datos invalidos']);
        exit;
    }

    $stmt = $conn->prepare('UPDATE ordenes SET estado = ? WHERE id = ?');
    $stmt->bind_param('si', $estado, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Estado actualizado']);
    } else {
        echo json_encode(['error' => 'Error al actualizar estado']);
    }

    $stmt->close();
}

$conn->close();
?>