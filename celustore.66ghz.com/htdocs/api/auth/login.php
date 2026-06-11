<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);

$email    = trim($datos['email'] ?? '');
$password = trim($datos['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(['error' => 'Email y contraseña son obligatorios']);
    exit;
}

$conn = conectar();

// Comparar contraseña directamente sin cifrado
$stmt = $conn->prepare('SELECT id, nombre, email, rol FROM usuarios WHERE email = ? AND password = ? AND verificado = 1');
$stmt->bind_param('ss', $email, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['error' => 'Email o contraseña incorrectos']);
    $stmt->close();
    $conn->close();
    exit;
}

$usuario = $resultado->fetch_assoc();

$_SESSION['usuario_id']     = $usuario['id'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_rol']    = $usuario['rol'];

echo json_encode([
    'success' => true,
    'usuario' => [
        'id'     => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'rol'    => $usuario['rol']
    ]
]);

$stmt->close();
$conn->close();
?>