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
    <title>Datos de Envío — CeluStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .envio-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }
        .envio-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
        }
        .envio-card h2 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 500px) {
            .form-row { grid-template-columns: 1fr; }
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
        .step.active  { color: var(--primary); font-weight: 600; }
        .step.done    { color: var(--success); }
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
        .step-divider {
            width: 40px;
            height: 2px;
            background: var(--border);
            margin: 0 0.3rem;
        }
        .opciones-envio {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .opcion-envio {
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .opcion-envio:hover { border-color: var(--primary); }
        .opcion-envio.selected {
            border-color: var(--primary);
            background: rgba(108,99,255,0.08);
        }
        .opcion-envio .titulo {
            font-weight: 700;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .opcion-envio .precio {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
        }
        .opcion-envio .precio.gratis { color: var(--success); }
        .opcion-envio .desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
        }
        @media (max-width: 500px) {
            .opciones-envio { grid-template-columns: 1fr; }
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

<div class="envio-wrapper">

    <!-- PASOS -->
    <div class="steps">
        <div class="step done">
            <div class="step-num"><i class="fas fa-check" style="font-size:0.6rem"></i></div>
            Carrito
        </div>
        <div class="step-divider"></div>
        <div class="step active">
            <div class="step-num">2</div>
            Envío
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num">3</div>
            Pago
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num">4</div>
            Confirmación
        </div>
    </div>

    <div class="envio-card">
        <h2><i class="fas fa-map-marker-alt"></i> Datos de Envío</h2>

        <div id="alertaEnvio"></div>

        <div class="form-row">
            <div class="form-group">
                <label>Calle y número *</label>
                <input type="text" id="calle" class="form-control"
                       placeholder="Av. Corrientes 1234">
            </div>
            <div class="form-group">
                <label>Piso / Depto</label>
                <input type="text" id="piso" class="form-control"
                       placeholder="3° B">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Ciudad *</label>
                <input type="text" id="ciudad" class="form-control"
                       placeholder="Buenos Aires">
            </div>
            <div class="form-group">
                <label>Provincia *</label>
                <input type="text" id="provincia" class="form-control"
                       placeholder="Buenos Aires">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Teléfono de contacto *</label>
                <input type="tel" id="telefono" class="form-control"
                       placeholder="+54 11 1234-5678">
            </div>
            <div class="form-group">
                <label>Código postal</label>
                <input type="text" id="codigoPostal" class="form-control"
                       placeholder="1043">
            </div>
        </div>
        <div class="form-group">
            <label>Notas adicionales</label>
            <input type="text" id="notas" class="form-control"
                   placeholder="Instrucciones para el delivery, horario, etc.">
        </div>

        <!-- OPCIONES DE ENVÍO -->
        <div class="form-group">
            <label><i class="fas fa-truck"></i> Tipo de envío *</label>
            <div class="opciones-envio">
                <div class="opcion-envio selected" id="op-estandar"
                     onclick="seleccionarEnvio('estandar', 1500)">
                    <div class="titulo">
                        <i class="fas fa-truck"></i> Estándar
                    </div>
                    <div class="precio">$1.500</div>
                    <div class="desc">5 a 7 días hábiles</div>
                </div>
                <div class="opcion-envio" id="op-express"
                     onclick="seleccionarEnvio('express', 3500)">
                    <div class="titulo">
                        <i class="fas fa-bolt"></i> Express
                    </div>
                    <div class="precio">$3.500</div>
                    <div class="desc">1 a 2 días hábiles</div>
                </div>
                <div class="opcion-envio" id="op-retiro"
                     onclick="seleccionarEnvio('retiro', 0)">
                    <div class="titulo">
                        <i class="fas fa-store"></i> Retiro en local
                    </div>
                    <div class="precio gratis">Gratis</div>
                    <div class="desc">Coordinar por email</div>
                </div>
                <div class="opcion-envio" id="op-gratis"
                     onclick="seleccionarEnvio('gratis', 0)">
                    <div class="titulo">
                        <i class="fas fa-gift"></i> Envío gratis
                    </div>
                    <div class="precio gratis">Gratis</div>
                    <div class="desc">Compras mayores a $500.000</div>
                </div>
            </div>
        </div>

        <div style="display:flex; gap:1rem; justify-content:space-between; margin-top:1.5rem">
            <a href="carrito.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al carrito
            </a>
            <button class="btn btn-primary" onclick="continuar()">
                Continuar al pago <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let envioSeleccionado = { tipo: 'estandar', costo: 1500 };

// Verificar si el carrito tiene productos
async function verificarCarrito() {
    const res  = await fetch('api/carrito/listar.php');
    const data = await res.json();
    if (!data.items || data.items.length === 0) {
        window.location.href = 'carrito.php';
        return;
    }

    // Si el total supera 500000, habilitar envío gratis
    if (data.subtotal >= 500000) {
        document.getElementById('op-gratis').style.opacity = '1';
    } else {
        document.getElementById('op-gratis').style.opacity = '0.4';
        document.getElementById('op-gratis').style.pointerEvents = 'none';
        document.getElementById('op-gratis').querySelector('.desc').textContent =
            `Necesitás $${(500000 - data.subtotal).toLocaleString('es-AR')} más`;
    }
}

function seleccionarEnvio(tipo, costo) {
    envioSeleccionado = { tipo, costo };
    document.querySelectorAll('.opcion-envio').forEach(el => el.classList.remove('selected'));
    document.getElementById(`op-${tipo}`).classList.add('selected');
}

function continuar() {
    const calle    = document.getElementById('calle').value.trim();
    const ciudad   = document.getElementById('ciudad').value.trim();
    const provincia = document.getElementById('provincia').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const alerta   = document.getElementById('alertaEnvio');

    alerta.innerHTML = '';

    if (!calle || !ciudad || !provincia || !telefono) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Completá los campos obligatorios</div>';
        return;
    }

    const piso         = document.getElementById('piso').value.trim();
    const codigoPostal = document.getElementById('codigoPostal').value.trim();
    const notas        = document.getElementById('notas').value.trim();

    let domicilio = calle;
    if (piso)          domicilio += `, ${piso}`;
    domicilio += `, ${ciudad}, ${provincia}`;
    if (codigoPostal)  domicilio += ` (CP: ${codigoPostal})`;
    if (notas)         domicilio += ` — ${notas}`;

    // Guardar en sessionStorage para usar en checkout
    sessionStorage.setItem('envio_domicilio',  domicilio);
    sessionStorage.setItem('envio_telefono',   telefono);
    sessionStorage.setItem('envio_tipo',       envioSeleccionado.tipo);
    sessionStorage.setItem('envio_costo',      envioSeleccionado.costo);

    window.location.href = 'checkout.php';
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

verificarCarrito();
</script>

</body>
</html>