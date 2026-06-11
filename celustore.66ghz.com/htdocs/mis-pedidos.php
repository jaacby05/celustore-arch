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
    <title>Mis Pedidos — CeluStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pedido-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .pedido-header:hover { background: rgba(255,255,255,0.03); }
        .pedido-numero {
            font-weight: 700;
            color: var(--primary);
            font-size: 1rem;
            letter-spacing: 1px;
        }
        .pedido-fecha { color: var(--text-muted); font-size: 0.85rem; }
        .pedido-total { font-size: 1.1rem; font-weight: 700; }
        .pedido-body { padding: 1.2rem 1.5rem; display: none; }
        .pedido-body.open { display: block; }
        .pedido-producto {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--border);
        }
        .pedido-producto:last-child { border-bottom: none; }
        .pedido-producto img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            padding: 0.3rem;
        }
        .pedido-info-envio {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
            font-size: 0.85rem;
        }
        .pedido-info-item { display: flex; flex-direction: column; gap: 0.2rem; }
        .pedido-info-item span:first-child { color: var(--text-muted); font-size: 0.8rem; }
        .pedido-info-item span:last-child  { color: var(--text); font-weight: 600; }
        .timeline {
            display: flex;
            justify-content: space-between;
            margin: 1.5rem 0 1rem;
            position: relative;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 14px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: var(--border);
        }
        .timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            position: relative;
            z-index: 1;
        }
        .timeline-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }
        .timeline-dot.done    { background: var(--success); color: white; }
        .timeline-dot.current { background: var(--primary); color: white; box-shadow: 0 0 0 4px rgba(108,99,255,0.3); }
        .timeline-label { font-size: 0.7rem; color: var(--text-muted); text-align: center; max-width: 70px; }
        .pedidos-vacio { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
        .pedidos-vacio i { font-size: 4rem; margin-bottom: 1rem; display: block; color: var(--border); }
        .btn-factura {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            background: rgba(108,99,255,0.15);
            border: 1px solid rgba(108,99,255,0.4);
            border-radius: 8px;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-factura:hover {
            background: rgba(108,99,255,0.3);
            border-color: var(--primary);
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
        <li><a href="carrito.php"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
    </ul>
    <div class="navbar-actions">
        <span style="color:var(--text-muted); font-size:0.85rem">
            <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
        </span>
        <a href="api/auth/logout.php" class="btn btn-secondary btn-sm">Salir</a>
    </div>
</nav>

<div class="container page-content">
    <h2 style="margin-bottom:1.5rem">
        <i class="fas fa-box"></i> Mis Pedidos
    </h2>
    <div id="pedidosContenido">
        <p style="color:var(--text-muted)">Cargando pedidos...</p>
    </div>
</div>

<script>
const ESTADOS = {
    pendiente:      { label: 'Pendiente',      icon: 'fa-clock',        paso: 0 },
    en_preparacion: { label: 'En preparación', icon: 'fa-box-open',     paso: 1 },
    en_camino:      { label: 'En camino',      icon: 'fa-truck',        paso: 2 },
    entregado:      { label: 'Entregado',      icon: 'fa-check-circle', paso: 3 },
    cancelado:      { label: 'Cancelado',      icon: 'fa-times-circle', paso: -1 }
};

const PASOS_TIMELINE = [
    { key: 'pendiente',      label: 'Pendiente',  icon: 'fa-clock'    },
    { key: 'en_preparacion', label: 'Preparando', icon: 'fa-box-open' },
    { key: 'en_camino',      label: 'En camino',  icon: 'fa-truck'    },
    { key: 'entregado',      label: 'Entregado',  icon: 'fa-check'    }
];

function formatFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-AR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function buildTimeline(estadoActual) {
    if (estadoActual === 'cancelado') {
        return `<div style="text-align:center; padding:0.8rem; color:var(--danger)">
                    <i class="fas fa-times-circle"></i> Pedido cancelado
                </div>`;
    }
    const pasoActual = ESTADOS[estadoActual]?.paso ?? 0;
    return `
        <div class="timeline">
            ${PASOS_TIMELINE.map((paso, i) => {
                let clazz = '';
                if (i < pasoActual)  clazz = 'done';
                if (i === pasoActual) clazz = 'current';
                return `
                    <div class="timeline-step">
                        <div class="timeline-dot ${clazz}">
                            <i class="fas ${paso.icon}"></i>
                        </div>
                        <div class="timeline-label">${paso.label}</div>
                    </div>`;
            }).join('')}
        </div>`;
}

async function cargarPedidos() {
    const res  = await fetch('api/ordenes/listar.php');
    const data = await res.json();
    const contenido = document.getElementById('pedidosContenido');

    if (!data.ordenes || data.ordenes.length === 0) {
        contenido.innerHTML = `
            <div class="pedidos-vacio">
                <i class="fas fa-box-open"></i>
                <h3>No tenés pedidos aún</h3>
                <p style="margin:0.5rem 0 1.5rem">Explorá el catálogo y realizá tu primera compra</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-store"></i> Ir al catálogo
                </a>
            </div>`;
        return;
    }

    contenido.innerHTML = data.ordenes.map((orden, idx) => {
        const estado = ESTADOS[orden.estado] || ESTADOS['pendiente'];

        const productosHTML = orden.productos.map(p => `
            <div class="pedido-producto">
                <img src="${p.imagen || 'assets/img/no-image.png'}"
                     alt="${p.nombre}"
                     onerror="this.src='assets/img/no-image.png'">
                <div style="flex:1">
                    <div style="font-weight:600">${p.nombre}</div>
                    <div style="color:var(--text-muted); font-size:0.85rem">
                        Cantidad: ${p.cantidad} ×
                        $${parseFloat(p.precio_unitario).toLocaleString('es-AR')}
                    </div>
                </div>
                <div style="font-weight:700; color:var(--primary)">
                    $${(p.cantidad * p.precio_unitario).toLocaleString('es-AR')}
                </div>
            </div>
        `).join('');

        return `
            <div class="pedido-card">
                <div class="pedido-header" onclick="togglePedido(${idx})">
                    <div>
                        <div class="pedido-numero">
                            <i class="fas fa-hashtag"></i> ${orden.numero_orden}
                        </div>
                        <div class="pedido-fecha">
                            <i class="fas fa-calendar-alt"></i>
                            ${formatFecha(orden.fecha)}
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap">
                        <span class="badge badge-${orden.estado}">
                            <i class="fas ${estado.icon}"></i>
                            ${estado.label}
                        </span>
                        <div class="pedido-total">
                            $${parseFloat(orden.total).toLocaleString('es-AR')}
                        </div>
                        <i class="fas fa-chevron-down" id="chevron-${idx}"
                           style="color:var(--text-muted); transition:transform 0.3s"></i>
                    </div>
                </div>
                <div class="pedido-body" id="body-${idx}">
                    ${buildTimeline(orden.estado)}
                    ${productosHTML}
                    <div class="pedido-info-envio">
                        <div class="pedido-info-item">
                            <span><i class="fas fa-map-marker-alt"></i> Dirección de envío</span>
                            <span>${orden.domicilio_envio}</span>
                        </div>
                        <div class="pedido-info-item">
                            <span><i class="fas fa-wallet"></i> Método de pago</span>
                            <span>${orden.metodo_pago === 'tarjeta' ? '💳 Tarjeta' : '🏦 Transferencia'}</span>
                        </div>
                    </div>
                    <div style="margin-top:1.2rem; padding-top:1rem; border-top:1px solid var(--border); display:flex; gap:1rem; flex-wrap:wrap; align-items:center">
                        <a href="api/ordenes/factura.php?orden_id=${orden.id}"
                           class="btn-factura"
                           target="_blank">
                            <i class="fas fa-file-pdf"></i>
                            Descargar factura PDF
                        </a>
                        <span style="color:var(--text-muted); font-size:0.8rem">
                            <i class="fas fa-info-circle"></i>
                            También te enviamos la confirmación por email
                        </span>
                    </div>
                </div>
            </div>`;
    }).join('');

    if (data.ordenes.length > 0) togglePedido(0);
}

function togglePedido(idx) {
    const body    = document.getElementById(`body-${idx}`);
    const chevron = document.getElementById(`chevron-${idx}`);
    const isOpen  = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

cargarPedidos();
</script>

</body>
</html>