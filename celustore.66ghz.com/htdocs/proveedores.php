<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

$mensaje = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre']    ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $contacto  = trim($_POST['contacto']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $notas     = trim($_POST['notas']     ?? '');

    if (empty($nombre) || empty($categoria)) {
        $error = 'El nombre y la categoría son obligatorios.';
    } else {
        $conn = conectar();
        $stmt = $conn->prepare("INSERT INTO proveedores (nombre, categoria, contacto, email, telefono, direccion, notas, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssss", $nombre, $categoria, $contacto, $email, $telefono, $direccion, $notas);
        if ($stmt->execute()) {
            $mensaje = '✅ Proveedor <strong>' . htmlspecialchars($nombre) . '</strong> guardado en MySQL con ID #' . $conn->insert_id;
        } else {
            $error = 'Error al guardar: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    }
}

$proveedores = [];
$conn = conectar();
$result = $conn->query("SELECT * FROM proveedores WHERE activo = 1 ORDER BY nombre ASC");
while ($row = $result->fetch_assoc()) $proveedores[] = $row;
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores — CeluStore</title>
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
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:9px 13px; background:var(--dark); border:1.5px solid var(--border); border-radius:8px; color:var(--text); font-size:0.9rem; font-family:inherit; }
        .form-group textarea { resize:vertical; min-height:70px; }
        .alert-ok    { background:rgba(76,175,80,0.15); color:#4ade80; border:1px solid #4ade80; padding:12px 18px; border-radius:8px; margin-bottom:20px; }
        .alert-error { background:rgba(244,67,54,0.15); color:#f87171; border:1px solid #f87171; padding:12px 18px; border-radius:8px; margin-bottom:20px; }
        .prov-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
        .prov-card { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius); padding:18px; }
        .prov-card h3 { font-size:1rem; margin-bottom:6px; }
        .prov-card p  { font-size:0.85rem; color:var(--text-muted); margin-bottom:4px; }
        .badge-mysql  { background:rgba(255,193,7,0.2); color:#ffc107; border:1px solid #ffc107; font-size:0.72rem; padding:2px 10px; border-radius:20px; display:inline-block; margin-bottom:10px; }
        .section-header { display:flex; align-items:center; gap:10px; margin:0 0 16px; }
        .section-header h2 { margin:0; font-size:1.1rem; }
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
            <li><a href="proveedores.php" class="active"><i class="fas fa-truck"></i> Proveedores</a></li>
            <li><a href="articulos.php"><i class="fas fa-tags"></i> Artículos</a></li>
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
                <h1><i class="fas fa-truck"></i> Proveedores</h1>
                <p style="color:var(--text-muted);font-size:0.9rem;margin-top:0.3rem;">Guardado en <strong style="color:#ffc107">MySQL</strong> directo desde PHP</p>
            </div>
            <a href="articulos.php" class="btn btn-primary"><i class="fas fa-tags"></i> Ver Artículos</a>
        </div>

        <?php if ($mensaje): ?><div class="alert-ok"><?= $mensaje ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <!-- Formulario -->
        <div class="form-card">
            <h3 style="margin-bottom:1.2rem;font-size:1rem;"><i class="fas fa-plus"></i> Nuevo Proveedor</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" placeholder="Ej: Distribuidora Norte" required>
                    </div>
                    <div class="form-group">
                        <label>Categoría *</label>
                        <select name="categoria" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach(['Tecnología','Alimentos','Indumentaria','Construcción','Salud','Electrónica','Otro'] as $cat): ?>
                            <option value="<?= $cat ?>"><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contacto</label>
                        <input type="text" name="contacto" placeholder="Nombre del contacto">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="contacto@proveedor.com">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" placeholder="+54 11 1234-5678">
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" placeholder="Dirección">
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px;">
                    <label>Notas</label>
                    <textarea name="notas" placeholder="Observaciones opcionales..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:16px;"><i class="fas fa-save"></i> Guardar en MySQL</button>
            </form>
        </div>

        <!-- Lista -->
        <div class="section-header">
            <h2>Lista de Proveedores</h2>
            <span style="background:rgba(255,193,7,0.2);color:#ffc107;border:1px solid #ffc107;font-size:0.75rem;padding:3px 10px;border-radius:20px;font-weight:700;">MySQL</span>
        </div>

        <?php if (empty($proveedores)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:40px;">No hay proveedores cargados aún.</p>
        <?php else: ?>
        <div class="prov-grid">
            <?php foreach ($proveedores as $p): ?>
            <div class="prov-card">
                <span class="badge-mysql">MySQL #<?= $p['id'] ?></span>
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <p><i class="fas fa-tag"></i> <?= htmlspecialchars($p['categoria'] ?? '—') ?></p>
                <?php if (!empty($p['contacto'])): ?><p><i class="fas fa-user"></i> <?= htmlspecialchars($p['contacto']) ?></p><?php endif; ?>
                <?php if (!empty($p['email'])): ?><p><i class="fas fa-envelope"></i> <?= htmlspecialchars($p['email']) ?></p><?php endif; ?>
                <?php if (!empty($p['telefono'])): ?><p><i class="fas fa-phone"></i> <?= htmlspecialchars($p['telefono']) ?></p><?php endif; ?>
                <div style="margin-top:12px;">
                    <a href="articulos.php?proveedor=<?= $p['id'] ?>" class="btn btn-primary" style="font-size:0.8rem;padding:6px 14px;"><i class="fas fa-tags"></i> Ver artículos</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
