<?php
require_once __DIR__ . '/reutilizable/session_admin.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

$titulo_pagina = 'Panel Inicio';
$pdo = conectar_db();

// ─────────────────────────────────────────
// FILTROS
// ─────────────────────────────────────────
$filtro_estado = $_GET['estado'] ?? 'todos';
$filtro_orden  = $_GET['orden']  ?? 'nombre';
$busqueda      = trim($_GET['buscar'] ?? '');

$estados_validos = ['todos', 'activo', 'suspendido', 'cancelado'];
$ordenes_validos = ['nombre', 'suscripcion_vence', 'clientes_desc', 'proximo_vencer'];

if (!in_array($filtro_estado, $estados_validos)) $filtro_estado = 'todos';
if (!in_array($filtro_orden,  $ordenes_validos))  $filtro_orden  = 'nombre';

// ─────────────────────────────────────────
// CONTADORES para tarjetas del dashboard
// ─────────────────────────────────────────
$contadores = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(estado = 'activo')      AS activos,
        SUM(estado = 'suspendido')  AS suspendidos,
        SUM(estado = 'cancelado')   AS cancelados,
        SUM(suscripcion_vence IS NOT NULL AND suscripcion_vence BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS por_vencer
    FROM gyms
")->fetch();

// ─────────────────────────────────────────
// QUERY PRINCIPAL de gyms con filtros
// ─────────────────────────────────────────
$where  = [];
$params = [];

if ($filtro_estado !== 'todos') {
    $where[]  = 'g.estado = ?';
    $params[] = $filtro_estado;
}

if ($busqueda !== '') {
    $where[]  = '(g.nombre LIKE ? OR g.email LIKE ? OR g.telefono LIKE ?)';
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$order_sql = match($filtro_orden) {
    'suscripcion_vence' => 'ORDER BY g.suscripcion_vence DESC',
    'clientes_desc'     => 'ORDER BY total_clientes DESC',
    'proximo_vencer'    => 'ORDER BY g.suscripcion_vence ASC',
    default             => 'ORDER BY g.nombre ASC',
};

$stmt = $pdo->prepare("
    SELECT
        g.id,
        g.nombre,
        g.email,
        g.telefono,
        g.estado,
        g.suscripcion_vence,
        g.ultimo_acceso,
        g.fecha_creacion,
        COUNT(c.id) AS total_clientes
    FROM gyms g
    LEFT JOIN clientes c ON c.gym_id = g.id
    $where_sql
    GROUP BY g.id
    $order_sql
");
$stmt->execute($params);
$gyms = $stmt->fetchAll();

// ─────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────
function badgeEstado(string $estado): string {
    return match($estado) {
        'activo'     => '<span class="badge bg-success">Activo</span>',
        'suspendido' => '<span class="badge bg-warning text-dark">Suspendido</span>',
        'cancelado'  => '<span class="badge bg-danger">Cancelado</span>',
        default      => '<span class="badge bg-secondary">-</span>',
    };
}

function alertaVencimiento(?string $fecha): string {
    if (!$fecha) return '<span class="text-muted">—</span>';
    $hoy  = new DateTime();
    $venc = new DateTime($fecha);
    $dias = (int)$hoy->diff($venc)->format('%r%a');
    $formato = date('d/m/Y', strtotime($fecha));
    if ($dias < 0)  return "<span class='text-danger fw-bold'>$formato (vencido)</span>";
    if ($dias <= 7) return "<span class='text-warning fw-bold'>$formato ($dias días)</span>";
    return "<span class='text-success'>$formato</span>";
}
?>
<?php require_once __DIR__ . '/reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/reutilizable/menu.php'; ?>

<div class="container-fluid py-4 px-4 ga-admin-main">

    <section class="ga-admin-hero">
        <p class="ga-admin-kicker">Panel administrativo</p>
        <h1 class="ga-admin-title">Gestión central de gimnasios</h1>
        <p class="ga-admin-subtitle">Supervisa estado, suscripciones y accesos desde un solo lugar.</p>
    </section>

    <!-- ── TARJETAS RESUMEN ── -->
    <div class="row g-3 mb-4 ga-admin-stats">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-light ga-admin-stat">
                <div class="card-body">
                    <span class="ga-admin-stat-icon ga-admin-stat-icon--total" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M2 2.5A.5.5 0 0 1 2.5 2h11a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-11A.5.5 0 0 1 2 4.5v-2zm0 5A.5.5 0 0 1 2.5 7h11a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-11A.5.5 0 0 1 2 9.5v-2zm0 5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-2z"/></svg>
                    </span>
                    <div class="fs-2 fw-bold"><?= $contadores['total'] ?></div>
                    <div class="text-muted small">Total gyms</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-success bg-opacity-10 ga-admin-stat">
                <div class="card-body">
                    <span class="ga-admin-stat-icon ga-admin-stat-icon--active" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M2.5 8a5.5 5.5 0 1 1 11 0 5.5 5.5 0 0 1-11 0zm4.354-1.646a.5.5 0 0 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3a.5.5 0 1 0-.708-.708L7.5 7.793l-1.646-1.647z"/></svg>
                    </span>
                    <div class="fs-2 fw-bold text-success"><?= $contadores['activos'] ?></div>
                    <div class="text-muted small">Activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-warning bg-opacity-10 ga-admin-stat">
                <div class="card-body">
                    <span class="ga-admin-stat-icon ga-admin-stat-icon--pause" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M5.5 3.5A.5.5 0 0 1 6 3h1a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5H6a.5.5 0 0 1-.5-.5v-9zm4 0A.5.5 0 0 1 10 3h1a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-9z"/></svg>
                    </span>
                    <div class="fs-2 fw-bold text-warning"><?= $contadores['suspendidos'] ?></div>
                    <div class="text-muted small">Suspendidos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 bg-danger bg-opacity-10 ga-admin-stat">
                <div class="card-body">
                    <span class="ga-admin-stat-icon ga-admin-stat-icon--alert" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M7.938 2.016a.13.13 0 0 1 .125 0l6.857 3.943c.05.029.08.082.08.14v7.884a.16.16 0 0 1-.08.14l-6.857 3.943a.13.13 0 0 1-.125 0L1.08 14.123A.16.16 0 0 1 1 13.983V6.1c0-.058.03-.111.08-.14l6.857-3.943zM8 5.5a.5.5 0 0 0-.5.5v3.5a.5.5 0 0 0 1 0V6a.5.5 0 0 0-.5-.5zm0 6a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5z"/></svg>
                    </span>
                    <div class="fs-2 fw-bold text-danger"><?= $contadores['por_vencer'] ?></div>
                    <div class="text-muted small">Vencen en 7 días</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── BUSCADOR Y FILTROS ── -->
    <div class="card mb-4 border-0 shadow-sm ga-admin-filters">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Buscar gym</label>
                    <input
                        type="text"
                        name="buscar"
                        class="form-control"
                        placeholder="Nombre, email o teléfono..."
                        value="<?= htmlspecialchars($busqueda) ?>"
                    >
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos"      <?= $filtro_estado === 'todos'      ? 'selected' : '' ?>>Todos</option>
                        <option value="activo"     <?= $filtro_estado === 'activo'     ? 'selected' : '' ?>>Activo</option>
                        <option value="suspendido" <?= $filtro_estado === 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                        <option value="cancelado"  <?= $filtro_estado === 'cancelado'  ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted">Ordenar por</label>
                    <select name="orden" class="form-select">
                        <option value="nombre"           <?= $filtro_orden === 'nombre'           ? 'selected' : '' ?>>Nombre A-Z</option>
                        <option value="suscripcion_vence"<?= $filtro_orden === 'suscripcion_vence' ? 'selected' : '' ?>>Mayor vencimiento</option>
                        <option value="clientes_desc"    <?= $filtro_orden === 'clientes_desc'    ? 'selected' : '' ?>>Más clientes</option>
                        <option value="proximo_vencer"   <?= $filtro_orden === 'proximo_vencer'   ? 'selected' : '' ?>>Próximos a vencer</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    <a href="panel_inicio.php" class="btn btn-outline-secondary w-100">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- ── TABLA DE GYMS ── -->
    <div class="card border-0 shadow-sm ga-admin-table-card">
        <div class="card-body p-0">
            <?php if (empty($gyms)): ?>
                <div class="text-center text-muted py-5">No se encontraron gyms.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Gym</th>
                                <th>Contacto</th>
                                <th class="text-center">Clientes</th>
                                <th>Vence suscripción</th>
                                <th>Último acceso</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gyms as $gym): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($gym['nombre']) ?></div>
                                    <div class="text-muted small">desde <?= date('d/m/Y', strtotime($gym['fecha_creacion'])) ?></div>
                                </td>
                                <td>
                                    <div class="small"><?= htmlspecialchars($gym['email'] ?? '—') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($gym['telefono'] ?? '—') ?></div>
                                </td>
                                <td class="text-center fw-bold"><?= $gym['total_clientes'] ?></td>
                                <td><?= alertaVencimiento($gym['suscripcion_vence']) ?></td>
                                <td class="small text-muted">
                                    <?= $gym['ultimo_acceso']
                                        ? date('d/m/Y H:i', strtotime($gym['ultimo_acceso']))
                                        : '—' ?>
                                </td>
                                <td class="text-center"><?= badgeEstado($gym['estado']) ?></td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="ver_gym.php?id=<?= $gym['id'] ?>" class="btn btn-sm btn-outline-primary ga-admin-btn-icon" title="Ver detalle">
                                            <span class="ga-admin-icon" aria-hidden="true">
                                                <svg viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z"/><path d="M8 5a3 3 0 1 1 0 6A3 3 0 0 1 8 5z"/></svg>
                                            </span>
                                            <span class="visually-hidden">Ver detalle</span>
                                        </a>
                                        <form method="POST" action="backend/entrar_gym.php" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $gym['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary ga-admin-btn-icon" title="Entrar al gym">
                                                <span class="ga-admin-icon" aria-hidden="true">
                                                    <svg viewBox="0 0 16 16"><path d="M6 2a1 1 0 0 0-1 1v3h1V3h8v10H6v-3H5v3a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H6z"/><path d="M.146 8.354a.5.5 0 0 1 0-.708l2.5-2.5a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l1.647 1.646a.5.5 0 0 1-.708.708l-2.5-2.5z"/></svg>
                                                </span>
                                                <span class="visually-hidden">Entrar al gym</span>
                                            </button>
                                        </form>
                                        <?php if ($gym['estado'] === 'activo'): ?>
                                            <form method="POST" action="backend/suspender_gym.php" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $gym['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning ga-admin-btn-icon" title="Suspender" onclick="return confirm('¿Suspender este gym?')">
                                                    <span class="ga-admin-icon" aria-hidden="true">
                                                        <svg viewBox="0 0 16 16"><path d="M5.5 3.5A.5.5 0 0 1 6 3h1a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5H6a.5.5 0 0 1-.5-.5v-9zm4 0A.5.5 0 0 1 10 3h1a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-9z"/></svg>
                                                    </span>
                                                    <span class="visually-hidden">Suspender</span>
                                                </button>
                                            </form>
                                        <?php elseif ($gym['estado'] === 'suspendido'): ?>
                                            <form method="POST" action="backend/activar_gym.php" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $gym['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success ga-admin-btn-icon" title="Activar" onclick="return confirm('¿Activar este gym?')">
                                                    <span class="ga-admin-icon" aria-hidden="true">
                                                        <svg viewBox="0 0 16 16"><path d="M5.5 3.5 12 8l-6.5 4.5z"/></svg>
                                                    </span>
                                                    <span class="visually-hidden">Activar</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/reutilizable/footer.php'; ?>
