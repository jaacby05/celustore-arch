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
    <title>Finalizar Compra — CeluStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .checkout-layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            align-items: start;
        }
        @media (max-width: 768px) {
            .checkout-layout { grid-template-columns: 1fr; }
        }
        .checkout-section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .checkout-section h3 {
            font-size: 1rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .resumen-envio {
            background: rgba(108,99,255,0.08);
            border: 1px solid rgba(108,99,255,0.2);
            border-radius: var(--radius);
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .resumen-envio-titulo {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .resumen-envio-titulo a {
            font-size: 0.8rem;
            font-weight: 400;
        }
        .metodo-pago-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .metodo-card {
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .metodo-card:hover  { border-color: var(--primary); }
        .metodo-card.selected {
            border-color: var(--primary);
            background: rgba(108,99,255,0.1);
        }
        .metodo-card i    { font-size:2rem; margin-bottom:0.5rem; display:block; color:var(--primary); }
        .metodo-card span { font-size:0.9rem; font-weight:600; }
        .resumen-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }
        .resumen-item:last-child { border-bottom: none; }
        .resumen-item-nombre {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .resumen-item-nombre img {
            width: 45px;
            height: 45px;
            object-fit: contain;
            background: rgba(255,255,255,0.03);
            border-radius: 6px;
            padding: 0.2rem;
        }
        .resumen-total-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            position: sticky;
            top: 80px;
        }
        .resumen-total-card h3 {
            font-size: 1rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
        }
        .total-fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .total-envio {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            color: var(--text);
        }
        .total-final {
            display: flex;
            justify-content: space-between;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            padding-top: 0.8rem;
            border-top: 2px solid var(--border);
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .success-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            z-index: 3000;
            align-items: center;
            justify-content: center;
        }
        .success-overlay.active { display: flex; }
        .success-card {
            background: var(--dark2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 3rem 2rem;
            text-align: center;
            max-width: 420px;
            width: 90%;
        }
        .success-icon { font-size:4rem; color:var(--success); margin-bottom:1rem; }
        .numero-orden {
            background: rgba(108,99,255,0.15);
            border: 1px solid var(--primary);
            border-radius: 8px;
            padding: 0.8rem 1.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 2px;
            margin: 1rem 0 1.5rem;
            display: inline-block;
        }
        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .step.active { color: var(--primary); font-weight: 600; }
        .step.done   { color: var(--success); }
        .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .step.active .step-num { background: var(--primary); color: white; }
        .step.done   .step-num { background: var(--success);  color: white; }
        .step-divider { width:40px; height:2px; background:var(--border); margin:0 0.3rem; }
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

    <div class="steps">
        <div class="step done">
            <div class="step-num"><i class="fas fa-check" style="font-size:0.6rem"></i></div>
            Carrito
        </div>
        <div class="step-divider"></div>
        <div class="step done">
            <div class="step-num"><i class="fas fa-check" style="font-size:0.6rem"></i></div>
            Envío
        </div>
        <div class="step-divider"></div>
        <div class="step active">
            <div class="step-num">3</div>
            Pago
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num">4</div>
            Confirmación
        </div>
    </div>

    <h2 style="margin-bottom:1.5rem">
        <i class="fas fa-credit-card"></i> Método de Pago
    </h2>

    <div class="checkout-layout">
        <div>

            <!-- RESUMEN DATOS DE ENVÍO -->
            <div class="resumen-envio">
                <div class="resumen-envio-titulo">
                    <span><i class="fas fa-map-marker-alt"></i> Datos de envío</span>
                    <a href="envio.php"><i class="fas fa-edit"></i> Modificar</a>
                </div>
                <div id="resumenDireccion" style="color:var(--text-muted)">
                    Cargando...
                </div>
            </div>

            <!-- MÉTODO DE PAGO -->
            <div class="checkout-section">
                <h3><i class="fas fa-wallet"></i> Elegí tu método de pago</h3>
                <div class="metodo-pago-grid">
                    <div class="metodo-card selected" id="card-tarjeta"
                         onclick="seleccionarPago('tarjeta')">
                        <i class="fas fa-credit-card"></i>
                        <span>Tarjeta de crédito/débito</span>
                    </div>
                    <div class="metodo-card" id="card-transferencia"
                         onclick="seleccionarPago('transferencia')">
                        <i class="fas fa-university"></i>
                        <span>Transferencia bancaria</span>
                    </div>
                </div>

                <div id="infoTarjeta" style="margin-top:1.2rem">
                    <div class="form-group">
                        <label>Número de tarjeta</label>
                        <input type="text" class="form-control"
                               placeholder="1234 5678 9012 3456"
                               maxlength="19" oninput="formatCard(this)">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem">
                        <div class="form-group">
                            <label>Vencimiento</label>
                            <input type="text" class="form-control"
                                   placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" class="form-control"
                                   placeholder="123" maxlength="3">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nombre en la tarjeta</label>
                        <input type="text" class="form-control"
                               placeholder="Como figura en la tarjeta">
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Sistema de demostración. No ingreses datos reales de tarjeta.
                    </div>
                </div>

                <div id="infoTransferencia" style="display:none; margin-top:1.2rem">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Realizá la transferencia a:<br><br>
                        <strong>CBU:</strong> 0000 0000 0000 0000 0000<br>
                        <strong>Alias:</strong> CELUSTORE.PAGOS<br>
                        <strong>Titular:</strong> CeluStore S.A.<br><br>
                        Una vez confirmado el pago procesaremos tu pedido.
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMEN DERECHA -->
        <div class="resumen-total-card">
            <h3><i class="fas fa-receipt"></i> Resumen del Pedido</h3>
            <div id="resumenItems">
                <p style="color:var(--text-muted); font-size:0.9rem">Cargando...</p>
            </div>
            <div id="resumenTotales"></div>
            <button class="btn btn-success btn-block" id="btnConfirmar"
                    onclick="confirmarCompra()">
                <i class="fas fa-check-circle"></i> Confirmar Compra
            </button>
            <a href="envio.php" class="btn btn-secondary btn-block"
               style="margin-top:0.8rem">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<!-- OVERLAY ÉXITO -->
<div class="success-overlay" id="successOverlay">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="margin-bottom:0.5rem">¡Compra exitosa!</h2>
        <p style="color:var(--text-muted); margin-bottom:0.5rem">Tu número de orden es:</p>
        <div class="numero-orden" id="numeroOrdenDisplay"></div>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:1.5rem">
            Guardá este número para hacer seguimiento de tu pedido.
        </p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap">
            <a href="mis-pedidos.php" class="btn btn-primary">
                <i class="fas fa-box"></i> Ver mis pedidos
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-store"></i> Seguir comprando
            </a>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let metodoPago = 'tarjeta';

// Recuperar datos de envío guardados
const envioDomicilio = sessionStorage.getItem('envio_domicilio');
const envioTelefono  = sessionStorage.getItem('envio_telefono');
const envioTipo      = sessionStorage.getItem('envio_tipo');
const envioCosto     = parseFloat(sessionStorage.getItem('envio_costo') || 0);

// Si no hay datos de envío redirigir
if (!envioDomicilio || !envioTelefono) {
    window.location.href = 'envio.php';
}

const tiposEnvio = {
    estandar: 'Envío Estándar (5-7 días)',
    express:  'Envío Express (1-2 días)',
    retiro:   'Retiro en local',
    gratis:   'Envío gratis'
};

// Mostrar resumen de dirección
document.getElementById('resumenDireccion').innerHTML = `
    <div><i class="fas fa-home"></i> ${envioDomicilio}</div>
    <div style="margin-top:0.3rem">
        <i class="fas fa-phone"></i> ${envioTelefono}
    </div>
    <div style="margin-top:0.3rem; color:var(--primary)">
        <i class="fas fa-truck"></i> ${tiposEnvio[envioTipo] || envioTipo}
        ${envioCosto > 0
            ? `— $${envioCosto.toLocaleString('es-AR')}`
            : '— <span style="color:var(--success)">Gratis</span>'}
    </div>`;

async function cargarResumen() {
    const res  = await fetch('api/carrito/listar.php');
    const data = await res.json();

    if (!data.items || data.items.length === 0) {
        window.location.href = 'carrito.php';
        return;
    }

    document.getElementById('resumenItems').innerHTML = data.items.map(item => `
        <div class="resumen-item">
            <div class="resumen-item-nombre">
                <img src="${item.imagen || 'assets/img/no-image.png'}"
                     alt="${item.nombre}"
                     onerror="this.src='assets/img/no-image.png'">
                <div>
                    <div style="font-weight:600">${item.nombre}</div>
                    <div style="color:var(--text-muted); font-size:0.8rem">x${item.cantidad}</div>
                </div>
            </div>
            <div style="font-weight:600; color:var(--primary)">
                $${parseFloat(item.subtotal).toLocaleString('es-AR')}
            </div>
        </div>
    `).join('');

    const subtotal = parseFloat(data.subtotal);
    const total    = subtotal + envioCosto;

    document.getElementById('resumenTotales').innerHTML = `
        <div style="padding-top:1rem; margin-top:0.5rem; border-top:1px solid var(--border)">
            <div class="total-fila">
                <span>Subtotal</span>
                <span>$${subtotal.toLocaleString('es-AR')}</span>
            </div>
            <div class="total-envio">
                <span>
                    <i class="fas fa-truck" style="color:var(--primary)"></i>
                    ${tiposEnvio[envioTipo] || 'Envío'}
                </span>
                <span style="color:${envioCosto > 0 ? 'var(--text)' : 'var(--success)'}">
                    ${envioCosto > 0 ? '$' + envioCosto.toLocaleString('es-AR') : 'Gratis'}
                </span>
            </div>
            <div class="total-final">
                <span>Total</span>
                <span>$${total.toLocaleString('es-AR')}</span>
            </div>
        </div>`;
}

function seleccionarPago(metodo) {
    metodoPago = metodo;
    document.getElementById('card-tarjeta').classList.toggle('selected',      metodo === 'tarjeta');
    document.getElementById('card-transferencia').classList.toggle('selected', metodo === 'transferencia');
    document.getElementById('infoTarjeta').style.display       = metodo === 'tarjeta'       ? 'block' : 'none';
    document.getElementById('infoTransferencia').style.display = metodo === 'transferencia' ? 'block' : 'none';
}

async function confirmarCompra() {
    const btn = document.getElementById('btnConfirmar');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    const res  = await fetch('api/ordenes/crear.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            domicilio_envio:   envioDomicilio,
            telefono_contacto: envioTelefono,
            tipo_envio:        envioTipo,
            costo_envio:       envioCosto,
            metodo_pago:       metodoPago
        })
    });
    const data = await res.json();

    if (data.success) {
        sessionStorage.removeItem('envio_domicilio');
        sessionStorage.removeItem('envio_telefono');
        sessionStorage.removeItem('envio_tipo');
        sessionStorage.removeItem('envio_costo');
        document.getElementById('numeroOrdenDisplay').textContent = data.numero_orden;
        document.getElementById('successOverlay').classList.add('active');
    } else {
        mostrarToast(data.error, 'error');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Compra';
    }
}

function formatCard(input) {
    let val     = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = val.replace(/(.{4})/g, '$1 ').trim();
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

cargarResumen();
</script>

</body>
</html>