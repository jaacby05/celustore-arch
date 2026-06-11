<?php
define('DB_HOST', 'sql302.infinityfree.com');
define('DB_USER', 'if0_42020029');
define('DB_PASS', 'ProjectsClass0');
define('DB_NAME', 'if0_42020029_celustore_db');

function conectar() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Error de conexion: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
