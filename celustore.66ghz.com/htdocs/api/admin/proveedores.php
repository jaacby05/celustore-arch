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

if ($metodo === 'GET') {
    $resultado = $conn->query(
        'SELECT * FROM proveedores ORDER BY nombre ASC'
    );
    $proveedores = [];
    while ($fila = $resultado->fetch_assoc()) {
        $proveedores[] = $fila;
    }
    echo json_encode(['success' => true, 'proveedores' => $proveedores]);

} elseif ($metodo === 'POST') {
    $datos    = json_decode(file_get_contents('php://input'), true);
    $nombre   = trim($datos['nombre']    ?? '');
    $contacto = trim($datos['contacto']  ?? '');
    $email    = trim($datos['email']     ?? '');
    $telefono = trim($datos['telefono']  ?? '');
    $direccion = trim($datos['direccion'] ?? '');
    $notas    = trim($datos['notas']     ?? '');

    if (empty($nombre)) {
        echo json_encode(['error' => 'El nombre es obligatorio']);
        exit;
    }

    $stmt = $conn->prepare(
        'INSERT INTO proveedores (nombre, contacto, email, telefono, direccion, notas)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('ssssss', $nombre, $contacto, $email, $telefono, $direccion, $notas);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Proveedor agregado', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['error' => 'Error al guardar']);
    }
    $stmt->close();

} elseif ($metodo === 'PUT') {
    $datos     = json_decode(file_get_contents('php://input'), true);
    $id        = intval($datos['id']        ?? 0);
    $nombre    = trim($datos['nombre']      ?? '');
    $contacto  = trim($datos['contacto']    ?? '');
    $email     = trim($datos['email']       ?? '');
    $telefono  = trim($datos['telefono']    ?? '');
    $direccion = trim($datos['direccion']   ?? '');
    $notas     = trim($datos['notas']       ?? '');

    if ($id <= 0 || empty($nombre)) {
        echo json_encode(['error' => 'Datos invalidos']);
        exit;
    }

    $stmt = $conn->prepare(
        'UPDATE proveedores SET nombre=?, contacto=?, email=?, telefono=?, direccion=?, notas=?
         WHERE id=?'
    );
    $stmt->bind_param('ssssssi', $nombre, $contacto, $email, $telefono, $direccion, $notas, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Proveedor actualizado']);
    } else {
        echo json_encode(['error' => 'Error al actualizar']);
    }
    $stmt->close();

} elseif ($metodo === 'DELETE') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $id    = intval($datos['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['error' => 'ID invalido']);
        exit;
    }

    $stmt = $conn->prepare('UPDATE proveedores SET activo = 0 WHERE id = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Proveedor desactivado']);
    } else {
        echo json_encode(['error' => 'Error al eliminar']);
    }
    $stmt->close();
}

$conn->close();
?>