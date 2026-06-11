<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'ID de producto invalido']);
    exit;
}

$conn = conectar();

$stmt = $conn->prepare(
    'SELECT p.*, c.nombre AS categoria
     FROM productos p
     LEFT JOIN categorias c ON p.categoria_id = c.id
     WHERE p.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['error' => 'Producto no encontrado']);
    $stmt->close();
    $conn->close();
    exit;
}

$producto = $resultado->fetch_assoc();

// Decodificar especificaciones e imágenes guardadas como JSON
$producto['especificaciones'] = $producto['especificaciones']
    ? json_decode($producto['especificaciones'], true)
    : [];

$producto['imagenes'] = $producto['imagenes']
    ? json_decode($producto['imagenes'], true)
    : [];

// Si tiene imagen principal y no está en el array de imágenes, agregarla
if ($producto['imagen'] && !in_array($producto['imagen'], $producto['imagenes'])) {
    array_unshift($producto['imagenes'], $producto['imagen']);
}

echo json_encode(['success' => true, 'producto' => $producto]);

$stmt->close();
$conn->close();
?>