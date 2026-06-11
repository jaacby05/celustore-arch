<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
require_once '../../config/db.php';

define('API_URL', 'https://celustore-api-xlsm.onrender.com');

$metodo      = $_SERVER['REQUEST_METHOD'];
$producto_id = intval($_GET['producto_id'] ?? 0);

function llamarAPI($endpoint, $metodo = 'GET', $datos = null) {
    $url = API_URL . $endpoint;
    $ch  = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($metodo === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    } elseif ($metodo === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    } elseif ($metodo === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        if ($datos) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
        }
    }

    $respuesta = curl_exec($ch);
    $error     = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => 'Error de conexión con la API: ' . $error];
    }

    return json_decode($respuesta, true);
}

// ── GET ──────────────────────────────────────────────────────
if ($metodo === 'GET' && $producto_id > 0) {
    $resultado = llamarAPI('/api/resenas/' . $producto_id);
    echo json_encode($resultado);

// ── POST ─────────────────────────────────────────────────────
} elseif ($metodo === 'POST') {
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['error' => 'Debes iniciar sesion']);
        exit;
    }
    if ($_SESSION['usuario_rol'] === 'admin') {
        echo json_encode(['error' => 'Los administradores no pueden dejar reseñas']);
        exit;
    }

    // Verificar usuario en MySQL — solo columna "nombre"
    $conn = conectar();
    $uid  = (int)$_SESSION['usuario_id'];
    $stmt = $conn->prepare('SELECT id, nombre FROM usuarios WHERE id = ? AND rol != "admin"');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res  = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['error' => 'Usuario no válido']);
        exit;
    }
    $usuario = $res->fetch_assoc();
    $stmt->close();
    $conn->close();

    $datos      = json_decode(file_get_contents('php://input'), true);
    $prod_id    = intval($datos['producto_id']    ?? 0);
    $puntuacion = intval($datos['puntuacion']      ?? 0);
    $titulo     = trim($datos['titulo']            ?? '');
    $comentario = trim($datos['comentario']        ?? '');
    $caract     = $datos['caracteristicas']        ?? [];

    if ($prod_id <= 0 || $puntuacion < 1 || $puntuacion > 5 || empty($comentario)) {
        echo json_encode(['error' => 'Datos invalidos']);
        exit;
    }

    $payload = [
        'producto_id'     => $prod_id,
        'usuario_id'      => $uid,
        'usuario_nombre'  => $usuario['nombre'],
        'puntuacion'      => $puntuacion,
        'titulo'          => $titulo,
        'comentario'      => $comentario,
        'caracteristicas' => $caract,
    ];

    $resultado = llamarAPI('/api/resenas', 'POST', $payload);
    echo json_encode($resultado);

// ── PUT ──────────────────────────────────────────────────────
} elseif ($metodo === 'PUT') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $id    = trim($datos['id'] ?? '');

    if (empty($id)) {
        echo json_encode(['error' => 'ID invalido']);
        exit;
    }

    $resultado = llamarAPI('/api/resenas/' . $id . '/util', 'PUT', []);
    echo json_encode($resultado);

// ── DELETE ───────────────────────────────────────────────────
} elseif ($metodo === 'DELETE') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $id    = trim($datos['id'] ?? '');

    if (empty($id)) {
        echo json_encode(['error' => 'ID invalido']);
        exit;
    }

    $resultado = llamarAPI('/api/resenas/' . $id, 'DELETE');
    echo json_encode($resultado);

} else {
    echo json_encode(['error' => 'Accion no valida']);
}
?>