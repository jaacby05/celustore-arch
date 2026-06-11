<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

$conn = conectar();

// Detalle de un usuario con sus pedidos
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare(
        'SELECT id, numero_orden, total, estado, fecha
         FROM ordenes
         WHERE usuario_id = ?
         ORDER BY fecha DESC'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

    $pedidos = [];
    while ($fila = $res->fetch_assoc()) {
        $pedidos[] = $fila;
    }

    echo json_encode(['success' => true, 'pedidos' => $pedidos]);
    $conn->close();
    exit;
}

// Listar todos los clientes con stats
$resultado = $conn->query(
    'SELECT u.id, u.nombre, u.email, u.fecha_registro,
            COUNT(o.id)        AS total_pedidos,
            COALESCE(SUM(o.total), 0) AS total_gastado
     FROM usuarios u
     LEFT JOIN ordenes o ON u.id = o.usuario_id
     WHERE u.rol = "cliente"
     GROUP BY u.id
     ORDER BY u.fecha_registro DESC'
);

$usuarios = [];
while ($fila = $resultado->fetch_assoc()) {
    $usuarios[] = $fila;
}

echo json_encode(['success' => true, 'usuarios' => $usuarios]);
$conn->close();
?>