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
    <title>Pedidos — CeluStore Admin</title>
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
        .tabla-pedidos {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .tabla-pedidos th {
            text-align: left;
            padding: 0.8rem 1rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
            background: var(--dark2);
        }
        .tabla-pedidos td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .tabla-pedidos tr:hover td { background: rgba(255,255,255,0.02); }
        .estado-select {
            background: var(--dark);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .detalle-panel {
            background: var(--dark2);
            border-top: 1px solid var(--border);
            padding: 1.2rem 1.5rem;
            display: none;
        }
        .detalle-panel.open { display: block; }
        .filtros-pedidos {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.2rem;
            align-items: flex-end;
        }
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
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="productos.php"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="pedidos.php" class="active"><i class="fas fa-shopping-bag"></i> Pedidos</a></li>
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
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.5rem">
                <i class="fas fa-shopping-bag"></i> Gestión de Pedidos
            </h1>
        </div>

        <div class="filtros-pedidos">
            <div class="form-group" style="margin:0; min-width:200px">
                <label>Buscar por orden o cliente</label>
                <input type="text" id="filtroBusqueda" class="form-control"
                       placeholder="🔍 Nro. orden o nombre..."
                       oninput="filtrarPedidos()">
            </div>
            <div class="form-group" style="margin:0">
                <label>Filtrar por estado</label>
                <select id="filtroEstado" class="form-control" onchange="filtrarPedidos()">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_preparacion">En preparación</option>
                    <option value="en_camino">En camino</option>
                    <option value="entregado">Entregado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </div>

        <div id="contadorPedidos"
             style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1rem"></div>

        <div style="background:var(--card-bg); border:1px solid var(--border);
                    border-radius:var(--radius); overflow:hidden; overflow-x:auto">
            <table class="tabla-pedidos">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nro. Orden</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <tr><td colspan="8" style="text-align:center;
                        color:var(--text-muted); padding:2rem">
                        Cargando...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let pedidosData = [];

function formatFecha(f) {
    return new Date(f).toLocaleDateString('es-AR', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit'
    });
}

async function cargarPedidos() {
    const res  = await fetch('../api/admin/pedidos.php');
    const data = await res.json();
    pedidosData = data.pedidos || [];
    filtrarPedidos();
}

function filtrarPedidos() {
    const q      = document.getElementById('filtroBusqueda').value.toLowerCase();
    const estado = document.getElementById('filtroEstado').value;

    const filtrados = pedidosData.filter(p => {
        const matchQ = p.numero_orden.toLowerCase().includes(q) ||
                       p.cliente.toLowerCase().includes(q);
        const matchE = !estado || p.estado === estado;
        return matchQ && matchE;
    });

    document.getElementById('contadorPedidos').textContent =
        `Mostrando ${filtrados.length} de ${pedidosData.length} pedidos`;

    renderTabla(filtrados);
}

function renderTabla(lista) {
    const tbody = document.getElementById('tablaBody');

    if (lista.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;
            color:var(--text-muted); padding:2rem">
            No se encontraron pedidos
        </td></tr>`;
        return;
    }

    tbody.innerHTML = lista.map(p => `
        <tr>
            <td>
                <button class="btn btn-sm btn-secondary"
                        onclick="toggleDetalle(${p.id})"
                        title="Ver detalle">
                    <i class="fas fa-eye" id="eye-${p.id}"></i>
                </button>
            </td>
            <td style="font-weight:700; color:var(--primary); font-size:0.8rem">
                ${p.numero_orden}
            </td>
            <td>
                <div style="font-weight:600">${p.cliente}</div>
                <div style="color:var(--text-muted); font-size:0.8rem">${p.email}</div>
            </td>
            <td style="color:var(--text-muted)">${formatFecha(p.fecha)}</td>
            <td style="font-weight:700">
                $${parseFloat(p.total).toLocaleString('es-AR')}
            </td>
            <td style="color:var(--text-muted)">
                ${p.metodo_pago === 'tarjeta' ? '💳 Tarjeta' : '🏦 Transferencia'}
            </td>
            <td>
                <span class="badge badge-${p.estado}" id="badge-${p.id}">
                    ${p.estado.replace('_',' ')}
                </span>
            </td>
            <td>
                <select class="estado-select"
                        onchange="cambiarEstado(${p.id}, this.value)">
                    <option value="">— Cambiar —</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_preparacion">En preparación</option>
                    <option value="en_camino">En camino</option>
                    <option value="entregado">Entregado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="8" style="padding:0">
                <div class="detalle-panel" id="detalle-${p.id}">
                    <strong style="font-size:0.85rem; color:var(--text-muted)">
                        DETALLE DEL PEDIDO
                    </strong>
                    <div style="margin-top:0.5rem; font-size:0.85rem; color:var(--text-muted)">
                        <i class="fas fa-map-marker-alt"></i>
                        Envío a: <strong style="color:var(--text)">${p.domicilio_envio}</strong>
                    </div>
                    ${p.telefono_contacto ? `
                    <div style="margin-top:0.3rem; font-size:0.85rem; color:var(--text-muted)">
                        <i class="fas fa-phone"></i>
                        Teléfono: <strong style="color:var(--text)">${p.telefono_contacto}</strong>
                    </div>` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

async function cambiarEstado(id, nuevoEstado) {
    if (!nuevoEstado) return;

    const res  = await fetch('../api/admin/pedidos.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, estado: nuevoEstado })
    });
    const data = await res.json();

    if (data.success) {
        mostrarToast('Estado actualizado correctamente', 'success');
        const badge = document.getElementById(`badge-${id}`);
        if (badge) {
            badge.className   = `badge badge-${nuevoEstado}`;
            badge.textContent = nuevoEstado.replace('_', ' ');
        }
        const pedido = pedidosData.find(p => p.id == id);
        if (pedido) pedido.estado = nuevoEstado;
    } else {
        mostrarToast(data.error, 'error');
    }
}

function toggleDetalle(id) {
    const panel = document.getElementById(`detalle-${id}`);
    const icon  = document.getElementById(`eye-${id}`);
    const isOpen = panel.classList.contains('open');
    panel.classList.toggle('open', !isOpen);
    icon.className = isOpen ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function mostrarToast(msg, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    const iconos    = { success:'✅', error:'❌', warning:'⚠️' };
    const toast     = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.innerHTML = `<span>${iconos[tipo]}</span> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

cargarPedidos();
</script>

</body>
</html>