<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CeluStore — Bienvenido</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark2) 50%, #0a1628 100%);
            min-height: 100vh;
        }
        .landing-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .landing-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            background: linear-gradient(135deg, rgba(108,99,255,0.15) 0%, rgba(108,99,255,0.05) 100%);
            border-right: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .landing-left::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(108,99,255,0.15) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            border-radius: 50%;
        }
        .landing-left::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,101,132,0.1) 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
            border-radius: 50%;
        }
        .landing-logo {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        .landing-logo span { color: var(--text); }
        .landing-tagline {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1rem;
            line-height: 1.3;
            position: relative;
            z-index: 1;
        }
        .landing-tagline span { color: var(--primary); }
        .landing-desc {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2.5rem;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }
        .features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }
        .features-list li {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(108,99,255,0.2);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .phones-decoration {
            position: absolute;
            right: -20px;
            bottom: 2rem;
            font-size: 8rem;
            opacity: 0.06;
            z-index: 0;
        }
        .landing-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .auth-tabs {
            display: flex;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 2rem;
            gap: 4px;
        }
        .auth-tab {
            flex: 1;
            padding: 0.6rem;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .auth-tab.active {
            background: var(--primary);
            color: white;
        }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }
        .auth-panel h2 { font-size: 1.3rem; margin-bottom: 0.3rem; }
        .auth-panel p  { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem; }
        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.4rem;
            transition: all 0.3s;
            background: transparent;
        }
        .strength-text { font-size: 0.75rem; margin-top: 0.2rem; min-height: 1rem; }
        .codigo-inputs {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1.5rem 0;
        }
        .codigo-input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            color: var(--text);
            transition: border-color 0.2s;
        }
        .codigo-input:focus {
            outline: none;
            border-color: var(--primary);
        }
        @media (max-width: 768px) {
            .landing-wrapper { grid-template-columns: 1fr; }
            .landing-left {
                padding: 2.5rem 1.5rem;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }
            .landing-tagline { font-size: 1.3rem; }
            .phones-decoration { display: none; }
        }
    </style>
</head>
<body>

<div class="landing-wrapper">

    <!-- LADO IZQUIERDO -->
    <div class="landing-left">
        <div class="landing-logo">
            <i class="fas fa-mobile-alt"></i> Celu<span>Store</span>
        </div>
        <h1 class="landing-tagline">
            Los mejores celulares,<br>
            <span>al mejor precio</span>
        </h1>
        <p class="landing-desc">
            Explorá nuestra amplia variedad de smartphones y accesorios.
            Comprá de forma rápida, segura y con seguimiento de tu pedido en tiempo real.
        </p>
        <ul class="features-list">
            <li>
                <div class="feature-icon"><i class="fas fa-tags"></i></div>
                <span>Precios competitivos en todas las marcas</span>
            </li>
            <li>
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <span>Compras seguras con confirmación por email</span>
            </li>
            <li>
                <div class="feature-icon"><i class="fas fa-truck"></i></div>
                <span>Seguimiento de tu pedido en tiempo real</span>
            </li>
            <li>
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <span>Atención al cliente todos los días</span>
            </li>
        </ul>
        <div class="phones-decoration">
            <i class="fas fa-mobile-alt"></i>
        </div>
    </div>

    <!-- LADO DERECHO -->
    <div class="landing-right">
        <div class="auth-card">

            <div class="auth-tabs" id="authTabs">
                <button class="auth-tab active" id="tabLogin"
                        onclick="cambiarTab('login')">
                    Iniciar Sesión
                </button>
                <button class="auth-tab" id="tabRegister"
                        onclick="cambiarTab('register')">
                    Registrarse
                </button>
            </div>

            <!-- PANEL LOGIN -->
            <div class="auth-panel active" id="panelLogin">
                <h2>Bienvenido de vuelta</h2>
                <p>Ingresá tus datos para acceder al catálogo</p>

                <div id="alertaLogin"></div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="loginEmail" class="form-control"
                           placeholder="tu@email.com" autofocus>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="loginPassword" class="form-control"
                               placeholder="Tu contraseña">
                        <button class="password-toggle"
                                onclick="togglePassword('loginPassword', 'eyeLogin')">
                            <i class="fas fa-eye" id="eyeLogin"></i>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary btn-block" id="btnLogin">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </div>

            <!-- PANEL REGISTRO -->
            <div class="auth-panel" id="panelRegister">
                <h2>Creá tu cuenta</h2>
                <p>Es gratis y solo toma un minuto</p>

                <div id="alertaRegister"></div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre completo</label>
                    <input type="text" id="regNombre" class="form-control"
                           placeholder="Juan Pérez">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="regEmail" class="form-control"
                           placeholder="tu@email.com">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="regPassword" class="form-control"
                               placeholder="Mínimo 6 caracteres"
                               oninput="checkStrength(this.value)">
                        <button class="password-toggle"
                                onclick="togglePassword('regPassword', 'eyeReg')">
                            <i class="fas fa-eye" id="eyeReg"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="strengthBar"></div>
                    <div class="strength-text"    id="strengthText"></div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmar contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="regPassword2" class="form-control"
                               placeholder="Repetí tu contraseña">
                        <button class="password-toggle"
                                onclick="togglePassword('regPassword2', 'eyeReg2')">
                            <i class="fas fa-eye" id="eyeReg2"></i>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary btn-block" id="btnRegister">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>
            </div>

            <!-- PANEL VERIFICACIÓN -->
            <div class="auth-panel" id="panelVerificacion">
                <h2><i class="fas fa-envelope-open-text"></i> Verificá tu email</h2>
                <p id="textoVerificacion">Te enviamos un código de 6 dígitos. Revisá tu bandeja de entrada.</p>

                <div id="alertaVerificacion"></div>

                <div class="codigo-inputs">
                    <input type="text" class="codigo-input" maxlength="1" id="c1">
                    <input type="text" class="codigo-input" maxlength="1" id="c2">
                    <input type="text" class="codigo-input" maxlength="1" id="c3">
                    <input type="text" class="codigo-input" maxlength="1" id="c4">
                    <input type="text" class="codigo-input" maxlength="1" id="c5">
                    <input type="text" class="codigo-input" maxlength="1" id="c6">
                </div>

                <button class="btn btn-primary btn-block" id="btnVerificar">
                    <i class="fas fa-check-circle"></i> Verificar cuenta
                </button>

                <div style="text-align:center; margin-top:1rem">
                    <button onclick="reenviarCodigo()"
                            style="background:none; border:none; color:var(--primary);
                                   cursor:pointer; font-size:0.85rem; text-decoration:underline">
                        ¿No recibiste el código? Reenviar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let emailVerificacion = '';

// ── TABS ─────────────────────────────────────────────────────
function cambiarTab(tab) {
    document.getElementById('panelLogin').classList.toggle('active',    tab === 'login');
    document.getElementById('panelRegister').classList.toggle('active', tab === 'register');
    document.getElementById('panelVerificacion').classList.remove('active');
    document.getElementById('tabLogin').classList.toggle('active',      tab === 'login');
    document.getElementById('tabRegister').classList.toggle('active',   tab === 'register');
    document.getElementById('authTabs').style.display = 'flex';
    document.getElementById('alertaLogin').innerHTML    = '';
    document.getElementById('alertaRegister').innerHTML = '';
}

function mostrarPanelVerificacion(email, mensaje) {
    emailVerificacion = email;
    document.getElementById('panelLogin').classList.remove('active');
    document.getElementById('panelRegister').classList.remove('active');
    document.getElementById('panelVerificacion').classList.add('active');
    document.getElementById('authTabs').style.display = 'none';
    document.getElementById('textoVerificacion').textContent =
        mensaje || 'Te enviamos un código de 6 dígitos. Revisá tu bandeja de entrada.';
    document.getElementById('c1').focus();
}

// ── INPUTS DE CÓDIGO ──────────────────────────────────────────
document.querySelectorAll('.codigo-input').forEach((input, index, inputs) => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && index > 0) {
            inputs[index - 1].focus();
        }
        if (e.key === 'Enter') verificarCodigo();
    });
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const texto = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
        texto.split('').forEach((char, i) => {
            if (inputs[i]) inputs[i].value = char;
        });
        if (inputs[Math.min(texto.length, 5)]) {
            inputs[Math.min(texto.length, 5)].focus();
        }
    });
});

// ── LOGIN ─────────────────────────────────────────────────────
document.getElementById('btnLogin').addEventListener('click', login);

async function login() {
    const email    = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();
    const btn      = document.getElementById('btnLogin');
    const alerta   = document.getElementById('alertaLogin');

    alerta.innerHTML = '';

    if (!email || !password) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Completá todos los campos</div>';
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando...';

    const res  = await fetch('api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    });
    const data = await res.json();

    if (data.success) {
        alerta.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Bienvenido/a! Redirigiendo...</div>';
        setTimeout(() => {
            window.location.href = data.usuario.rol === 'admin'
                ? 'admin/index.php'
                : 'index.php';
        }, 800);
    } else {
        alerta.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ${data.error}</div>`;
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
    }
}

// ── REGISTRO ──────────────────────────────────────────────────
document.getElementById('btnRegister').addEventListener('click', registrar);

async function registrar() {
    const nombre    = document.getElementById('regNombre').value.trim();
    const email     = document.getElementById('regEmail').value.trim();
    const password  = document.getElementById('regPassword').value;
    const password2 = document.getElementById('regPassword2').value;
    const btn       = document.getElementById('btnRegister');
    const alerta    = document.getElementById('alertaRegister');

    alerta.innerHTML = '';

    if (!nombre || !email || !password || !password2) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Completá todos los campos</div>';
        return;
    }
    if (password !== password2) {
        alerta.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Las contraseñas no coinciden</div>';
        return;
    }
    if (password.length < 6) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> La contraseña debe tener al menos 6 caracteres</div>';
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    const res  = await fetch('api/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, email, password })
    });
    const data = await res.json();

    btn.disabled  = false;
    btn.innerHTML = '<i class="fas fa-user-plus"></i> Crear Cuenta';

    if (data.success && data.verificacion) {
        mostrarPanelVerificacion(email, data.mensaje);
    } else if (data.error) {
        alerta.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ${data.error}</div>`;
    }
}

// ── VERIFICACIÓN ──────────────────────────────────────────────
document.getElementById('btnVerificar').addEventListener('click', verificarCodigo);

async function verificarCodigo() {
    const codigo = ['c1','c2','c3','c4','c5','c6']
        .map(id => document.getElementById(id).value)
        .join('');

    const btn    = document.getElementById('btnVerificar');
    const alerta = document.getElementById('alertaVerificacion');

    alerta.innerHTML = '';

    if (codigo.length !== 6) {
        alerta.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Ingresá el código completo de 6 dígitos</div>';
        return;
    }

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

    const res  = await fetch('api/auth/verificar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: emailVerificacion, codigo })
    });
    const data = await res.json();

    if (data.success) {
        alerta.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ¡Cuenta verificada! Redirigiendo...</div>';
        setTimeout(() => {
            window.location.href = data.usuario.rol === 'admin'
                ? 'admin/index.php'
                : 'index.php';
        }, 1000);
    } else {
        alerta.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ${data.error}</div>`;
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Verificar cuenta';
    }
}

// ── REENVIAR CÓDIGO ───────────────────────────────────────────
async function reenviarCodigo() {
    if (!emailVerificacion) return;
    mostrarToast('Reenviando código...', 'success');

    const nombre = document.getElementById('regNombre').value.trim() || 'Usuario';
    const password = document.getElementById('regPassword').value || '000000';

    await fetch('api/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre, email: emailVerificacion, password })
    });
    mostrarToast('Código reenviado. Revisá tu email.', 'success');
}

// ── UTILIDADES ────────────────────────────────────────────────
function togglePassword(inputId, iconId) {
    const input    = document.getElementById(inputId);
    const icon     = document.getElementById(iconId);
    input.type     = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password'  ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function checkStrength(pass) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    if (!pass) { bar.style.width = '0'; text.textContent = ''; return; }
    let score = 0;
    if (pass.length >= 6)           score++;
    if (pass.length >= 10)          score++;
    if (/[A-Z]/.test(pass))         score++;
    if (/[0-9]/.test(pass))         score++;
    if (/[^A-Za-z0-9]/.test(pass)) score++;
    const niveles = [
        { label:'Muy débil',  color:'#f44336', width:'20%'  },
        { label:'Débil',      color:'#ff9800', width:'40%'  },
        { label:'Regular',    color:'#ffeb3b', width:'60%'  },
        { label:'Fuerte',     color:'#8bc34a', width:'80%'  },
        { label:'Muy fuerte', color:'#4caf50', width:'100%' },
    ];
    const nivel          = niveles[Math.min(score - 1, 4)] || niveles[0];
    bar.style.width      = nivel.width;
    bar.style.background = nivel.color;
    text.textContent     = nivel.label;
    text.style.color     = nivel.color;
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

document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const loginActivo = document.getElementById('panelLogin').classList.contains('active');
        if (loginActivo) login();
        else if (document.getElementById('panelRegister').classList.contains('active')) registrar();
    }
});
</script>

</body>
</html>