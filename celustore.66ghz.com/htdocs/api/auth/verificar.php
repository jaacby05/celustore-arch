<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$datos  = json_decode(file_get_contents('php://input'), true);
$email  = trim($datos['email']  ?? '');
$codigo = trim($datos['codigo'] ?? '');

if (empty($email) || empty($codigo)) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$conn = conectar();
$stmt = $conn->prepare('SELECT id, nombre, rol, token_verificacion FROM usuarios WHERE email = ? AND verificado = 0');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['error' => 'Cuenta no encontrada o ya verificada']);
    $stmt->close();
    $conn->close();
    exit;
}

$usuario = $res->fetch_assoc();
$stmt->close();

if ($usuario['token_verificacion'] !== $codigo) {
    echo json_encode(['error' => 'Código incorrecto. Verificá tu email.']);
    $conn->close();
    exit;
}

// Activar cuenta
$stmt = $conn->prepare('UPDATE usuarios SET verificado = 1, token_verificacion = NULL WHERE id = ?');
$stmt->bind_param('i', $usuario['id']);
$stmt->execute();
$stmt->close();
$conn->close();

// Iniciar sesión automáticamente
$_SESSION['usuario_id']     = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_rol']    = $usuario['rol'];

echo json_encode([
    'success' => true,
    'mensaje' => '¡Cuenta verificada correctamente!',
    'usuario' => [
        'id'     => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'rol'    => $usuario['rol']
    ]
]);
?>