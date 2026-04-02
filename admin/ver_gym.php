<?php
require_once __DIR__ . '/reutilizable/session_admin.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

$gym_id = (int)($_GET['id'] ?? 0);
if (!$gym_id) {
    header('Location: panel_inicio.php');
    exit;
}

$pdo = conectar_db();

// Datos del gym
$stmt = $pdo->prepare("SELECT * FROM gyms WHERE id = ? LIMIT 1");
$stmt->execute([$gym_id]);
$gym = $stmt->fetch();

if (!$gym) {
    header('Location: panel_inicio.php');
    exit;
}

// Usuario admin_gym vinculado
$stmt = $pdo->prepare("SELECT id, nombre, usuario, activo FROM usuarios WHERE gym_id = ? AND rol = 'admin_gym' LIMIT 1");
$stmt->execute([$gym_id]);
$usuario_gym = $stmt->fetch();

// Total clientes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE gym_id = ?");
$stmt->execute([$gym_id]);
$total_clientes = $stmt->fetchColumn();

// Historial de cobros
$stmt = $pdo->prepare("
    SELECT monto, fecha_pago, periodo_desde, periodo_hasta, notas
    FROM pagos_suscripciones
    WHERE gym_id = ?
    ORDER BY fecha_pago DESC
    LIMIT 10
");
$stmt->execute([$gym_id]);
$cobros = $stmt->fetchAll();

// Auditoría
$stmt = $pdo->prepare("
    SELECT a.accion, a.detalle, a.created_at, u.nombre AS hecho_por
    FROM auditoria a
    JOIN usuarios u ON u.id = a.usuario_id
    WHERE a.gym_id = ?
    ORDER BY a.created_at DESC
    LIMIT 20
");
$stmt->execute([$gym_id]);
$auditoria = $stmt->fetchAll();

$titulo_pagina = 'Ver Gym — ' . htmlspecialchars($gym['nombre']);

function badgeEstado(string $estado): string {
    return match($estado) {
        'activo'     => '<span class="badge bg-success">Activo</span>',
        'suspendido' => '<span class="badge bg-warning text-dark">Suspendido</span>',
        'cancelado'  => '<span class="badge bg-danger">Cancelado</span>',
        default      => '<span class="badge bg-secondary">—</span>',
    };
}
?>
<?php require_once __DIR__ . '/reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/reutilizable/menu.php'; ?>

<div class="container-fluid py-4 px-4">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="panel_inicio.php" class="btn btn-sm btn-outline-secondary">← Volver</a>
        <h4 class="mb-0 fw-bold">🏋️ <?= htmlspecialchars($gym['nombre']) ?></h4>
        <?= badgeEstado($gym['estado']) ?>
    </div>

    <div class="row g-4">

        <!-- COLUMNA IZQUIERDA: datos + acciones -->
        <div class="col-12 col-lg-4">

            <!-- Datos del gym -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-dark text-white fw-semibold">📋 Datos del gym</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Dirección</td><td><?= htmlspecialchars($gym['direccion'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">Teléfono</td><td><?= htmlspecialchars($gym['telefono'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($gym['email'] ?? '—') ?></td></tr>
                        <tr><td class="text-muted">Clientes</td><td><strong><?= $total_clientes ?></strong></td></tr>
                        <tr><td class="text-muted">Suscripción vence</td>
                            <td><?= $gym['suscripcion_vence'] ? date('d/m/Y', strtotime($gym['suscripcion_vence'])) : '—' ?></td></tr>
                        <tr><td class="text-muted">Último acceso</td>
                            <td><?= $gym['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($gym['ultimo_acceso'])) : '—' ?></td></tr>
                        <tr><td class="text-muted">Creado</td>
                            <td><?= date('d/m/Y', strtotime($gym['fecha_creacion'])) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Usuario admin del gym -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-dark text-white fw-semibold">👤 Administrador</div>
                <div class="card-body">
                    <?php if ($usuario_gym): ?>
                        <div><strong><?= htmlspecialchars($usuario_gym['nombre']) ?></strong></div>
                        <div class="text-muted small">@<?= htmlspecialchars($usuario_gym['usuario']) ?></div>
                        <div class="mt-1">
                            <?= $usuario_gym['activo']
                                ? '<span class="badge bg-success">Activo</span>'
                                : '<span class="badge bg-danger">Inactivo</span>' ?>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">Sin usuario asignado</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">⚙️ Acciones</div>
                <div class="card-body d-flex flex-column gap-2">

                    <!-- TODO: cuando implementes el PIN, reemplazá estos href
                         por onclick="abrirModalPin('entrar', <?= $gym_id ?>)"
                         y descomentá el modal de PIN al final de este archivo -->
                    <form method="POST" action="backend/entrar_gym.php">
                        <input type="hidden" name="id" value="<?= $gym_id ?>">
                        <button type="submit" class="btn btn-outline-secondary">
                            🔑 Entrar al gym
                        </button>
                    </form>

                    <?php if ($gym['estado'] === 'activo'): ?>
                        <!-- TODO: PIN → onclick="abrirModalPin('suspender', <?= $gym_id ?>)" -->
                        <form method="POST" action="backend/suspender_gym.php">
                            <input type="hidden" name="id" value="<?= $gym_id ?>">
                            <button type="submit" class="btn btn-outline-warning" onclick="return confirm('¿Suspender este gym?')">
                                ⏸️ Suspender
                            </button>
                        </form>
                    <?php elseif ($gym['estado'] === 'suspendido'): ?>
                        <!-- TODO: PIN → onclick="abrirModalPin('activar', <?= $gym_id ?>)" -->
                        <form method="POST" action="backend/activar_gym.php">
                            <input type="hidden" name="id" value="<?= $gym_id ?>">
                            <button type="submit" class="btn btn-outline-success" onclick="return confirm('¿Reactivar este gym?')">
                                ▶️ Activar
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($gym['estado'] !== 'cancelado'): ?>
                        <!-- TODO: PIN → onclick="abrirModalPin('cancelar', <?= $gym_id ?>)" -->
                        <form method="POST" action="backend/cancelar_gym.php">
                            <input type="hidden" name="id" value="<?= $gym_id ?>">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Cancelar definitivamente este gym? Esta acción no se puede deshacer.')">
                                ❌ Cancelar gym
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="cobros.php?id=<?= $gym_id ?>" class="btn btn-outline-primary">
                        💰 Registrar cobro
                    </a>

                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA: cobros + auditoría -->
        <div class="col-12 col-lg-8">

            <!-- Historial de cobros -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white fw-semibold">💰 Últimos cobros</div>
                <div class="card-body p-0">
                    <?php if (empty($cobros)): ?>
                        <div class="text-muted text-center py-4">Sin cobros registrados.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha pago</th>
                                        <th>Monto</th>
                                        <th>Período cubierto</th>
                                        <th>Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cobros as $c): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($c['fecha_pago'])) ?></td>
                                        <td><strong>$<?= number_format($c['monto'], 2, ',', '.') ?></strong></td>
                                        <td class="small">
                                            <?= date('d/m/Y', strtotime($c['periodo_desde'])) ?>
                                            al
                                            <?= date('d/m/Y', strtotime($c['periodo_hasta'])) ?>
                                        </td>
                                        <td class="small text-muted"><?= htmlspecialchars($c['notas'] ?? '—') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Auditoría -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">📝 Historial de acciones</div>
                <div class="card-body p-0">
                    <?php if (empty($auditoria)): ?>
                        <div class="text-muted text-center py-4">Sin acciones registradas.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                        <th>Detalle</th>
                                        <th>Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($auditoria as $a): ?>
                                    <tr>
                                        <td class="small"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($a['accion']) ?></span></td>
                                        <td class="small"><?= htmlspecialchars($a['detalle'] ?? '—') ?></td>
                                        <td class="small text-muted"><?= htmlspecialchars($a['hecho_por']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     TODO: MODAL DE PIN — descomentar cuando implementes el PIN
     ============================================================

<div class="modal fade" id="modalPin" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🔒 Confirmá tu PIN</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="password" id="inputPin" class="form-control" placeholder="PIN de seguridad">
                <div id="errorPin" class="text-danger small mt-1 d-none">PIN incorrecto.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarPin()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
let _pinAccion = '';
let _pinGymId  = 0;
const rutas = {
    entrar:    'backend/entrar_gym.php',
    suspender: 'backend/suspender_gym.php',
    activar:   'backend/activar_gym.php',
    cancelar:  'backend/cancelar_gym.php',
};

function abrirModalPin(accion, gymId) {
    _pinAccion = accion;
    _pinGymId  = gymId;
    document.getElementById('inputPin').value = '';
    document.getElementById('errorPin').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalPin')).show();
}

function confirmarPin() {
    const pin = document.getElementById('inputPin').value;
    fetch('backend/verificar_pin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pin })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            window.location.href = rutas[_pinAccion] + '?id=' + _pinGymId;
        } else {
            document.getElementById('errorPin').classList.remove('d-none');
        }
    });
}
</script>
============================================================ -->

<?php require_once __DIR__ . '/reutilizable/footer.php'; ?>
