<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../../config/db.php';

$conn = conectar();
$resultado = $conn->query('SELECT * FROM categorias ORDER BY nombre');

$categorias = [];
while ($fila = $resultado->fetch_assoc()) {
    $categorias[] = $fila;
}

echo json_encode(['success' => true, 'categorias' => $categorias]);
$conn->close();
?>