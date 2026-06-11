<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CeluStore — Tienda de Celulares</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <i class="fas fa-mobile-alt"></i>
        Celu<span>Store</span>
    </a>

    <ul class="navbar-menu">
    <li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>

    <?php if ($_SESSION['usuario_rol'] !== 'admin'): ?>
        <li><a href="mis-pedidos.php"><i class="fas fa-box"></i> Mis Pedidos</a></li>
    <?php endif; ?>

    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <li><a href="admin/index.php"><i class="fas fa-cog"></i> Admin</a></li>
    <?php endif; ?>
</ul>

    <div class="navbar-actions">
        <?php if ($_SESSION['usuario_rol'] !== 'admin'): ?>
            <a href="carrito.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge" id="cartBadge">0</span>
            </a>
        <?php endif; ?>
        <span style="color:var(--text-muted); font-size:0.85rem">
            Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
        </span>
        <a href="api/auth/logout.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-sign-out-alt"></i> Salir
        </a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <h1>Los mejores <span>celulares</span><br>al mejor precio</h1>
        <p>Explorá nuestra amplia variedad de smartphones y accesorios</p>
        <a href="#catalogo" class="btn btn-primary">
            <i class="fas fa-search"></i> Ver Catálogo
        </a>
    </div>
</section>

<!-- CATÁLOGO -->
<div class="container page-content" id="catalogo">

    <!-- FILTROS -->
    <div class="filtros">
        <div class="form-group">
            <label>Buscar</label>
            <input type="text" id="busqueda" class="form-control"
                   placeholder="Nombre, marca, modelo...">
        </div>
        <div class="form-group">
            <label>Categoría</label>
            <select id="categoria" class="form-control">
                <option value="">Todas</option>
            </select>
        </div>
        <div class="form-group">
            <label>Precio mínimo</label>
            <input type="number" id="precioMin" class="form-control" placeholder="0">
        </div>
        <div class="form-group">
            <label>Precio máximo</label>
            <input type="number" id="precioMax" class="form-control" placeholder="Sin límite">
        </div>
        <button class="btn btn-primary" onclick="cargarProductos()">
            <i class="fas fa-filter"></i> Filtrar
        </button>
        <button class="btn btn-secondary" onclick="limpiarFiltros()">
            <i class="fas fa-times"></i> Limpiar
        </button>
    </div>

    <!-- GRID DE PRODUCTOS -->
    <div id="productosGrid" class="productos-grid">
        <p style="color:var(--text-muted)">Cargando productos...</p>
    </div>
</div>

<!-- TOAST -->
<div class="toast-container" id="toastContainer"></div>

<script>
const esAdmin = <?= $_SESSION['usuario_rol'] === 'admin' ? 'true' : 'false' ?>;

// ── CARGAR CATEGORÍAS ───────────────────────────────────────
async function cargarCategorias() {
    const res  = await fetch('api/productos/categorias.php');
    const data = await res.json();
    const sel  = document.getElementById('categoria');
    data.categorias.forEach(cat => {
        const opt       = document.createElement('option');
        opt.value       = cat.id;
        opt.textContent = cat.nombre;
        sel.appendChild(opt);
    });
}

// ── CARGAR PRODUCTOS ────────────────────────────────────────
async function cargarProductos() {
    const busqueda  = document.getElementById('busqueda').value;
    const categoria = document.getElementById('categoria').value;
    const precioMin = document.getElementById('precioMin').value || 0;
    const precioMax = document.getElementById('precioMax').value || 99999999;

    let url = `api/productos/listar.php?precio_min=${precioMin}&precio_max=${precioMax}`;
    if (busqueda)  url += `&busqueda=${encodeURIComponent(busqueda)}`;
    if (categoria) url += `&categoria=${categoria}`;

    const grid = document.getElementById('productosGrid');
    grid.innerHTML = '<p style="color:var(--text-muted)">Cargando...</p>';

    const res  = await fetch(url);
    const data = await res.json();

    if (!data.productos || data.productos.length === 0) {
        grid.innerHTML = `
            <div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--text-muted)">
                <i class="fas fa-search" style="font-size:3rem; margin-bottom:1rem; display:block"></i>
                Sin resultados. Intentá con otros filtros.
            </div>`;
        return;
    }

    grid.innerHTML = data.productos.map(p => `
        <div class="producto-card" onclick="verDetalle(${p.id})">
            <img class="producto-img"
                 src="${p.imagen || 'assets/img/no-image.png'}"
                 alt="${p.nombre}"
                 onerror="this.src='assets/img/no-image.png'">
            <div class="producto-info">
                <div class="producto-marca">${p.marca || ''}</div>
                <div class="producto-nombre">${p.nombre}</div>
                <div class="producto-precio">
                    $${parseFloat(p.precio).toLocaleString('es-AR')}
                </div>
                <div class="producto-stock ${p.stock == 0 ? 'sin-stock' : p.stock <= 5 ? 'stock-bajo' : ''}">
                    ${p.stock == 0
                        ? '❌ Sin stock'
                        : p.stock <= 5
                            ? `⚠️ Últimas ${p.stock} unidades`
                            : `✅ En stock`}
                </div>
                ${esAdmin ? `
                <div style="text-align:center; padding:0.5rem;
                            background:rgba(255,152,0,0.1);
                            border:1px solid rgba(255,152,0,0.3);
                            border-radius:8px; color:#ffb74d; font-size:0.8rem">
                    <i class="fas fa-info-circle"></i>
                    Los administradores no pueden comprar
                </div>` : `
                <button class="btn btn-primary btn-block"
                        onclick="event.stopPropagation(); agregarAlCarrito(${p.id})"
                        ${p.stock == 0 ? 'disabled' : ''}>
                    <i class="fas fa-cart-plus"></i>
                    ${p.stock == 0 ? 'Sin stock' : 'Agregar al carrito'}
                </button>`}
            </div>
        </div>
    `).join('');

    if (!esAdmin) actualizarBadgeCarrito();
}

// ── VER DETALLE ─────────────────────────────────────────────
function verDetalle(id) {
    window.location.href = `producto.php?id=${id}`;
}

// ── AGREGAR AL CARRITO ──────────────────────────────────────
async function agregarAlCarrito(productoId, cantidad = 1) {
    const res  = await fetch('api/carrito/agregar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ producto_id: productoId, cantidad: parseInt(cantidad) })
    });
    const data = await res.json();

    if (data.success) {
        mostrarToast(data.mensaje, 'success');
        actualizarBadgeCarrito();
    } else {
        mostrarToast(data.error, 'error');
    }
}

// ── BADGE CARRITO ───────────────────────────────────────────
async function actualizarBadgeCarrito() {
    const res   = await fetch('api/carrito/listar.php');
    const data  = await res.json();
    const badge = document.getElementById('cartBadge');
    if (badge && data.items) {
        badge.textContent = data.items.length;
    }
}

// ── LIMPIAR FILTROS ─────────────────────────────────────────
function limpiarFiltros() {
    document.getElementById('busqueda').value  = '';
    document.getElementById('categoria').value = '';
    document.getElementById('precioMin').value = '';
    document.getElementById('precioMax').value = '';
    cargarProductos();
}

// ── TOAST ───────────────────────────────────────────────────
function mostrarToast(msg, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    const iconos    = { success: '✅', error: '❌', warning: '⚠️' };
    const toast     = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.innerHTML = `<span>${iconos[tipo]}</span> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ── BUSCAR CON ENTER ────────────────────────────────────────
document.getElementById('busqueda').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') cargarProductos();
});

// ── INIT ────────────────────────────────────────────────────
cargarCategorias();
cargarProductos();
</script>

</body>
</html>