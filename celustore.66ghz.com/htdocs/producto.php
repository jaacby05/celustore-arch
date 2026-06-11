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
    <title>Producto — CeluStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .producto-layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 2.5rem;
            align-items: start;
            margin-bottom: 3rem;
        }
        @media (max-width: 900px) {
            .producto-layout { grid-template-columns: 1fr; }
        }
        .galeria { position: sticky; top: 80px; }
        .galeria-principal {
            width: 100%;
            height: 380px;
            object-fit: contain;
            background: rgba(255,255,255,0.03);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: zoom-in;
            transition: transform 0.2s;
        }
        .galeria-principal:hover { transform: scale(1.02); }
        .galeria-thumbs { display: flex; gap: 0.7rem; flex-wrap: wrap; }
        .thumb {
            width: 72px;
            height: 72px;
            object-fit: contain;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            border: 2px solid var(--border);
            padding: 4px;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .thumb:hover, .thumb.active { border-color: var(--primary); }
        .producto-page-info { display: flex; flex-direction: column; gap: 1rem; }
        .producto-page-marca {
            font-size: 0.8rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }
        .producto-page-nombre { font-size: 1.8rem; font-weight: 800; line-height: 1.2; }
        .producto-page-precio { font-size: 2.2rem; font-weight: 800; color: var(--primary); }
        .stock-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            width: fit-content;
        }
        .stock-ok      { background: rgba(76,175,80,0.15);  color: #81c784; }
        .stock-bajo    { background: rgba(255,152,0,0.15);  color: #ffb74d; }
        .stock-agotado { background: rgba(244,67,54,0.15);  color: #e57373; }
        .cantidad-selector { display: flex; align-items: center; gap: 1rem; }
        .cantidad-selector label { color: var(--text-muted); font-size: 0.9rem; }
        .cantidad-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .cantidad-control button {
            width: 38px;
            height: 38px;
            background: rgba(255,255,255,0.05);
            border: none;
            color: var(--text);
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .cantidad-control button:hover { background: var(--primary); }
        .cantidad-control input {
            width: 55px;
            height: 38px;
            text-align: center;
            background: transparent;
            border: none;
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            color: var(--text);
            font-size: 1rem;
        }
        .compra-botones { display: flex; flex-direction: column; gap: 0.8rem; }
        .compra-botones .btn { padding: 0.9rem; font-size: 1rem; justify-content: center; }
        .aviso-admin {
            padding: 1rem 1.2rem;
            background: rgba(255,152,0,0.1);
            border: 1px solid rgba(255,152,0,0.3);
            border-radius: var(--radius);
            color: #ffb74d;
            font-size: 0.9rem;
            text-align: center;
            line-height: 1.6;
        }
        .aviso-admin i { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
        .tabs { margin-top: 1rem; }
        .tabs-nav {
            display: flex;
            border-bottom: 2px solid var(--border);
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }
        .tab-btn {
            padding: 0.8rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            margin-bottom: -2px;
            transition: all 0.2s;
        }
        .tab-btn:hover  { color: var(--text); }
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        .specs-seccion {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1.2rem;
            overflow: hidden;
        }
        .specs-seccion-titulo {
            background: rgba(108,99,255,0.1);
            padding: 0.8rem 1.2rem;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .specs-tabla { width: 100%; border-collapse: collapse; }
        .specs-tabla tr:nth-child(even) td { background: rgba(255,255,255,0.02); }
        .specs-tabla td {
            padding: 0.7rem 1.2rem;
            font-size: 0.88rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .specs-tabla tr:last-child td { border-bottom: none; }
        .specs-tabla td:first-child { color: var(--text-muted); width: 45%; font-weight: 600; }
        .resena-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
            margin-bottom: 1rem;
        }
        .resena-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }
        .resena-autor { font-weight: 700; font-size: 0.95rem; }
        .resena-fecha { color: var(--text-muted); font-size: 0.75rem; margin-top: 0.2rem; }
        .resena-titulo { font-weight: 600; margin-bottom: 0.5rem; }
        .resena-comentario { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; }
        .resena-caract { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.8rem; }
        .caract-badge {
            background: rgba(108,99,255,0.1);
            border: 1px solid rgba(108,99,255,0.2);
            border-radius: 20px;
            padding: 0.2rem 0.8rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .star-selector { display: flex; gap: 0.5rem; font-size: 1.8rem; cursor: pointer; }
        .star-selector span { transition: color 0.1s; }
        .promedio-box {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .promedio-num { font-size: 3rem; font-weight: 800; color: var(--primary); line-height: 1; }
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.92);
            z-index: 5000;
            align-items: center;
            justify-content: center;
        }
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 8px; }
        .lightbox-close {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        .breadcrumb a { color: var(--text-muted); }
        .breadcrumb a:hover { color: var(--primary); }
        .breadcrumb i { font-size: 0.7rem; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <i class="fas fa-mobile-alt"></i> Celu<span>Store</span>
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
            <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
        </span>
        <a href="api/auth/logout.php" class="btn btn-secondary btn-sm">Salir</a>
    </div>
</nav>

<div class="container page-content">
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <i class="fas fa-chevron-right"></i>
        <span id="breadcrumbNombre">Producto</span>
    </div>
    <div id="contenidoProducto">
        <p style="color:var(--text-muted)">Cargando producto...</p>
    </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="cerrarLightbox()">
    <button class="lightbox-close"><i class="fas fa-times"></i></button>
    <img id="lightboxImg" src="" alt="">
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
const esAdmin    = <?= $_SESSION['usuario_rol'] === 'admin' ? 'true' : 'false' ?>;
const logueado   = true;
const urlParams  = new URLSearchParams(window.location.search);
const productoId = urlParams.get('id');

const ICONOS_SECCIONES = {
    'General':        'fa-info-circle',
    'Pantalla':       'fa-desktop',
    'Camara':         'fa-camera',
    'Cámara':         'fa-camera',
    'Rendimiento':    'fa-microchip',
    'Almacenamiento': 'fa-hdd',
    'Bateria':        'fa-battery-full',
    'Batería':        'fa-battery-full',
    'Conectividad':   'fa-wifi',
    'Diseño':         'fa-ruler',
    'Software':       'fa-mobile-alt',
};

function getIcono(nombre) {
    return ICONOS_SECCIONES[nombre] || 'fa-list';
}

async function cargarProducto() {
    if (!productoId) {
        window.location.href = 'index.php';
        return;
    }

    const res  = await fetch(`api/productos/detalle.php?id=${productoId}`);
    const data = await res.json();

    if (!data.producto) {
        window.location.href = 'index.php';
        return;
    }

    const p = data.producto;
    document.title = `${p.nombre} — CeluStore`;
    document.getElementById('breadcrumbNombre').textContent = p.nombre;

    const imagenes = p.imagenes && p.imagenes.length > 0
        ? p.imagenes
        : [p.imagen || 'assets/img/no-image.png'];

    let stockHTML, stockClass;
    if (p.stock == 0) {
        stockHTML  = '❌ Sin stock';
        stockClass = 'stock-agotado';
    } else if (p.stock <= 5) {
        stockHTML  = `⚠️ Últimas ${p.stock} unidades`;
        stockClass = 'stock-bajo';
    } else {
        stockHTML  = `✅ En stock (${p.stock} disponibles)`;
        stockClass = 'stock-ok';
    }

    let specsHTML = '';
    if (p.especificaciones && p.especificaciones.length > 0) {
        specsHTML = p.especificaciones.map(seccion => `
            <div class="specs-seccion">
                <div class="specs-seccion-titulo">
                    <i class="fas ${getIcono(seccion.seccion)}"></i>
                    ${seccion.seccion}
                </div>
                <table class="specs-tabla">
                    ${seccion.datos.map(dato => `
                        <tr>
                            <td>${dato.clave}</td>
                            <td>${dato.valor}</td>
                        </tr>
                    `).join('')}
                </table>
            </div>
        `).join('');
    } else {
        specsHTML = '<p style="color:var(--text-muted)">Sin especificaciones cargadas.</p>';
    }

    let compraHTML = '';
    if (esAdmin) {
        compraHTML = `
            <div class="aviso-admin">
                <i class="fas fa-info-circle"></i>
                Los administradores no pueden realizar compras.<br>
                <small style="color:var(--text-muted)">
                    Si necesitás comprar, creá una cuenta de cliente aparte.
                </small>
            </div>`;
    } else if (p.stock > 0) {
        compraHTML = `
            <div class="cantidad-selector">
                <label>Cantidad:</label>
                <div class="cantidad-control">
                    <button onclick="cambiarCantidad(-1)">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" id="cantidad" value="1"
                           min="1" max="${p.stock}" readonly>
                    <button onclick="cambiarCantidad(1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="compra-botones">
                <button class="btn btn-primary"
                        onclick="agregarAlCarrito(${p.id})">
                    <i class="fas fa-cart-plus"></i> Agregar al carrito
                </button>
                <button class="btn btn-success"
                        onclick="comprarAhora(${p.id})">
                    <i class="fas fa-bolt"></i> Comprar ahora
                </button>
            </div>`;
    } else {
        compraHTML = `
            <button class="btn btn-secondary" disabled style="opacity:0.5">
                <i class="fas fa-times"></i> Sin stock disponible
            </button>`;
    }

    document.getElementById('contenidoProducto').innerHTML = `
        <div class="producto-layout">
            <div class="galeria">
                <img class="galeria-principal" id="imgPrincipal"
                     src="${imagenes[0]}"
                     alt="${p.nombre}"
                     onerror="this.src='assets/img/no-image.png'"
                     onclick="abrirLightbox(this.src)">
                <div class="galeria-thumbs">
                    ${imagenes.map((img, i) => `
                        <img class="thumb ${i === 0 ? 'active' : ''}"
                             src="${img}"
                             alt="Imagen ${i+1}"
                             onerror="this.src='assets/img/no-image.png'"
                             onclick="cambiarImagen(this, '${img}')">
                    `).join('')}
                </div>
            </div>
            <div class="producto-page-info">
                <div class="producto-page-marca">
                    ${p.marca || ''} ${p.categoria ? '· ' + p.categoria : ''}
                </div>
                <h1 class="producto-page-nombre">${p.nombre}</h1>
                <div class="producto-page-precio">
                    $${parseFloat(p.precio).toLocaleString('es-AR')}
                </div>
                <div class="stock-info ${stockClass}">
                    ${stockHTML}
                </div>
                ${compraHTML}
                <div class="tabs">
                    <div class="tabs-nav">
                        <button class="tab-btn active"
                                onclick="cambiarTab(this, 'descripcion')">
                            <i class="fas fa-align-left"></i> Descripción
                        </button>
                        <button class="tab-btn"
                                onclick="cambiarTab(this, 'specs')">
                            <i class="fas fa-list-ul"></i> Especificaciones
                        </button>
                        <button class="tab-btn"
                                onclick="cambiarTab(this, 'resenas')">
                            <i class="fas fa-star"></i> Reseñas
                        </button>
                    </div>
                    <div class="tab-panel active" id="tab-descripcion">
                        <p style="color:var(--text-muted); line-height:1.8; font-size:0.95rem">
                            ${p.descripcion || 'Sin descripción disponible.'}
                        </p>
                    </div>
                    <div class="tab-panel" id="tab-specs">
                        ${specsHTML}
                    </div>
                    <div class="tab-panel" id="tab-resenas">
                        <div class="promedio-box">
                            <div style="text-align:center">
                                <div class="promedio-num" id="promedioNum">—</div>
                                <div id="estrellasProm"
                                     style="color:#ffd700; font-size:1.2rem; margin:0.3rem 0">
                                </div>
                                <div style="color:var(--text-muted); font-size:0.8rem"
                                     id="totalResenas">Sin reseñas</div>
                            </div>
                            <div style="flex:1; color:var(--text-muted);
                                        font-size:0.9rem; line-height:1.6">
                                Las reseñas son de clientes que compraron este producto.
                                Tu opinión ayuda a otros compradores.
                            </div>
                        </div>
                        ${esAdmin ? '' : `
                        <div class="resena-card" style="margin-bottom:1.5rem">
                            <h4 style="margin-bottom:1rem; font-size:1rem">
                                <i class="fas fa-pen"></i> Dejá tu reseña
                            </h4>
                            <div class="form-group">
                                <label>Puntuación general *</label>
                                <div class="star-selector" id="starSelector">
                                    <span onclick="setPuntuacion(1)" class="star"
                                          data-v="1" style="color:var(--text-muted)">☆</span>
                                    <span onclick="setPuntuacion(2)" class="star"
                                          data-v="2" style="color:var(--text-muted)">☆</span>
                                    <span onclick="setPuntuacion(3)" class="star"
                                          data-v="3" style="color:var(--text-muted)">☆</span>
                                    <span onclick="setPuntuacion(4)" class="star"
                                          data-v="4" style="color:var(--text-muted)">☆</span>
                                    <span onclick="setPuntuacion(5)" class="star"
                                          data-v="5" style="color:var(--text-muted)">☆</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Título</label>
                                <input type="text" id="resenaTitle" class="form-control"
                                       placeholder="Resumen de tu experiencia">
                            </div>
                            <div class="form-group">
                                <label>Comentario *</label>
                                <textarea id="resenaComentario" class="form-control"
                                          rows="3"
                                          placeholder="Contá tu experiencia con el producto...">
                                </textarea>
                            </div>
                            <div class="form-group">
                                <label>Calificaciones por aspecto (opcional)</label>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.8rem">
                                    <div>
                                        <label style="font-size:0.8rem; color:var(--text-muted)">
                                            🔋 Batería
                                        </label>
                                        <select id="caract_bateria" class="form-control">
                                            <option value="">—</option>
                                            <option value="1">1 - Muy mala</option>
                                            <option value="2">2 - Mala</option>
                                            <option value="3">3 - Regular</option>
                                            <option value="4">4 - Buena</option>
                                            <option value="5">5 - Excelente</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:0.8rem; color:var(--text-muted)">
                                            📷 Cámara
                                        </label>
                                        <select id="caract_camara" class="form-control">
                                            <option value="">—</option>
                                            <option value="1">1 - Muy mala</option>
                                            <option value="2">2 - Mala</option>
                                            <option value="3">3 - Regular</option>
                                            <option value="4">4 - Buena</option>
                                            <option value="5">5 - Excelente</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:0.8rem; color:var(--text-muted)">
                                            ⚡ Rendimiento
                                        </label>
                                        <select id="caract_rendimiento" class="form-control">
                                            <option value="">—</option>
                                            <option value="1">1 - Muy malo</option>
                                            <option value="2">2 - Malo</option>
                                            <option value="3">3 - Regular</option>
                                            <option value="4">4 - Bueno</option>
                                            <option value="5">5 - Excelente</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:0.8rem; color:var(--text-muted)">
                                            💰 Precio/Calidad
                                        </label>
                                        <select id="caract_precio" class="form-control">
                                            <option value="">—</option>
                                            <option value="1">1 - Muy mala</option>
                                            <option value="2">2 - Mala</option>
                                            <option value="3">3 - Regular</option>
                                            <option value="4">4 - Buena</option>
                                            <option value="5">5 - Excelente</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="alertaResena"></div>
                            <button class="btn btn-primary" onclick="publicarResena()">
                                <i class="fas fa-paper-plane"></i> Publicar reseña
                            </button>
                        </div>`}
                        <div id="listaResenas">
                            <p style="color:var(--text-muted)">Cargando reseñas...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

    if (!esAdmin) actualizarBadgeCarrito();
    cargarResenas(productoId);
}

function cambiarImagen(el, src) {
    document.getElementById('imgPrincipal').src = src;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function cambiarCantidad(delta) {
    const input = document.getElementById('cantidad');
    const max   = parseInt(input.max);
    let val     = parseInt(input.value) + delta;
    if (val < 1)   val = 1;
    if (val > max) val = max;
    input.value = val;
}

function cambiarTab(btn, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

function abrirLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('active');
}

function cerrarLightbox() {
    document.getElementById('lightbox').classList.remove('active');
}

async function agregarAlCarrito(prodId) {
    const cantidad = parseInt(document.getElementById('cantidad').value);
    const res  = await fetch('api/carrito/agregar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ producto_id: prodId, cantidad })
    });
    const data = await res.json();
    if (data.success) {
        mostrarToast(data.mensaje, 'success');
        actualizarBadgeCarrito();
    } else {
        mostrarToast(data.error, 'error');
    }
}

async function comprarAhora(prodId) {
    await agregarAlCarrito(prodId);
    setTimeout(() => window.location.href = 'carrito.php', 800);
}

async function actualizarBadgeCarrito() {
    const res   = await fetch('api/carrito/listar.php');
    const data  = await res.json();
    const badge = document.getElementById('cartBadge');
    if (badge && data.items) badge.textContent = data.items.length;
}

let puntuacionSeleccionada = 0;

function setPuntuacion(valor) {
    puntuacionSeleccionada = valor;
    document.querySelectorAll('.star').forEach(s => {
        const v       = parseInt(s.dataset.v);
        s.textContent = v <= valor ? '★' : '☆';
        s.style.color = v <= valor ? '#ffd700' : 'var(--text-muted)';
    });
}

function estrellas(num) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<span style="color:${i <= num ? '#ffd700' : 'var(--border)'}">★</span>`;
    }
    return html;
}

async function cargarResenas(prodId) {
    const res  = await fetch(`api/resenas/mongodb.php?producto_id=${prodId}`);
    const data = await res.json();

    if (!data.success) return;

    const promedioEl  = document.getElementById('promedioNum');
    const estrellasEl = document.getElementById('estrellasProm');
    const totalEl     = document.getElementById('totalResenas');

    if (promedioEl) {
        promedioEl.textContent = data.promedio || '—';
        estrellasEl.innerHTML  = data.promedio ? estrellas(Math.round(data.promedio)) : '';
        totalEl.textContent    = data.total > 0
            ? `${data.total} reseña${data.total !== 1 ? 's' : ''}`
            : 'Sin reseñas aún';
    }

    const lista = document.getElementById('listaResenas');
    if (!lista) return;

    if (!data.resenas || data.resenas.length === 0) {
        lista.innerHTML = `
            <div style="text-align:center; padding:2rem; color:var(--text-muted)">
                <i class="fas fa-comment-slash"
                   style="font-size:2rem; margin-bottom:0.5rem; display:block"></i>
                Todavía no hay reseñas. ¡Sé el primero en opinar!
            </div>`;
        return;
    }

    lista.innerHTML = data.resenas.map(r => {
        const caract     = r.caracteristicas || {};
        const nombresAspecto = {
            bateria:        '🔋 Batería',
            camara:         '📷 Cámara',
            rendimiento:    '⚡ Rendimiento',
            precio_calidad: '💰 Precio/Calidad'
        };
        const caractHTML = Object.keys(caract).length > 0
            ? `<div class="resena-caract">
                ${Object.entries(caract).map(([k, v]) => v ? `
                    <span class="caract-badge">
                        ${nombresAspecto[k] || k}: ${estrellas(v)}
                    </span>
                ` : '').join('')}
               </div>`
            : '';

        const resenaId = r._id?.$oid || r._id || r.id || '';
        const fecha    = r.createdAt
            ? new Date(r.createdAt).toLocaleDateString('es-AR')
            : (r.fecha ? r.fecha.substring(0, 10) : '');

        return `
            <div class="resena-card">
                <div class="resena-header">
                    <div>
                        <div class="resena-autor">${r.usuario_nombre}</div>
                        <div class="resena-fecha">${fecha}</div>
                    </div>
                    <div>${estrellas(r.puntuacion)}</div>
                </div>
                ${r.titulo ? `<div class="resena-titulo">${r.titulo}</div>` : ''}
                <div class="resena-comentario">${r.comentario}</div>
                ${caractHTML}
                <div style="margin-top:0.8rem">
                    <button class="btn btn-sm btn-secondary"
                            onclick="marcarUtil('${resenaId}')">
                        <i class="fas fa-thumbs-up"></i>
                        Útil (${r.util || 0})
                    </button>
                </div>
            </div>`;
    }).join('');
}

async function publicarResena() {
    if (puntuacionSeleccionada === 0) {
        document.getElementById('alertaResena').innerHTML =
            '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Seleccioná una puntuación</div>';
        return;
    }

    const comentario = document.getElementById('resenaComentario').value.trim();
    if (!comentario) {
        document.getElementById('alertaResena').innerHTML =
            '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> El comentario es obligatorio</div>';
        return;
    }

    const caracteristicas = {};
    const bat = document.getElementById('caract_bateria').value;
    const cam = document.getElementById('caract_camara').value;
    const ren = document.getElementById('caract_rendimiento').value;
    const pre = document.getElementById('caract_precio').value;

    // ── CORRECCIÓN: claves sin tilde para que coincidan con MongoDB ──
    if (bat) caracteristicas['bateria']        = parseInt(bat);
    if (cam) caracteristicas['camara']         = parseInt(cam);
    if (ren) caracteristicas['rendimiento']    = parseInt(ren);
    if (pre) caracteristicas['precio_calidad'] = parseInt(pre);

    const res  = await fetch('api/resenas/mongodb.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            producto_id:     parseInt(productoId),
            puntuacion:      puntuacionSeleccionada,
            titulo:          document.getElementById('resenaTitle').value.trim(),
            comentario:      comentario,
            caracteristicas: caracteristicas
        })
    });
    const data = await res.json();

    if (data.success) {
        mostrarToast('¡Reseña publicada!', 'success');
        document.getElementById('resenaComentario').value   = '';
        document.getElementById('resenaTitle').value        = '';
        document.getElementById('caract_bateria').value     = '';
        document.getElementById('caract_camara').value      = '';
        document.getElementById('caract_rendimiento').value = '';
        document.getElementById('caract_precio').value      = '';
        document.getElementById('alertaResena').innerHTML   = '';
        setPuntuacion(0);
        cargarResenas(productoId);
    } else {
        document.getElementById('alertaResena').innerHTML =
            `<div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> ${data.error}
             </div>`;
    }
}

async function marcarUtil(id) {
    if (!id) return;
    const res  = await fetch('api/resenas/mongodb.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    });
    const data = await res.json();
    if (data.success) {
        mostrarToast('Marcado como útil', 'success');
        cargarResenas(productoId);
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

cargarProducto();
</script>

</body>
</html>