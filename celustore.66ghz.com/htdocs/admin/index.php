<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — CeluStore Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: var(--dark2);
            border-right: 1px solid var(--border);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        .sidebar-brand h2 { font-size:1.1rem; color:var(--primary); }
        .sidebar-brand p  { font-size:0.8rem; color:var(--text-muted); margin-top:0.2rem; }
        .sidebar-menu { list-style:none; padding:0 0.8rem; }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: all 0.2s;
            margin-bottom: 0.2rem;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(108,99,255,0.15);
            color: var(--primary);
        }
        .sidebar-menu li a i { width:18px; text-align:center; }
        .sidebar-divider { border:none; border-top:1px solid var(--border); margin:1rem 1.5rem; }
        .admin-content { padding:2rem; background:var(--dark); }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .admin-header h1 { font-size: 1.5rem; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .stat-icon.purple { background: rgba(108,99,255,0.2); color: var(--primary); }
        .stat-icon.green  { background: rgba(76,175,80,0.2);  color: var(--success); }
        .stat-icon.orange { background: rgba(255,152,0,0.2);  color: var(--warning); }
        .stat-icon.red    { background: rgba(244,67,54,0.2);  color: var(--danger);  }
        .stat-icon.blue   { background: rgba(33,150,243,0.2); color: #64b5f6; }
        .stat-icon.yellow { background: rgba(255,215,0,0.2);  color: #ffd700; }
        .stat-info h3 { font-size:1.8rem; font-weight:800; line-height:1; }
        .stat-info p  { font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
        .dash-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
        }
        .dash-card h3 {
            font-size: 1rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tabla-simple { width:100%; border-collapse:collapse; font-size:0.85rem; }
        .tabla-simple th {
            text-align: left;
            padding: 0.6rem 0.8rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        .tabla-simple td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .tabla-simple tr:last-child td { border-bottom: none; }
        .tabla-simple tr:hover td { background: rgba(255,255,255,0.02); }
        @media (max-width: 768px) {
            .admin-layout { grid-template-columns: 1fr; }
            .sidebar { height:auto; position:relative; }
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2><i class="fas fa-mobile-alt"></i> CeluStore</h2>
            <p>Panel de Administración</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="pedidos.php"><i class="fas fa-shopping-bag"></i> Pedidos</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="resenas.php"><i class="fas fa-star"></i> Reseñas</a></li>
        </ul>
        <hr class="sidebar-divider">
        <ul class="sidebar-menu">
            <li><a href="../index.php"><i class="fas fa-store"></i> Ver Tienda</a></li>
            <li><a href="../api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO -->
    <main class="admin-content">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.3rem">
                    Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                </p>
            </div>
            <div id="fechaHoy" style="color:var(--text-muted); font-size:0.85rem"></div>
        </div>

        <!-- STATS -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-spinner fa-spin"></i></div>
                <div class="stat-info"><h3>—</h3><p>Cargando...</p></div>
            </div>
        </div>

        <!-- GRILLA -->
        <div class="dashboard-grid">
            <div class="dash-card">
                <h3><i class="fas fa-shopping-bag"></i> Últimos Pedidos</h3>
                <div id="ultimosPedidos">
                    <p style="color:var(--text-muted)">Cargando...</p>
                </div>
            </div>
            <div class="dash-card">
                <h3>
                    <i class="fas fa-exclamation-triangle" style="color:var(--warning)"></i>
                    Stock Bajo
                </h3>
                <div id="stockBajo">
                    <p style="color:var(--text-muted)">Cargando...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('fechaHoy').textContent =
    new Date().toLocaleDateString('es-AR', {
        weekday:'long', year:'numeric', month:'long', day:'numeric'
    });

async function cargarDashboard() {
    const [resPedidos, resProductos, resUsuarios] = await Promise.all([
        fetch('../api/admin/pedidos.php'),
        fetch('../api/admin/productos.php'),
        fetch('../api/admin/usuarios.php')
    ]);

    const dataPedidos   = await resPedidos.json();
    const dataProductos = await resProductos.json();
    const dataUsuarios  = await resUsuarios.json();

    const pedidos   = dataPedidos.pedidos    || [];
    const productos = dataProductos.productos || [];
    const usuarios  = dataUsuarios.usuarios   || [];

    const totalVentas      = pedidos.reduce((sum, p) => sum + parseFloat(p.total), 0);
    const pedidosPend      = pedidos.filter(p => p.estado === 'pendiente').length;
    const productosActivos = productos.filter(p => p.activo == 1).length;
    const stockBajoCount   = productos.filter(p => p.stock <= 5 && p.activo == 1).length;
    const totalClientes    = usuarios.length;

    document.getElementById('statsGrid').innerHTML = `
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>$${totalVentas.toLocaleString('es-AR', {maximumFractionDigits:0})}</h3>
                <p>Total en ventas</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3>${pedidosPend}</h3>
                <p>Pedidos pendientes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <h3>${productosActivos}</h3>
                <p>Productos activos</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <h3>${totalClientes}</h3>
                <p>Clientes registrados</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <h3>${stockBajoCount}</h3>
                <p>Productos con stock bajo</p>
            </div>
        </div>`;

    const ultimos = pedidos.slice(0, 5);
    document.getElementById('ultimosPedidos').innerHTML = ultimos.length === 0
        ? '<p style="color:var(--text-muted)">No hay pedidos aún</p>'
        : `<table class="tabla-simple">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                ${ultimos.map(p => `
                    <tr>
                        <td style="color:var(--primary); font-weight:600; font-size:0.8rem">
                            ${p.numero_orden}
                        </td>
                        <td>${p.cliente}</td>
                        <td style="font-weight:600">
                            $${parseFloat(p.total).toLocaleString('es-AR')}
                        </td>
                        <td>
                            <span class="badge badge-${p.estado}">
                                ${p.estado.replace('_',' ')}
                            </span>
                        </td>
                    </tr>`).join('')}
            </tbody>
           </table>`;

    const bajo = productos.filter(p => p.stock <= 5 && p.activo == 1).slice(0, 6);
    document.getElementById('stockBajo').innerHTML = bajo.length === 0
        ? '<p style="color:var(--success)">✅ Todos los productos tienen stock suficiente</p>'
        : `<table class="tabla-simple">
            <thead>
                <tr><th>Producto</th><th>Stock</th><th></th></tr>
            </thead>
            <tbody>
                ${bajo.map(p => `
                    <tr>
                        <td>${p.nombre}</td>
                        <td>
                            <span class="badge ${p.stock === 0 ? 'badge-cancelado' : 'badge-pendiente'}">
                                ${p.stock === 0 ? 'Sin stock' : p.stock + ' uds.'}
                            </span>
                        </td>
                        <td>
                            <a href="productos.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>`).join('')}
            </tbody>
           </table>`;
}

cargarDashboard();
</script>

</body>
</html>