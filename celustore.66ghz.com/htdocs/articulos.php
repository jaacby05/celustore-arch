<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

define('API_URL', 'https://celustore-api-xlsm.onrender.com');

function api($endpoint, $metodo = 'GET', $datos = null) {
    $url = API_URL . $endpoint;
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        30);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($metodo === 'POST') {
        curl_setopt($ch, CURLOPT_POST,       true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    } elseif ($metodo === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    $respuesta = curl_exec($ch);
    $error     = curl_error($ch);
    curl_close($ch);
    if ($error) return ['error' => 'Error de conexión: ' . $error];
    return json_decode($respuesta, true);
}

$mensaje = null;
$error   = null;

// Proveedores de MySQL
$proveedores = [];
$conn = conectar();
$result = $conn->query("SELECT id, nombre, categoria FROM proveedores WHERE activo = 1 ORDER BY nombre ASC");
while ($row = $result->fetch_assoc()) $proveedores[] = $row;
$conn->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $denominacion = trim($_POST['denominacion'] ?? '');
    $precio       = trim($_POST['precio']       ?? '');
    $cantidad     = trim($_POST['cantidad']     ?? '');
    $campo        = trim($_POST['campo']        ?? '');
    $id_proveedor = intval($_POST['id_proveedor'] ?? 0);

    if (empty($denominacion) || $precio === '' || $cantidad === '' || empty($campo) || $id_proveedor <= 0) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $resultado = api('/api/articulos', 'POST', [
            'denominacion' => $denominacion,
            'precio'       => floatval($precio),
            'cantidad'     => intval($cantidad),
            'campo'        => $campo,
            'id_proveedor' => $id_proveedor,
        ]);
        if (isset($resultado['success']) && $resultado['success']) {
            $prov_nombre = '';
            foreach ($proveedores as $p) { if ($p['id'] == $id_proveedor) { $prov_nombre = $p['nombre']; break; } }
            $mensaje = '✅ Artículo <strong>' . htmlspecialchars($denominacion) . '</strong> guardado en MongoDB. Proveedor: ' . htmlspecialchars($prov_nombre);
        } else {
            $error = $resultado['error'] ?? 'Error desconocido.';
        }
    }
}

// Filtros
$filtro_proveedor = isset($_GET['proveedor'])  ? intval($_GET['proveedor'])   : 0;
$filtro_campo     = isset($_GET['campo'])      ? trim($_GET['campo'])         : '';
$filtro_orden     = isset($_GET['orden'])      ? trim($_GET['orden'])         : '';
$filtro_precio_max= isset($_GET['precio_max']) ? trim($_GET['precio_max'])    : '';

$qs = [];
if ($filtro_proveedor > 0)     $qs[] = 'id_proveedor=' . $filtro_proveedor;
if ($filtro_campo !== '')      $qs[] = 'campo=' . urlencode($filtro_campo);
if ($filtro_orden !== '')      $qs[] = 'orden=' . $filtro_orden;
if ($filtro_precio_max !== '') $qs[] = 'precio_max=' . $filtro_precio_max;

$res_art   = api('/api/articulos' . (count($qs) ? '?' . implode('&', $qs) : ''));
$articulos = $res_art['articulos'] ?? [];

$qs_b = [];
if ($filtro_proveedor > 0) $qs_b[] = 'id_proveedor=' . $filtro_proveedor;
if ($filtro_campo !== '')  $qs_b[] = 'campo=' . urlencode($filtro_campo);
$res_barato = api('/api/articulos/mas-barato' . (count($qs_b) ? '?' . implode('&', $qs_b) : ''));
$mas_barato = $res_barato['articulo'] ?? null;

$mapa_prov = [];
foreach ($proveedores as $p) $mapa_prov[$p['id']] = $p['nombre'];

function formatoPrecio($n) { return '$' . number_format($n, 2, ',', '.'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artículos — CeluStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout { display:grid; grid-template-columns:240px 1fr; min-height:100vh; }
        .sidebar { background:var(--dark2); border-right:1px solid var(--border); padding:1.5rem 0; position:sticky; top:0; height:100vh; overflow-y:auto; }
        .sidebar-brand { padding:0 1.5rem 1.5rem; border-bottom:1px solid var(--border); margin-bottom:1rem; }
        .sidebar-brand h2 { font-size:1.1rem; color:var(--primary); }
        .sidebar-brand p  { font-size:0.8rem; color:var(--text-muted); margin-top:0.2rem; }
        .sidebar-menu { list-style:none; padding:0 0.8rem; }
        .sidebar-menu li a { display:flex; align-items:center; gap:0.8rem; padding:0.75rem 1rem; border-radius:8px; color:var(--text-muted); font-size:0.9rem; transition:all 0.2s; margin-bottom:0.2rem; text-decoration:none; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background:rgba(108,99,255,0.15); color:var(--primary); }
        .sidebar-menu li a i { width:18px; text-align:center; }
        .sidebar-divider { border:none; border-top:1px solid var(--border); margin:1rem 1.5rem; }
        .admin-content { padding:2rem; background:var(--dark); }
        .form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:2rem; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .form-group { margin-bottom:0; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; margin-bottom:5px; color:var(--text-muted); }
        .form-group input, .form-group select { width:100%; padding:9px 13px; background:var(--dark); border:1.5px solid var(--border); border-radius:8px; color:var(--text); font-size:0.9rem; }
        .alert-ok    { background:rgba(76,175,80,0.15); color:#4ade80; border:1px solid #4ade80; padding:12px 18px; border-radius:8px; margin-bottom:20px; }
        .alert-error { background:rgba(244,67,54,0.15); color:#f87171; border:1px solid #f87171; padding:12px 18px; border-radius:8px; margin-bottom:20px; }
        .art-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:16px; }
        .art-card { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius); padding:18px; }
        .art-card h3 { font-size:1rem; margin-bottom:6px; }
        .precio { font-size:1.2rem; font-weight:700; color:#4ade80; margin:8px 0; }
        .art-card p { font-size:0.85rem; color:var(--text-muted); margin-bottom:3px; }
        .badge-mongo { background:rgba(0,104,74,0.3); color:#4ade80; border:1px solid #00684a; font-size:0.72rem; padding:2px 10px; border-radius:20px; display:inline-block; margin-bottom:10px; }
        .badge-barato { background:rgba(233,69,96,0.2); color:#e94560; border:1px solid #e94560; font-size:0.72rem; padding:2px 10px; border-radius:20px; display:inline-block; margin-bottom:10px; }
        .filtros { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
        .filtros select, .filtros input { padding:8px 12px; background:var(--dark); border:1.5px solid var(--border); border-radius:8px; color:var(--text); font-size:0.85rem; }
        .section-header { display:flex; align-items:center; gap:10px; margin:0 0 16px; }
        .section-header h2 { margin:0; font-size:1.1rem; }
        .arq { background:var(--dark2); border:1px solid var(--border); border-radius:8px; padding:14px 18px; font-family:monospace; font-size:0.8rem; line-height:1.8; margin-bottom:24px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2><i class="fas fa-mobile-alt"></i> CeluStore</h2>
            <p>Panel de Administración</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin/productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="admin/pedidos.php"><i class="fas fa-shopping-bag"></i> Pedidos</a></li>
            <li><a href="admin/usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="admin/resenas.php"><i class="fas fa-star"></i> Reseñas</a></li>
            <li><a href="proveedores.php"><i class="fas fa-truck"></i> Proveedores</a></li>
            <li><a href="articulos.php" class="active"><i class="fas fa-tags"></i> Artículos</a></li>
        </ul>
        <hr class="sidebar-divider">
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-store"></i> Ver Tienda</a></li>
            <li><a href="api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
            <div>
                <h1><i class="fas fa-tags"></i> Artículos de Proveedores</h1>
                <p style="color:var(--text-muted);font-size:0.9rem;margin-top:0.3rem;">Guardado en <strong style="color:#4ade80">MongoDB Atlas</strong> via API en Render</p>
            </div>
            <a href="proveedores.php" class="btn btn-primary"><i class="fas fa-truck"></i> Ver Proveedores</a>
        </div>

        <div class="arq">
            <span style="color:#74b9ff">[PHP InfinityFree]</span> → lee proveedores de <span style="color:#ffc107;font-weight:bold">[MySQL]</span><br>
            <span style="color:#74b9ff">[PHP InfinityFree]</span> → cURL → <span style="color:#e94560;font-weight:bold">[API Render]</span> → <span style="color:#4ade80;font-weight:bold">[MongoDB Atlas]</span>
        </div>

        <?php if ($mensaje): ?><div class="alert-ok"><?= $mensaje ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if (empty($proveedores)): ?>
            <div class="alert-error">⚠️ No hay proveedores. Primero <a href="proveedores.php" style="color:#f87171">agregá uno</a>.</div>
        <?php endif; ?>

        <!-- Formulario -->
        <div class="form-card">
            <h3 style="margin-bottom:1.2rem;font-size:1rem;"><i class="fas fa-plus"></i> Nuevo Artículo</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Denominación *</label>
                        <input type="text" name="denominacion" placeholder="Ej: Cable USB-C 2m" required>
                    </div>
                    <div class="form-group">
                        <label>Proveedor (MySQL) *</label>
                        <select name="id_proveedor" required <?= empty($proveedores) ? 'disabled' : '' ?>>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($proveedores as $p): ?>
                            <option value="<?= $p['id'] ?>">#<?= $p['id'] ?> — <?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Precio ($) *</label>
                        <input type="number" name="precio" min="0" step="0.01" placeholder="Ej: 5000" required>
                    </div>
                    <div class="form-group">
                        <label>Cantidad *</label>
                        <input type="number" name="cantidad" min="0" placeholder="Ej: 50" required>
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Campo / Categoría *</label>
                        <input type="text" name="campo" placeholder="Ej: Accesorios, Electrónica..." required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" style="margin-top:16px;" <?= empty($proveedores) ? 'disabled' : '' ?>>
                    <i class="fas fa-save"></i> Guardar en MongoDB
                </button>
            </form>
        </div>

        <!-- Más barato -->
        <?php if ($mas_barato): ?>
        <div class="art-card" style="margin-bottom:20px;border-left:4px solid #e94560;">
            <span class="badge-barato">🏷️ Más barato<?= $filtro_proveedor ? ' del proveedor' : '' ?></span>
            <h3><?= htmlspecialchars($mas_barato['denominacion']) ?></h3>
            <div class="precio" style="color:#e94560;"><?= formatoPrecio($mas_barato['precio']) ?></div>
            <p>Campo: <?= htmlspecialchars($mas_barato['campo']) ?> · Cantidad: <?= $mas_barato['cantidad'] ?> · Proveedor: <?= htmlspecialchars($mapa_prov[$mas_barato['id_proveedor']] ?? '#' . $mas_barato['id_proveedor']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="section-header">
            <h2>Listado</h2>
            <span style="background:rgba(0,104,74,0.3);color:#4ade80;border:1px solid #00684a;font-size:0.75rem;padding:3px 10px;border-radius:20px;font-weight:700;">MongoDB</span>
        </div>

        <form method="GET">
            <div class="filtros">
                <select name="proveedor">
                    <option value="0">Todos los proveedores</option>
                    <?php foreach ($proveedores as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filtro_proveedor == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="campo" placeholder="Filtrar por campo..." value="<?= htmlspecialchars($filtro_campo) ?>">
                <input type="number" name="precio_max" placeholder="Precio máximo..." value="<?= htmlspecialchars($filtro_precio_max) ?>" min="0">
                <select name="orden">
                    <option value="">Orden por defecto</option>
                    <option value="precio_asc"  <?= $filtro_orden === 'precio_asc'  ? 'selected' : '' ?>>Precio: menor a mayor</option>
                    <option value="precio_desc" <?= $filtro_orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                </select>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <?php if ($filtro_proveedor || $filtro_campo || $filtro_orden || $filtro_precio_max): ?>
                    <a href="articulos.php" class="btn btn-secondary">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($articulos)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:40px;">No hay artículos<?= ($filtro_proveedor || $filtro_campo) ? ' con ese filtro' : '' ?>.</p>
        <?php else: ?>
        <div class="art-grid">
            <?php foreach ($articulos as $a): ?>
            <div class="art-card">
                <span class="badge-mongo">MongoDB</span>
                <h3><?= htmlspecialchars($a['denominacion']) ?></h3>
                <div class="precio"><?= formatoPrecio($a['precio']) ?></div>
                <p><i class="fas fa-tag"></i> <?= htmlspecialchars($a['campo']) ?></p>
                <p><i class="fas fa-boxes"></i> Cantidad: <?= $a['cantidad'] ?></p>
                <p><i class="fas fa-truck"></i> <?= htmlspecialchars($mapa_prov[$a['id_proveedor']] ?? '#' . $a['id_proveedor']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
