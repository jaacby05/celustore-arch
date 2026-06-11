<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Debes iniciar sesion para agregar al carrito']);
    exit;
}

if ($_SESSION['usuario_rol'] === 'admin') {
    echo json_encode(['error' => 'Los administradores no pueden realizar compras']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$datos       = json_decode(file_get_contents('php://input'), true);
$producto_id = intval($datos['producto_id'] ?? 0);
$cantidad    = intval($datos['cantidad'] ?? 1);
$usuario_id  = $_SESSION['usuario_id'];

if ($producto_id <= 0 || $cantidad <= 0) {
    echo json_encode(['error' => 'Datos invalidos']);
    exit;
}

$conn = conectar();

$stmt = $conn->prepare('SELECT id, nombre, stock FROM productos WHERE id = ? AND activo = 1');
$stmt->bind_param('i', $producto_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['error' => 'Producto no encontrado']);
    $stmt->close();
    $conn->close();
    exit;
}

$producto = $resultado->fetch_assoc();
$stmt->close();

if ($producto['stock'] < $cantidad) {
    echo json_encode(['error' => 'Stock insuficiente. Disponible: ' . $producto['stock']]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare('SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?');
$stmt->bind_param('ii', $usuario_id, $producto_id);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

if ($resultado->num_rows > 0) {
    $item           = $resultado->fetch_assoc();
    $nueva_cantidad = $item['cantidad'] + $cantidad;

    if ($nueva_cantidad > $producto['stock']) {
        echo json_encode(['error' => 'No hay suficiente stock para esa cantidad']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare('UPDATE carrito SET cantidad = ? WHERE id = ?');
    $stmt->bind_param('ii', $nueva_cantidad, $item['id']);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare('INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)');
    $stmt->bind_param('iii', $usuario_id, $producto_id, $cantidad);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'mensaje' => $producto['nombre'] . ' agregado al carrito']);
$conn->close();
?>