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

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);

    $nombre           = trim($datos['nombre'] ?? '');
    $marca            = trim($datos['marca'] ?? '');
    $modelo           = trim($datos['modelo'] ?? '');
    $descripcion      = trim($datos['descripcion'] ?? '');
    $precio           = floatval($datos['precio'] ?? 0);
    $stock            = intval($datos['stock'] ?? 0);
    $categoria        = intval($datos['categoria_id'] ?? 0);
    $imagen           = trim($datos['imagen'] ?? '');
    $especificaciones = json_encode($datos['especificaciones'] ?? []);
    $imagenes         = json_encode($datos['imagenes'] ?? []);

    if (empty($nombre) || $precio <= 0) {
        echo json_encode(['error' => 'Nombre y precio son obligatorios']);
        exit;
    }

    $sql  = "INSERT INTO productos (nombre, marca, modelo, descripcion, precio, stock, categoria_id, imagen, especificaciones, imagenes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssdissss', $nombre, $marca, $modelo, $descripcion, $precio, $stock, $categoria, $imagen, $especificaciones, $imagenes);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Producto agregado', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => 'Error al guardar: ' . $conn->error]);
    }
    $stmt->close();

} elseif ($metodo === 'PUT') {
    $datos = json_decode(file_get_contents('php://input'), true);

    $id               = intval($datos['id'] ?? 0);
    $nombre           = trim($datos['nombre'] ?? '');
    $marca            = trim($datos['marca'] ?? '');
    $modelo           = trim($datos['modelo'] ?? '');
    $descripcion      = trim($datos['descripcion'] ?? '');
    $precio           = floatval($datos['precio'] ?? 0);
    $stock            = intval($datos['stock'] ?? 0);
    $categoria        = intval($datos['categoria_id'] ?? 0);
    $imagen           = trim($datos['imagen'] ?? '');
    $especificaciones = json_encode($datos['especificaciones'] ?? []);
    $imagenes         = json_encode($datos['imagenes'] ?? []);

    if ($id <= 0 || empty($nombre) || $precio <= 0) {
        echo json_encode(['error' => 'Datos invalidos']);
        exit;
    }

    $sql  = "UPDATE productos SET nombre=?, marca=?, modelo=?, descripcion=?, precio=?, stock=?, categoria_id=?, imagen=?, especificaciones=?, imagenes=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssdissssi', $nombre, $marca, $modelo, $descripcion, $precio, $stock, $categoria, $imagen, $especificaciones, $imagenes, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Producto actualizado']);
    } else {
        echo json_encode(['error' => 'Error al actualizar: ' . $conn->error]);
    }
    $stmt->close();

} elseif ($metodo === 'DELETE') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $id    = intval($datos['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['error' => 'ID invalido']);
        exit;
    }

    $stmt = $conn->prepare('UPDATE productos SET activo = 0 WHERE id = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado']);
    } else {
        echo json_encode(['error' => 'Error al eliminar']);
    }
    $stmt->close();

} elseif ($metodo === 'GET') {
    $resultado = $conn->query(
        'SELECT p.*, c.nombre AS categoria
         FROM productos p
         LEFT JOIN categorias c ON p.categoria_id = c.id
         ORDER BY p.id DESC'
    );

    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
    echo json_encode(['success' => true, 'productos' => $productos]);
}

$conn->close();
?>