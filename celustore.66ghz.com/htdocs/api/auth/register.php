<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../../config/db.php';
require_once '../../libs/PHPMailer/PHPMailer.php';
require_once '../../libs/PHPMailer/SMTP.php';
require_once '../../libs/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

$datos     = json_decode(file_get_contents('php://input'), true);
$nombre    = trim($datos['nombre']   ?? '');
$email     = trim($datos['email']    ?? '');
$password  = trim($datos['password'] ?? '');

if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['error' => 'Nombre, email y contraseña son obligatorios']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'El email no es valido']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['error' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

$conn = conectar();

// Verificar si el email ya existe
$stmt = $conn->prepare('SELECT id, verificado FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $usuarioExistente = $res->fetch_assoc();
    if ($usuarioExistente['verificado'] == 0) {
        // Ya existe pero no verificado, reenviar código
        $codigo = strval(rand(100000, 999999));
        $stmt2  = $conn->prepare('UPDATE usuarios SET token_verificacion = ?, nombre = ?, password = ? WHERE email = ?');
        $stmt2->bind_param('ssss', $codigo, $nombre, $password, $email);
        $stmt2->execute();
        $stmt2->close();
        $stmt->close();

        $enviado = enviarCodigo($email, $nombre, $codigo);
        if ($enviado) {
            echo json_encode(['success' => true, 'verificacion' => true, 'mensaje' => 'Te reenviamos el código de verificación']);
        } else {
            echo json_encode(['error' => 'Error al enviar el email. Intentá de nuevo.']);
        }
        $conn->close();
        exit;
    }
    echo json_encode(['error' => 'El email ya esta registrado']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Generar código de 6 dígitos
$codigo = strval(rand(100000, 999999));

// Guardar usuario con verificado = 0
$stmt = $conn->prepare('INSERT INTO usuarios (nombre, email, password, verificado, token_verificacion) VALUES (?, ?, ?, 0, ?)');
$stmt->bind_param('ssss', $nombre, $email, $password, $codigo);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Error al registrar el usuario']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();
$conn->close();

// Enviar email con el código
$enviado = enviarCodigo($email, $nombre, $codigo);

if ($enviado) {
    echo json_encode(['success' => true, 'verificacion' => true, 'mensaje' => 'Te enviamos un código de verificación a tu email']);
} else {
    echo json_encode(['error' => 'Usuario registrado pero error al enviar el email. Contactá al soporte.']);
}

// ── Función para enviar el código por email ──────────────────
function enviarCodigo($email, $nombre, $codigo) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jaacby05@gmail.com';
        $mail->Password   = 'yxqbljxloffsewhc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('jaacby05@gmail.com', 'CeluStore');
        $mail->addAddress($email, $nombre);
        $mail->CharSet = 'UTF-8';

        $mail->isHTML(true);
        $mail->Subject = 'Tu código de verificación — CeluStore';
        $mail->Body    = "
            <div style='font-family:Arial,sans-serif; max-width:500px; margin:0 auto; background:#1a1a2e; color:#fff; border-radius:12px; overflow:hidden'>
                <div style='background:#6c63ff; padding:2rem; text-align:center'>
                    <h1 style='margin:0; font-size:1.8rem'>📱 CeluStore</h1>
                    <p style='margin:0.5rem 0 0; opacity:0.9'>Verificación de cuenta</p>
                </div>
                <div style='padding:2rem'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Tu código de verificación es:</p>
                    <div style='background:#6c63ff; border-radius:12px; padding:1.5rem; text-align:center; margin:1.5rem 0'>
                        <span style='font-size:2.5rem; font-weight:800; letter-spacing:0.5rem'>{$codigo}</span>
                    </div>
                    <p style='color:#aaa; font-size:0.9rem'>Este código expira en 30 minutos. Si no creaste una cuenta en CeluStore, ignorá este email.</p>
                </div>
            </div>
        ";
        $mail->AltBody = "Tu código de verificación de CeluStore es: {$codigo}";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>