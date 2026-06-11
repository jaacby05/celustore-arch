<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['usuario_rol'] === 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito — CeluStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .carrito-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 2rem;
            align-items: start;
        }
        @media (max-width: 768px) {
            .carrito-layout { grid-template-columns: 1fr; }
        }
        .carrito-item {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
            display: flex;
            gap: 1.2rem;
            align-items: center;
            margin-bottom: 1rem;
        }
        .carrito-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            padding: 0.3rem;
            flex-shrink: 0;
        }
        .carrito-item-info { flex: 1; }
        .carrito-item-nombre {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        .carrito-item-precio {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.1rem;
        }
        .carrito-item-subtotal {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .cantidad-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .cantidad-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.05);
            color: var(--text);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .cantidad-btn:hover { background: var(--primary); }
        .cantidad-num {
            width: 40px;
            text-align: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            padding: 0.2rem;
            font-size: 0.95rem;
        }
        .resumen-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            position: sticky;
            top: 80px;
        }
        .resumen-card h3 {
            font-size: 1.1rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
        }
        .resumen-fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .resumen-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            padding-top: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 0.5rem;
        }
        .carrito-vacio {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }
        .carrito-vacio i {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
            color: var(--border);
        }
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
        <li><a href="mis-pedidos.php"><i class="fas fa-box"></i> Mis Pedidos</a></li>
    </ul>
    <div class="navbar-actions">
        <a href="carrito.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-badge" id="cartBadge">0</span>
        </a>
        <span style="color:var(--text-muted); font-size:0.85rem">
            <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
        </span>
        <a href="api/auth/logout.php" class="btn btn-secondary btn-sm">Salir</a>
    </div>
</nav>

<div class="container page-content">
    <h2 style="margin-bottom:1.5rem">
        <i class="fas fa-shopping-cart"></i> Mi Carrito
    </h2>
    <div id="carritoContenido"></div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
async function cargarCarrito() {
    const res   = await fetch('api/carrito/listar.php');
    const data  = await res.json();
    const contenido = document.getElementById('carritoContenido');
    const badge     = document.getElementById('cartBadge');

    if (!data.items || data.items.length === 0) {
        badge.textContent = '0';
        contenido.innerHTML = `
            <div class="carrito-vacio">
                <i class="fas fa-shopping-cart"></i>
                <h3>Tu carrito está vacío</h3>
                <p style="margin:0.5rem 0 1.5rem">
                    Explorá el catálogo y agregá productos
                </p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-store"></i> Ir al catálogo
                </a>
            </div>`;
        return;
    }

    badge.textContent = data.items.length;

    const itemsHTML = data.items.map(item => `
        <div class="carrito-item" id="item-${item.id}">
            <img src="${item.imagen || 'assets/img/no-image.png'}"
                 alt="${item.nombre}"
                 onerror="this.src='assets/img/no-image.png'">
            <div class="carrito-item-info">
                <div class="carrito-item-nombre">${item.nombre}</div>
                <div class="carrito-item-precio">
                    $${parseFloat(item.precio).toLocaleString('es-AR')}
                </div>
                <div class="carrito-item-subtotal">
                    Subtotal: $${parseFloat(item.subtotal).toLocaleString('es-AR')}
                </div>
                <div class="cantidad-control">
                    <button class="cantidad-btn"
                            onclick="cambiarCantidad(${item.id}, ${item.cantidad - 1}, ${item.stock})">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input class="cantidad-num" type="number"
                           value="${item.cantidad}" min="1" max="${item.stock}"
                           onchange="cambiarCantidad(${item.id}, this.value, ${item.stock})">
                    <button class="cantidad-btn"
                            onclick="cambiarCantidad(${item.id}, ${item.cantidad + 1}, ${item.stock})">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <button class="btn btn-danger btn-sm" onclick="eliminarItem(${item.id})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');

    contenido.innerHTML = `
        <div class="carrito-layout">
            <div id="itemsLista">${itemsHTML}</div>
            <div class="resumen-card">
                <h3><i class="fas fa-receipt"></i> Resumen</h3>
                <div class="resumen-fila">
                    <span>Subtotal (${data.items.length} producto${data.items.length !== 1 ? 's' : ''})</span>
                    <span>$${parseFloat(data.subtotal).toLocaleString('es-AR')}</span>
                </div>
                <div class="resumen-fila">
                    <span style="color:var(--text-muted); font-size:0.8rem">
                        <i class="fas fa-info-circle"></i> Envío calculado en el siguiente paso
                    </span>
                </div>
                <div class="resumen-total">
                    <span>Subtotal</span>
                    <span>$${parseFloat(data.subtotal).toLocaleString('es-AR')}</span>
                </div>
                <br>
                <a href="envio.php" class="btn btn-success btn-block">
                    <i class="fas fa-truck"></i> Continuar al envío
                </a>
                <a href="index.php" class="btn btn-secondary btn-block"
                   style="margin-top:0.8rem">
                    <i class="fas fa-arrow-left"></i> Seguir comprando
                </a>
            </div>
        </div>`;
}

async function cambiarCantidad(carritoId, nuevaCantidad, stock) {
    nuevaCantidad = parseInt(nuevaCantidad);
    if (nuevaCantidad <= 0) {
        eliminarItem(carritoId);
        return;
    }
    if (nuevaCantidad > stock) {
        mostrarToast(`Stock máximo disponible: ${stock}`, 'warning');
        cargarCarrito();
        return;
    }
    const res  = await fetch('api/carrito/actualizar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ carrito_id: carritoId, cantidad: nuevaCantidad })
    });
    const data = await res.json();
    if (data.success) cargarCarrito();
    else mostrarToast(data.error, 'error');
}

async function eliminarItem(carritoId) {
    const res  = await fetch('api/carrito/eliminar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ carrito_id: carritoId })
    });
    const data = await res.json();
    if (data.success) {
        mostrarToast('Producto eliminado del carrito', 'success');
        cargarCarrito();
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

cargarCarrito();
</script>

</body>
</html>