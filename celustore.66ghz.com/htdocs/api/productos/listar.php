<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../../config/db.php';

$conn = conectar();

$busqueda  = trim($_GET['busqueda'] ?? '');
$categoria = intval($_GET['categoria'] ?? 0);
$precioMin = floatval($_GET['precio_min'] ?? 0);
$precioMax = floatval($_GET['precio_max'] ?? 99999999);

// Construir SQL con valores directos para evitar problemas con bind_param
$sql = "SELECT p.id, p.nombre, p.marca, p.modelo, p.precio, p.stock, p.imagen,
               c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.precio BETWEEN $precioMin AND $precioMax";

if (!empty($busqueda)) {
    $b    = $conn->real_escape_string($busqueda);
    $sql .= " AND (p.nombre LIKE '%$b%' OR p.marca LIKE '%$b%' OR p.modelo LIKE '%$b%')";
}

if ($categoria > 0) {
    $sql .= " AND p.categoria_id = $categoria";
}

$sql .= " ORDER BY p.id DESC";

$resultado = $conn->query($sql);

$productos = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
}

echo json_encode([
    'success'   => true,
    'productos' => $productos,
    'total'     => count($productos)
]);

$conn->close();
?>