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
    <title>Productos — CeluStore Admin</title>
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
        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .tabla-productos th {
            text-align: left;
            padding: 0.8rem 1rem;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--border);
            background: var(--dark2);
        }
        .tabla-productos td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
        }
        .tabla-productos tr:hover td { background: rgba(255,255,255,0.02); }
        .producto-thumb {
            width: 50px;
            height: 50px;
            object-fit: contain;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            padding: 4px;
        }
        .acciones { display:flex; gap:0.5rem; }
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display:flex; }
        .modal {
            background: var(--dark2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            width: 90%;
            max-width: 600px;
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
        .imagen-row { display:flex; gap:0.5rem; margin-bottom:0.5rem; }
        .specs-seccion-admin {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.8rem;
        }
        .dato-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 0.5rem;
            margin-bottom: 0.4rem;
        }
        @media (max-width:768px) {
            .admin-layout { grid-template-columns:1fr; }
            .sidebar { height:auto; position:relative; }
            .form-row { grid-template-columns:1fr; }
            .dato-row { grid-template-columns:1fr; }
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
            <li><a href="productos.php" class="active"><i class="fas fa-box"></i> Productos</a></li>
            <li><a href="pedidos.php"><i class="fas fa-shopping-bag"></i> Pedidos</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem">
            <h1 style="font-size:1.5rem">
                <i class="fas fa-box"></i> Gestión de Productos
            </h1>
            <button class="btn btn-primary" id="btnNuevoProducto">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        </div>

        <div style="margin-bottom:1.2rem">
            <input type="text" id="filtroBusqueda" class="form-control"
                   placeholder="🔍 Buscar por nombre, marca o modelo..."
                   oninput="filtrarTabla()"
                   style="max-width:400px">
        </div>

        <div id="alertaAdmin"></div>

        <div style="background:var(--card-bg); border:1px solid var(--border);
                    border-radius:var(--radius); overflow:hidden; overflow-x:auto">
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Marca/Modelo</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
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

<!-- MODAL -->
<div class="modal-overlay" id="modalProducto">
    <div class="modal">
        <button class="modal-close" id="btnCerrarModal">
            <i class="fas fa-times"></i>
        </button>
        <h3 id="modalTitulo">Nuevo Producto</h3>
        <input type="hidden" id="productoId">

        <div class="form-row">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" id="pNombre" class="form-control" placeholder="iPhone 15 Pro">
            </div>
            <div class="form-group">
                <label>Marca *</label>
                <input type="text" id="pMarca" class="form-control" placeholder="Apple">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Modelo</label>
                <input type="text" id="pModelo" class="form-control" placeholder="A3290">
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select id="pCategoria" class="form-control"></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Precio *</label>
                <input type="number" id="pPrecio" class="form-control"
                       placeholder="0.00" min="0" step="0.01">
            </div>
            <div class="form-group">
                <label>Stock *</label>
                <input type="number" id="pStock" class="form-control"
                       placeholder="0" min="0">
            </div>
        </div>
        <div class="form-group">
            <label>Proveedor</label>
            <select id="pProveedor" class="form-control"></select>
        </div>
        <div class="form-group">
            <label>Descripción general</label>
            <textarea id="pDescripcion" class="form-control" rows="2"
                      placeholder="Descripción breve del producto..."></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-images"></i> Imágenes del producto</label>
            <div id="imagenesContainer"></div>
            <button type="button" class="btn btn-sm btn-secondary" id="btnAgregarImagen">
                <i class="fas fa-plus"></i> Agregar imagen
            </button>
        </div>

        <div class="form-group">
            <label><i class="fas fa-list-ul"></i> Especificaciones técnicas</label>
            <div id="seccionesContainer"></div>
            <button type="button" class="btn btn-sm btn-secondary" id="btnAgregarSeccion">
                <i class="fas fa-plus"></i> Agregar sección
            </button>
            <small style="color:var(--text-muted); display:block; margin-top:0.3rem">
                Ejemplos: General, Pantalla, Cámara, Rendimiento, Batería, Conectividad
            </small>
        </div>

        <div id="alertaModal"></div>

        <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.5rem">
            <button class="btn btn-secondary" id="btnCancelarModal">Cancelar</button>
            <button class="btn btn-primary"   id="btnGuardar">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let productosData  = [];
let categoriasData = [];
let proveedoresData = [];

document.getElementById('btnNuevoProducto').addEventListener('click',  abrirModalNuevo);
document.getElementById('btnCerrarModal').addEventListener('click',    cerrarModal);
document.getElementById('btnCancelarModal').addEventListener('click',  cerrarModal);
document.getElementById('btnGuardar').addEventListener('click',        guardarProducto);
document.getElementById('btnAgregarImagen').addEventListener('click',  () => agregarImagenRow(''));
document.getElementById('btnAgregarSeccion').addEventListener('click', () => agregarSeccion('', []));
document.getElementById('modalProducto').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

async function cargarCategorias() {
    const res  = await fetch('../api/productos/categorias.php');
    const data = await res.json();
    categoriasData = data.categorias || [];
    const sel = document.getElementById('pCategoria');
    sel.innerHTML = '<option value="">Sin categoría</option>';
    categoriasData.forEach(c => {
        sel.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
    });
}

async function cargarProveedores() {
    const res  = await fetch('../api/admin/proveedores.php');
    const data = await res.json();
    proveedoresData = data.proveedores || [];
    const sel = document.getElementById('pProveedor');
    sel.innerHTML = '<option value="">Sin proveedor</option>';
    proveedoresData.filter(p => p.activo == 1).forEach(p => {
        sel.innerHTML += `<option value="${p.id}">${p.nombre}</option>`;
    });
}

async function cargarProductos() {
    const res  = await fetch('../api/admin/productos.php');
    const data = await res.json();
    productosData = data.productos || [];
    renderTabla(productosData);
}

function renderTabla(lista) {
    const tbody = document.getElementById('tablaBody');
    if (lista.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;
            color:var(--text-muted); padding:2rem">No hay productos registrados</td></tr>`;
        return;
    }
    tbody.innerHTML = lista.map(p => `
        <tr>
            <td>
                <img class="producto-thumb"
                     src="${p.imagen || '../assets/img/no-image.png'}"
                     alt="${p.nombre}"
                     onerror="this.src='../assets/img/no-image.png'">
            </td>
            <td style="font-weight:600; max-width:180px">${p.nombre}</td>
            <td style="color:var(--text-muted)">
                ${p.marca || '—'} ${p.modelo ? '/ ' + p.modelo : ''}
            </td>
            <td style="font-weight:700; color:var(--primary)">
                $${parseFloat(p.precio).toLocaleString('es-AR')}
            </td>
            <td>
                <span class="badge ${p.stock == 0 ? 'badge-cancelado'
                    : p.stock <= 5 ? 'badge-pendiente' : 'badge-entregado'}">
                    ${p.stock} uds.
                </span>
            </td>
            <td style="color:var(--text-muted)">${p.categoria || '—'}</td>
            <td>
                <span class="badge ${p.activo == 1 ? 'badge-entregado' : 'badge-cancelado'}">
                    ${p.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>
                <div class="acciones">
                    <button class="btn btn-sm btn-primary"
                            onclick="editarProducto(${p.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger"
                            onclick="eliminarProducto(${p.id}, '${p.nombre.replace(/'/g,"\\'")}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filtrarTabla() {
    const q = document.getElementById('filtroBusqueda').value.toLowerCase();
    const filtrados = productosData.filter(p =>
        p.nombre.toLowerCase().includes(q) ||
        (p.marca  || '').toLowerCase().includes(q) ||
        (p.modelo || '').toLowerCase().includes(q)
    );
    renderTabla(filtrados);
}

function agregarImagenRow(url) {
    const container = document.getElementById('imagenesContainer');
    const div       = document.createElement('div');
    div.className   = 'imagen-row';
    const input     = document.createElement('input');
    input.type      = 'text';
    input.className = 'form-control imagen-url';
    input.placeholder = 'URL de imagen';
    input.value     = url || '';
    const btn       = document.createElement('button');
    btn.type        = 'button';
    btn.className   = 'btn btn-sm btn-danger';
    btn.style.flexShrink = '0';
    btn.innerHTML   = '<i class="fas fa-trash"></i>';
    btn.addEventListener('click', function() {
        const rows = document.querySelectorAll('.imagen-row');
        if (rows.length > 1) div.remove();
        else input.value = '';
    });
    div.appendChild(input);
    div.appendChild(btn);
    container.appendChild(div);
}

function getImagenes() {
    return Array.from(document.querySelectorAll('.imagen-url'))
        .map(i => i.value.trim())
        .filter(i => i !== '');
}

function agregarSeccion(nombre, datos) {
    const container  = document.getElementById('seccionesContainer');
    const secDiv     = document.createElement('div');
    secDiv.className = 'specs-seccion-admin';

    const headerDiv  = document.createElement('div');
    headerDiv.style  = 'display:flex; gap:0.5rem; margin-bottom:0.8rem; align-items:center';

    const inputNombre         = document.createElement('input');
    inputNombre.type          = 'text';
    inputNombre.className     = 'form-control seccion-nombre';
    inputNombre.placeholder   = 'Nombre de sección (ej: General, Pantalla...)';
    inputNombre.value         = nombre || '';
    inputNombre.style.fontWeight = '600';

    const btnEliminarSec      = document.createElement('button');
    btnEliminarSec.type       = 'button';
    btnEliminarSec.className  = 'btn btn-sm btn-danger';
    btnEliminarSec.style.flexShrink = '0';
    btnEliminarSec.innerHTML  = '<i class="fas fa-trash"></i>';
    btnEliminarSec.addEventListener('click', () => secDiv.remove());

    headerDiv.appendChild(inputNombre);
    headerDiv.appendChild(btnEliminarSec);

    const datosContainer      = document.createElement('div');
    datosContainer.className  = 'datos-container';

    const btnAgregarDato      = document.createElement('button');
    btnAgregarDato.type       = 'button';
    btnAgregarDato.className  = 'btn btn-sm btn-secondary';
    btnAgregarDato.style.marginTop = '0.5rem';
    btnAgregarDato.innerHTML  = '<i class="fas fa-plus"></i> Agregar dato';
    btnAgregarDato.addEventListener('click', () => agregarDatoRow(datosContainer, '', ''));

    secDiv.appendChild(headerDiv);
    secDiv.appendChild(datosContainer);
    secDiv.appendChild(btnAgregarDato);
    container.appendChild(secDiv);

    if (datos && datos.length > 0) {
        datos.forEach(d => agregarDatoRow(datosContainer, d.clave, d.valor));
    } else {
        agregarDatoRow(datosContainer, '', '');
    }
}

function agregarDatoRow(container, clave, valor) {
    const div         = document.createElement('div');
    div.className     = 'dato-row';

    const inputClave  = document.createElement('input');
    inputClave.type   = 'text';
    inputClave.className = 'form-control dato-clave';
    inputClave.placeholder = 'Característica (ej: Memoria RAM)';
    inputClave.value  = clave || '';

    const inputValor  = document.createElement('input');
    inputValor.type   = 'text';
    inputValor.className = 'form-control dato-valor';
    inputValor.placeholder = 'Valor (ej: 8 GB)';
    inputValor.value  = valor || '';

    const btnEliminar = document.createElement('button');
    btnEliminar.type  = 'button';
    btnEliminar.className = 'btn btn-sm btn-danger';
    btnEliminar.innerHTML = '<i class="fas fa-times"></i>';
    btnEliminar.addEventListener('click', () => div.remove());

    div.appendChild(inputClave);
    div.appendChild(inputValor);
    div.appendChild(btnEliminar);
    container.appendChild(div);
}

function getSecciones() {
    const secciones = [];
    document.querySelectorAll('.specs-seccion-admin').forEach(sec => {
        const nombre = sec.querySelector('.seccion-nombre').value.trim();
        if (!nombre) return;
        const datos = [];
        sec.querySelectorAll('.dato-row').forEach(row => {
            const clave = row.querySelector('.dato-clave').value.trim();
            const valor = row.querySelector('.dato-valor').value.trim();
            if (clave && valor) datos.push({ clave, valor });
        });
        if (datos.length > 0) secciones.push({ seccion: nombre, datos });
    });
    return secciones;
}

function abrirModalNuevo() {
    document.getElementById('modalTitulo').textContent   = 'Nuevo Producto';
    document.getElementById('productoId').value          = '';
    document.getElementById('pNombre').value             = '';
    document.getElementById('pMarca').value              = '';
    document.getElementById('pModelo').value             = '';
    document.getElementById('pPrecio').value             = '';
    document.getElementById('pStock').value              = '';
    document.getElementById('pDescripcion').value        = '';
    document.getElementById('pCategoria').value          = '';
    document.getElementById('pProveedor').value          = '';
    document.getElementById('alertaModal').innerHTML     = '';
    document.getElementById('imagenesContainer').innerHTML  = '';
    document.getElementById('seccionesContainer').innerHTML = '';
    agregarImagenRow('');
    document.getElementById('modalProducto').classList.add('active');
}

function editarProducto(id) {
    const p = productosData.find(x => x.id == id);
    if (!p) return;

    document.getElementById('modalTitulo').textContent   = 'Editar Producto';
    document.getElementById('productoId').value          = p.id;
    document.getElementById('pNombre').value             = p.nombre;
    document.getElementById('pMarca').value              = p.marca       || '';
    document.getElementById('pModelo').value             = p.modelo      || '';
    document.getElementById('pPrecio').value             = p.precio;
    document.getElementById('pStock').value              = p.stock;
    document.getElementById('pDescripcion').value        = p.descripcion || '';
    document.getElementById('pCategoria').value          = p.categoria_id || '';
    document.getElementById('pProveedor').value          = p.proveedor_id || '';
    document.getElementById('alertaModal').innerHTML     = '';
    document.getElementById('imagenesContainer').innerHTML  = '';
    document.getElementById('seccionesContainer').innerHTML = '';

    let imagenes = [];
    try { imagenes = JSON.parse(p.imagenes || '[]'); } catch(e) {}
    if (p.imagen && !imagenes.includes(p.imagen)) imagenes.unshift(p.imagen);
    if (imagenes.length === 0) imagenes = [''];
    imagenes.forEach(url => agregarImagenRow(url));

    let specs = [];
    try { specs = JSON.parse(p.especificaciones || '[]'); } catch(e) {}
    specs.forEach(sec => agregarSeccion(sec.seccion, sec.datos));

    document.getElementById('modalProducto').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modalProducto').classList.remove('active');
}

async function guardarProducto() {
    const id     = document.getElementById('productoId').value;
    const imgs   = getImagenes();
    const specs  = getSecciones();
    const alerta = document.getElementById('alertaModal');
    const btn    = document.getElementById('btnGuardar');

    const datos = {
        nombre:           document.getElementById('pNombre').value.trim(),
        marca:            document.getElementById('pMarca').value.trim(),
        modelo:           document.getElementById('pModelo').value.trim(),
        precio:           parseFloat(document.getElementById('pPrecio').value),
        stock:            parseInt(document.getElementById('pStock').value),
        descripcion:      document.getElementById('pDescripcion').value.trim(),
        categoria_id:     parseInt(document.getElementById('pCategoria').value) || 0,
        proveedor_id:     parseInt(document.getElementById('pProveedor').value) || 0,
        imagen:           imgs[0] || '',
        imagenes:         imgs,
        especificaciones: specs
    };

    if (!datos.nombre || !datos.precio) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Nombre y precio son obligatorios</div>';
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    if (id) datos.id = parseInt(id);

    const res  = await fetch('../api/admin/productos.php', {
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
        cargarProductos();
    } else {
        alerta.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ${data.error}</div>`;
    }
}

async function eliminarProducto(id, nombre) {
    if (!confirm(`¿Querés desactivar "${nombre}"?`)) return;
    const res  = await fetch('../api/admin/productos.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
        mostrarToast('Producto desactivado', 'success');
        cargarProductos();
    } else {
        mostrarToast(data.error, 'error');
    }
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

cargarCategorias();
cargarProveedores();
cargarProductos();
</script>

</body>
</html>