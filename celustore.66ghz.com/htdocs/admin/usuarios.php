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
    <title>Usuarios — CeluStore Admin</title>
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
        .tabla-usuarios {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .tabla-usuarios th {
            text-align: left;
            padding: 0.8rem 1rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
            background: var(--dark2);
        }
        .tabla-usuarios td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .tabla-usuarios tr:hover td { background: rgba(255,255,255,0.02); }
        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(108,99,255,0.2);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .usuario-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .stats-resumen {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-mini {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-mini i  { font-size:1.5rem; color:var(--primary); }
        .stat-mini h3 { font-size:1.5rem; font-weight:800; }
        .stat-mini p  { font-size:0.8rem; color:var(--text-muted); }
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--dark2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            width: 90%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 1rem; right: 1rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.4rem;
            cursor: pointer;
        }
        .detalle-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1.2rem;
        }
        .detalle-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.8rem 1rem;
        }
        .detalle-item label {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detalle-item span {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text);
        }
        .pedidos-lista { margin-top: 1rem; }
        .pedido-mini {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.85rem;
        }
        .pedido-mini:last-child { border-bottom: none; }
        @media (max-width: 768px) {
            .admin-layout { grid-template-columns: 1fr; }
            .sidebar { height:auto; position:relative; }
            .detalle-grid { grid-template-columns: 1fr; }
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
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="pedidos.php"><i class="fas fa-shopping-bag"></i> Pedidos</a></li>
            <li><a href="usuarios.php" class="active"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="proveedores.php"><i class="fas fa-truck-loading"></i> Proveedores</a></li>
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
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.5rem">
                <i class="fas fa-users"></i> Usuarios Registrados
            </h1>
        </div>

        <div class="stats-resumen" id="statsUsuarios"></div>

        <div style="margin-bottom:1.2rem">
            <input type="text" id="filtroBusqueda" class="form-control"
                   placeholder="🔍 Buscar por nombre o email..."
                   oninput="filtrarUsuarios()"
                   style="max-width:400px">
        </div>

        <div style="background:var(--card-bg); border:1px solid var(--border);
                    border-radius:var(--radius); overflow:hidden; overflow-x:auto">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Fecha de registro</th>
                        <th>Pedidos</th>
                        <th>Total gastado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <tr><td colspan="6" style="text-align:center;
                        color:var(--text-muted); padding:2rem">
                        Cargando...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- MODAL DETALLE -->
<div class="modal-overlay" id="modalUsuario">
    <div class="modal">
        <button class="modal-close" id="btnCerrarModal">
            <i class="fas fa-times"></i>
        </button>
        <div id="modalContenido"></div>
    </div>
</div>

<script>
let usuariosData = [];

document.getElementById('btnCerrarModal').addEventListener('click', cerrarModal);
document.getElementById('modalUsuario').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

function formatFecha(f) {
    return new Date(f).toLocaleDateString('es-AR', {
        day:'2-digit', month:'2-digit', year:'numeric'
    });
}

async function cargarUsuarios() {
    const res  = await fetch('../api/admin/usuarios.php');
    const data = await res.json();
    usuariosData = data.usuarios || [];

    const totalClientes  = usuariosData.length;
    const conPedidos     = usuariosData.filter(u => u.total_pedidos > 0).length;
    const totalFacturado = usuariosData.reduce((sum, u) => sum + parseFloat(u.total_gastado || 0), 0);

    document.getElementById('statsUsuarios').innerHTML = `
        <div class="stat-mini">
            <i class="fas fa-users"></i>
            <div><h3>${totalClientes}</h3><p>Clientes registrados</p></div>
        </div>
        <div class="stat-mini">
            <i class="fas fa-shopping-bag"></i>
            <div><h3>${conPedidos}</h3><p>Clientes con pedidos</p></div>
        </div>
        <div class="stat-mini">
            <i class="fas fa-dollar-sign"></i>
            <div>
                <h3>$${totalFacturado.toLocaleString('es-AR', {maximumFractionDigits:0})}</h3>
                <p>Total facturado</p>
            </div>
        </div>`;

    filtrarUsuarios();
}

function filtrarUsuarios() {
    const q = document.getElementById('filtroBusqueda').value.toLowerCase();
    const filtrados = usuariosData.filter(u =>
        u.nombre.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q)
    );
    renderTabla(filtrados);
}

function renderTabla(lista) {
    const tbody = document.getElementById('tablaBody');
    if (lista.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;
            color:var(--text-muted); padding:2rem">No se encontraron usuarios</td></tr>`;
        return;
    }
    tbody.innerHTML = lista.map(u => `
        <tr>
            <td>
                <div class="usuario-info">
                    <div class="avatar">${u.nombre.charAt(0).toUpperCase()}</div>
                    <div>
                        <div style="font-weight:600">${u.nombre}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted)">ID #${u.id}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--text-muted)">${u.email}</td>
            <td style="color:var(--text-muted)">${formatFecha(u.fecha_registro)}</td>
            <td>
                <span class="badge ${u.total_pedidos > 0 ? 'badge-entregado' : 'badge-pendiente'}">
                    ${u.total_pedidos} pedido${u.total_pedidos != 1 ? 's' : ''}
                </span>
            </td>
            <td style="font-weight:700; color:var(--primary)">
                $${parseFloat(u.total_gastado || 0).toLocaleString('es-AR')}
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="verDetalle(${u.id})">
                    <i class="fas fa-eye"></i> Ver detalle
                </button>
            </td>
        </tr>
    `).join('');
}

async function verDetalle(id) {
    const usuario = usuariosData.find(u => u.id == id);
    if (!usuario) return;

    const res     = await fetch(`../api/admin/usuarios.php?id=${id}`);
    const data    = await res.json();
    const pedidos = data.pedidos || [];

    const pedidosHTML = pedidos.length === 0
        ? '<p style="color:var(--text-muted); font-size:0.85rem">Este cliente no tiene pedidos aún.</p>'
        : pedidos.map(p => `
            <div class="pedido-mini">
                <div>
                    <div style="font-weight:600; color:var(--primary); font-size:0.8rem">
                        ${p.numero_orden}
                    </div>
                    <div style="color:var(--text-muted); font-size:0.75rem">
                        ${formatFecha(p.fecha)}
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:0.8rem">
                    <span class="badge badge-${p.estado}">
                        ${p.estado.replace('_',' ')}
                    </span>
                    <span style="font-weight:700">
                        $${parseFloat(p.total).toLocaleString('es-AR')}
                    </span>
                </div>
            </div>
        `).join('');

    document.getElementById('modalContenido').innerHTML = `
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem">
            <div class="avatar" style="width:52px; height:52px; font-size:1.3rem">
                ${usuario.nombre.charAt(0).toUpperCase()}
            </div>
            <div>
                <h3 style="font-size:1.1rem">${usuario.nombre}</h3>
                <div style="color:var(--text-muted); font-size:0.85rem">${usuario.email}</div>
            </div>
        </div>
        <div class="detalle-grid">
            <div class="detalle-item">
                <label><i class="fas fa-calendar"></i> Registrado</label>
                <span>${formatFecha(usuario.fecha_registro)}</span>
            </div>
            <div class="detalle-item">
                <label><i class="fas fa-shopping-bag"></i> Total pedidos</label>
                <span>${usuario.total_pedidos}</span>
            </div>
            <div class="detalle-item" style="grid-column:1/-1">
                <label><i class="fas fa-dollar-sign"></i> Total gastado</label>
                <span style="color:var(--primary); font-size:1.1rem">
                    $${parseFloat(usuario.total_gastado || 0).toLocaleString('es-AR')}
                </span>
            </div>
        </div>
        <div style="margin-top:1.5rem">
            <h4 style="font-size:0.9rem; color:var(--text-muted); margin-bottom:0.8rem">
                <i class="fas fa-history"></i> HISTORIAL DE PEDIDOS
            </h4>
            <div class="pedidos-lista">${pedidosHTML}</div>
        </div>`;

    document.getElementById('modalUsuario').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modalUsuario').classList.remove('active');
}

cargarUsuarios();
</script>

</body>
</html>