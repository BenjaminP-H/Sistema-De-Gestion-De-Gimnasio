<?php
require_once __DIR__ . '/reutilizable/session_admin.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

$titulo_pagina = 'Cobros';
$pdo = conectar_db();

// Si viene con ?id=X precarga el gym en el formulario
$gym_id_preseleccionado = (int)($_GET['id'] ?? 0);

// Mensajes
$error = $_SESSION['error_cobro'] ?? '';
$exito = $_SESSION['exito_cobro'] ?? '';
unset($_SESSION['error_cobro'], $_SESSION['exito_cobro']);

// Todos los gyms para el select
$gyms = $pdo->query("SELECT id, nombre FROM gyms WHERE estado != 'cancelado' ORDER BY nombre")->fetchAll();

// Historial completo de cobros
$cobros = $pdo->query("
    SELECT
        ps.id,
        g.nombre AS gym_nombre,
        ps.monto,
        ps.fecha_pago,
        ps.periodo_desde,
        ps.periodo_hasta,
        ps.notas,
        ps.created_at
    FROM pagos_suscripciones ps
    JOIN gyms g ON g.id = ps.gym_id
    ORDER BY ps.fecha_pago DESC
")->fetchAll();
?>
<?php require_once __DIR__ . '/reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/reutilizable/menu.php'; ?>

<div class="container-fluid py-4 px-4">

    <h4 class="fw-bold mb-4">💰 Cobros de suscripciones</h4>

    <div class="row g-4">

        <!-- FORMULARIO NUEVO COBRO -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">➕ Registrar cobro</div>
                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if ($exito): ?>
                        <div class="alert alert-success py-2 small"><?= htmlspecialchars($exito) ?></div>
                    <?php endif; ?>

                    <form action="backend/registrar_cobro.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small">Gym <span class="text-danger">*</span></label>
                            <select name="gym_id" class="form-select" required>
                                <option value="">— Seleccioná un gym —</option>
                                <?php foreach ($gyms as $g): ?>
                                    <option value="<?= $g['id'] ?>"
                                        <?= $g['id'] === $gym_id_preseleccionado ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Monto <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto" class="form-control"
                                       min="0" step="0.01" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Fecha de pago <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_pago" class="form-control"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Período desde <span class="text-danger">*</span></label>
                            <input type="date" name="periodo_desde" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Período hasta <span class="text-danger">*</span></label>
                            <input type="date" name="periodo_hasta" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">Notas</label>
                            <input type="text" name="notas" class="form-control"
                                   placeholder="Ej: pagó por transferencia">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar cobro</button>
                    </form>

                </div>
            </div>
        </div>

        <!-- HISTORIAL -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">📋 Historial de cobros</div>
                <div class="card-body p-0">
                    <?php if (empty($cobros)): ?>
                        <div class="text-center text-muted py-5">Sin cobros registrados todavía.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Gym</th>
                                        <th>Monto</th>
                                        <th>Fecha pago</th>
                                        <th>Período cubierto</th>
                                        <th>Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cobros as $c): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($c['gym_nombre']) ?></td>
                                        <td class="fw-bold text-success">
                                            $<?= number_format($c['monto'], 2, ',', '.') ?>
                                        </td>
                                        <td class="small"><?= date('d/m/Y', strtotime($c['fecha_pago'])) ?></td>
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
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/reutilizable/footer.php'; ?>
