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
    <title>Reseñas — CeluStore Admin</title>
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
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .admin-header h1 { font-size: 1.5rem; }
        .resena-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .resena-info { flex: 1; }
        .resena-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.6rem;
        }
        .resena-autor { font-weight: 700; font-size: 0.95rem; }
        .resena-producto {
            font-size: 0.8rem;
            color: var(--primary);
            margin-top: 0.2rem;
        }
        .resena-fecha { color: var(--text-muted); font-size: 0.75rem; }
        .resena-titulo { font-weight: 600; margin-bottom: 0.4rem; }
        .resena-comentario {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .resena-caract {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.8rem;
        }
        .caract-badge {
            background: rgba(108,99,255,0.1);
            border: 1px solid rgba(108,99,255,0.2);
            border-radius: 20px;
            padding: 0.2rem 0.8rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
        .filtros {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .stats-resenas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .stat-icon.purple { background: rgba(108,99,255,0.2); color: var(--primary); }
        .stat-icon.yellow { background: rgba(255,215,0,0.2);  color: #ffd700; }
        .stat-icon.green  { background: rgba(76,175,80,0.2);  color: var(--success); }
        .stat-info h3 { font-size:1.5rem; font-weight:800; line-height:1; }
        .stat-info p  { font-size:0.8rem; color:var(--text-muted); margin-top:0.2rem; }
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
            <li><a href="pedidos.php"><i class="fas fa-shopping-bag"></i> Pedidos</a></li>
            <li><a href="usuarios.php"><i class="fas fa-users"></i> Usuarios</a></li>
            <li><a href="resenas.php" class="active"><i class="fas fa-star"></i> Reseñas</a></li>
        </ul>
        <hr class="sidebar-divider">
        <ul class="sidebar-menu">
            <li><a href="../index.php"><i class="fas fa-store"></i> Ver Tienda</a></li>
            <li><a href="../api/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <!-- CONTENIDO -->
    <main class="admin-content">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-star"></i> Reseñas</h1>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.3rem">
                    Gestioná las reseñas de los productos
                </p>
            </div>
        </div>

        <!-- STATS -->
        <div class="stats-resenas" id="statsResenas">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-spinner fa-spin"></i></div>
                <div class="stat-info"><h3>—</h3><p>Cargando...</p></div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="filtros">
            <div style="flex:1; min-width:200px">
                <input type="text" id="buscador" class="form-control"
                       placeholder="🔍 Buscar por usuario o comentario..."
                       oninput="filtrarResenas()">
            </div>
            <div>
                <select id="filtroPuntuacion" class="form-control" onchange="filtrarResenas()">
                    <option value="">Todas las puntuaciones</option>
                    <option value="5">★★★★★ 5 estrellas</option>
                    <option value="4">★★★★☆ 4 estrellas</option>
                    <option value="3">★★★☆☆ 3 estrellas</option>
                    <option value="2">★★☆☆☆ 2 estrellas</option>
                    <option value="1">★☆☆☆☆ 1 estrella</option>
                </select>
            </div>
            <div style="color:var(--text-muted); font-size:0.85rem" id="contadorFiltro"></div>
        </div>

        <!-- LISTA -->
        <div id="listaResenas">
            <p style="color:var(--text-muted)">
                <i class="fas fa-spinner fa-spin"></i> Cargando reseñas...
            </p>
        </div>

    </main>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
let todasResenas = [];

function estrellas(num) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `<span style="color:${i <= num ? '#ffd700' : 'var(--border)'}">★</span>`;
    }
    return html;
}

async function cargarTodasResenas() {
    // Primero obtenemos todos los productos para saber sus IDs
    const resProductos = await fetch('../api/admin/productos.php');
    const dataProductos = await resProductos.json();
    const productos = dataProductos.productos || [];

    // Por cada producto buscamos sus reseñas
    const promesas = productos.map(p =>
        fetch(`../api/resenas/mongodb.php?producto_id=${p.id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.resenas) {
                    return data.resenas.map(r => ({
                        ...r,
                        producto_nombre: p.nombre
                    }));
                }
                return [];
            })
            .catch(() => [])
    );

    const resultados = await Promise.all(promesas);
    todasResenas = resultados.flat().sort((a, b) => {
        const fa = a.createdAt || a.fecha || '';
        const fb = b.createdAt || b.fecha || '';
        return fb.localeCompare(fa);
    });

    mostrarStats();
    renderResenas(todasResenas);
}

function mostrarStats() {
    const total    = todasResenas.length;
    const promedio = total > 0
        ? (todasResenas.reduce((sum, r) => sum + r.puntuacion, 0) / total).toFixed(1)
        : 0;
    const cincoEstrellas = todasResenas.filter(r => r.puntuacion === 5).length;

    document.getElementById('statsResenas').innerHTML = `
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-comments"></i></div>
            <div class="stat-info"><h3>${total}</h3><p>Total reseñas</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-star"></i></div>
            <div class="stat-info"><h3>${promedio}</h3><p>Promedio general</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-thumbs-up"></i></div>
            <div class="stat-info"><h3>${cincoEstrellas}</h3><p>Reseñas 5 estrellas</p></div>
        </div>`;
}

function filtrarResenas() {
    const texto      = document.getElementById('buscador').value.toLowerCase();
    const puntuacion = document.getElementById('filtroPuntuacion').value;

    let filtradas = todasResenas.filter(r => {
        const matchTexto = !texto ||
            r.usuario_nombre?.toLowerCase().includes(texto) ||
            r.comentario?.toLowerCase().includes(texto) ||
            r.titulo?.toLowerCase().includes(texto);
        const matchPunt = !puntuacion || r.puntuacion === parseInt(puntuacion);
        return matchTexto && matchPunt;
    });

    document.getElementById('contadorFiltro').textContent =
        `${filtradas.length} reseña${filtradas.length !== 1 ? 's' : ''}`;

    renderResenas(filtradas);
}

function renderResenas(resenas) {
    const lista = document.getElementById('listaResenas');

    if (resenas.length === 0) {
        lista.innerHTML = `
            <div style="text-align:center; padding:3rem; color:var(--text-muted)">
                <i class="fas fa-comment-slash"
                   style="font-size:3rem; margin-bottom:1rem; display:block"></i>
                No hay reseñas para mostrar
            </div>`;
        return;
    }

    const nombresAspecto = {
        bateria:        '🔋 Batería',
        camara:         '📷 Cámara',
        rendimiento:    '⚡ Rendimiento',
        precio_calidad: '💰 Precio/Calidad'
    };

    lista.innerHTML = resenas.map(r => {
        const caract     = r.caracteristicas || {};
        const caractHTML = Object.keys(caract).length > 0
            ? `<div class="resena-caract">
                ${Object.entries(caract).map(([k, v]) => v ? `
                    <span class="caract-badge">
                        ${nombresAspecto[k] || k}: ${estrellas(v)}
                    </span>
                ` : '').join('')}
               </div>`
            : '';

        const resenaId = r._id?.$oid || r._id || '';
        const fecha    = r.createdAt
            ? new Date(r.createdAt).toLocaleDateString('es-AR')
            : (r.fecha ? r.fecha.substring(0, 10) : '');

        return `
            <div class="resena-card" id="resena-${resenaId}">
                <div class="resena-info">
                    <div class="resena-header">
                        <div>
                            <div class="resena-autor">
                                <i class="fas fa-user-circle"></i>
                                ${r.usuario_nombre}
                            </div>
                            <div class="resena-producto">
                                <i class="fas fa-mobile-alt"></i>
                                ${r.producto_nombre || 'Producto #' + r.producto_id}
                            </div>
                            <div class="resena-fecha">${fecha}</div>
                        </div>
                        <div>${estrellas(r.puntuacion)}</div>
                    </div>
                    ${r.titulo
                        ? `<div class="resena-titulo">${r.titulo}</div>`
                        : ''}
                    <div class="resena-comentario">${r.comentario}</div>
                    ${caractHTML}
                    <div style="margin-top:0.8rem; color:var(--text-muted); font-size:0.8rem">
                        <i class="fas fa-thumbs-up"></i> Útil: ${r.util || 0}
                    </div>
                </div>
                <div style="flex-shrink:0">
                    <button class="btn btn-danger btn-sm"
                            onclick="eliminarResena('${resenaId}')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>`;
    }).join('');

    document.getElementById('contadorFiltro').textContent =
        `${resenas.length} reseña${resenas.length !== 1 ? 's' : ''}`;
}

async function eliminarResena(id) {
    if (!id) return;
    if (!confirm('¿Estás seguro de que querés eliminar esta reseña?')) return;

    const res  = await fetch('../api/resenas/mongodb.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });
    const data = await res.json();

    if (data.success) {
        mostrarToast('Reseña eliminada correctamente', 'success');
        todasResenas = todasResenas.filter(r => {
            const rid = r._id?.$oid || r._id || '';
            return rid !== id;
        });
        mostrarStats();
        filtrarResenas();
    } else {
        mostrarToast(data.error || 'Error al eliminar', 'error');
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

cargarTodasResenas();
</script>

</body>
</html>