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
    <title>Proveedores — CeluStore Admin</title>
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
        .tabla-proveedores {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .tabla-proveedores th {
            text-align: left;
            padding: 0.8rem 1rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
            background: var(--dark2);
        }
        .tabla-proveedores td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .tabla-proveedores tr:hover td { background: rgba(255,255,255,0.02); }
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
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        .modal h3 { margin-bottom:1.5rem; font-size:1.1rem; }
        .modal-close {
            position: absolute;
            top: 1rem; right: 1rem;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.4rem;
            cursor: pointer;
        }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .proveedor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(108,99,255,0.2);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        @media (max-width: 768px) {
            .admin-layout { grid-template-columns:1fr; }
            .sidebar { height:auto; position:relative; }
            .form-row { grid-template-columns:1fr; }
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
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="proveedores.php" class="active"><i class="fas fa-truck-loading"></i> Proveedores</a></li>
        </ul>
        <hr class="sidebar-divider">
        <ul class="sidebar-menu">
            <li><a href="../index.php"><i class="fas fa-store"></i> Ver Tienda</a></li>
            <li><a href="../api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO -->
    <main class="admin-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
            <h1 style="font-size:1.5rem">
                <i class="fas fa-truck-loading"></i> Proveedores
            </h1>
            <button class="btn btn-primary" id="btnNuevoProveedor">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </button>
        </div>

        <div style="margin-bottom:1.2rem">
            <input type="text" id="filtroBusqueda" class="form-control"
                   placeholder="🔍 Buscar proveedor..."
                   oninput="filtrarProveedores()"
                   style="max-width:400px">
        </div>

        <div id="alertaAdmin"></div>

        <div style="background:var(--card-bg); border:1px solid var(--border);
                    border-radius:var(--radius); overflow:hidden; overflow-x:auto">
            <table class="tabla-proveedores">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
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

<!-- MODAL -->
<div class="modal-overlay" id="modalProveedor">
    <div class="modal">
        <button class="modal-close" id="btnCerrarModal">
            <i class="fas fa-times"></i>
        </button>
        <h3 id="modalTitulo">Nuevo Proveedor</h3>
        <input type="hidden" id="proveedorId">

        <div class="form-row">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" id="pNombre" class="form-control"
                       placeholder="Distribuidora XYZ">
            </div>
            <div class="form-group">
                <label>Persona de contacto</label>
                <input type="text" id="pContacto" class="form-control"
                       placeholder="Juan González">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="pEmail" class="form-control"
                       placeholder="contacto@proveedor.com">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" id="pTelefono" class="form-control"
                       placeholder="+54 11 1234-5678">
            </div>
        </div>
        <div class="form-group">
            <label>Dirección</label>
            <input type="text" id="pDireccion" class="form-control"
                   placeholder="Calle, número, ciudad">
        </div>
        <div class="form-group">
            <label>Notas</label>
            <textarea id="pNotas" class="form-control" rows="3"
                      placeholder="Condiciones de pago, tiempo de entrega, observaciones..."></textarea>
        </div>

        <div id="alertaModal"></div>

        <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem">
            <button class="btn btn-secondary" id="btnCancelarModal">Cancelar</button>
            <button class="btn btn-primary" id="btnGuardar">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let proveedoresData = [];

document.getElementById('btnNuevoProveedor').addEventListener('click', abrirModalNuevo);
document.getElementById('btnCerrarModal').addEventListener('click',   cerrarModal);
document.getElementById('btnCancelarModal').addEventListener('click',  cerrarModal);
document.getElementById('btnGuardar').addEventListener('click',        guardarProveedor);
document.getElementById('modalProveedor').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

async function cargarProveedores() {
    const res  = await fetch('../api/admin/proveedores.php');
    const data = await res.json();
    proveedoresData = data.proveedores || [];
    filtrarProveedores();
}

function filtrarProveedores() {
    const q = document.getElementById('filtroBusqueda').value.toLowerCase();
    const filtrados = proveedoresData.filter(p =>
        p.nombre.toLowerCase().includes(q) ||
        (p.contacto || '').toLowerCase().includes(q) ||
        (p.email    || '').toLowerCase().includes(q)
    );
    renderTabla(filtrados);
}

function renderTabla(lista) {
    const tbody = document.getElementById('tablaBody');
    if (lista.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;
            color:var(--text-muted); padding:2rem">No hay proveedores registrados</td></tr>`;
        return;
    }
    tbody.innerHTML = lista.map(p => `
        <tr>
            <td>
                <div style="display:flex; align-items:center; gap:0.8rem">
                    <div class="proveedor-avatar">
                        ${p.nombre.charAt(0).toUpperCase()}
                    </div>
                    <div style="font-weight:600">${p.nombre}</div>
                </div>
            </td>
            <td style="color:var(--text-muted)">${p.contacto || '—'}</td>
            <td style="color:var(--text-muted)">${p.email    || '—'}</td>
            <td style="color:var(--text-muted)">${p.telefono || '—'}</td>
            <td>
                <span class="badge ${p.activo == 1 ? 'badge-entregado' : 'badge-cancelado'}">
                    ${p.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div style="display:flex; gap:0.5rem">
                    <button class="btn btn-sm btn-primary"
                            onclick="editarProveedor(${p.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="eliminarProveedor(${p.id}, '${p.nombre.replace(/'/g,"\\'")}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function abrirModalNuevo() {
    document.getElementById('modalTitulo').textContent  = 'Nuevo Proveedor';
    document.getElementById('proveedorId').value        = '';
    document.getElementById('pNombre').value            = '';
    document.getElementById('pContacto').value          = '';
    document.getElementById('pEmail').value             = '';
    document.getElementById('pTelefono').value          = '';
    document.getElementById('pDireccion').value         = '';
    document.getElementById('pNotas').value             = '';
    document.getElementById('alertaModal').innerHTML    = '';
    document.getElementById('modalProveedor').classList.add('active');
}

function editarProveedor(id) {
    const p = proveedoresData.find(x => x.id == id);
    if (!p) return;
    document.getElementById('modalTitulo').textContent  = 'Editar Proveedor';
    document.getElementById('proveedorId').value        = p.id;
    document.getElementById('pNombre').value            = p.nombre;
    document.getElementById('pContacto').value          = p.contacto  || '';
    document.getElementById('pEmail').value             = p.email     || '';
    document.getElementById('pTelefono').value          = p.telefono  || '';
    document.getElementById('pDireccion').value         = p.direccion || '';
    document.getElementById('pNotas').value             = p.notas     || '';
    document.getElementById('alertaModal').innerHTML    = '';
    document.getElementById('modalProveedor').classList.add('active');
}

async function guardarProveedor() {
    const id     = document.getElementById('proveedorId').value;
    const alerta = document.getElementById('alertaModal');
    const btn    = document.getElementById('btnGuardar');

    const datos = {
        nombre:    document.getElementById('pNombre').value.trim(),
        contacto:  document.getElementById('pContacto').value.trim(),
        email:     document.getElementById('pEmail').value.trim(),
        telefono:  document.getElementById('pTelefono').value.trim(),
        direccion: document.getElementById('pDireccion').value.trim(),
        notas:     document.getElementById('pNotas').value.trim()
    };

    if (!datos.nombre) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> El nombre es obligatorio</div>';
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    if (id) datos.id = parseInt(id);

    const res  = await fetch('../api/admin/proveedores.php', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    });
    const data = await res.json();

    btn.disabled  = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Guardar';

    if (data.success) {
        mostrarToast(data.mensaje, 'success');
        cerrarModal();
        cargarProveedores();
    } else {
        alerta.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
    }
}

async function eliminarProveedor(id, nombre) {
    if (!confirm(`¿Querés desactivar al proveedor "${nombre}"?`)) return;
    const res  = await fetch('../api/admin/proveedores.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
        mostrarToast('Proveedor desactivado', 'success');
        cargarProveedores();
    } else {
        mostrarToast(data.error, 'error');
    }
}

function cerrarModal() {
    document.getElementById('modalProveedor').classList.remove('active');
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

cargarProveedores();
</script>

</body>
</html>