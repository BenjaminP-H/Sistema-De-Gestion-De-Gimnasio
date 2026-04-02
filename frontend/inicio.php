<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();
$pdo = conectar_db();

/* =========================
   FILTROS
========================= */
$where = [];
$params = [];

$gymId = $_SESSION['gym_id'] ?? null;
$where[] = "c.gym_id = :gym_id";
$params[':gym_id'] = $gymId;

if (!empty($_GET['f_estado'])) {
    if ($_GET['f_estado'] === 'Activo') {
        $where[] = "p.id IS NOT NULL";
    } elseif ($_GET['f_estado'] === 'Inactivo') {
        $where[] = "p.id IS NULL";
    }
}

if (!empty($_GET['f_plan'])) {
    $plan = $_GET['f_plan'];
    if ($plan === 'Dia') {
        $plan = html_entity_decode('D&iacute;a', ENT_QUOTES, 'UTF-8');
    }
    $where[] = "pl.nombre = :plan";
    $params[':plan'] = $plan;
}

/* =========================
   CONSULTA
========================= */
$sql = "
SELECT 
    c.id,
    c.nombre,
    c.apellido,
    c.dni,
    NULL AS foto_carnet,
    DATEDIFF(p.fecha_vencimiento, p.fecha_pago) AS dias_pagados,
    p.fecha_pago,
    pl.nombre AS nombre_plan,
    p.fecha_vencimiento
FROM clientes c
LEFT JOIN pagos p 
    ON p.id = (
        SELECT p2.id
        FROM pagos p2
        WHERE p2.cliente_id = c.id
          AND p2.gym_id = :gym_id
        ORDER BY p2.fecha_pago DESC
        LIMIT 1
    )
LEFT JOIN gym_planes gp ON p.gym_plan_id = gp.id
LEFT JOIN planes pl ON gp.plan_id = pl.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hoy = date('Y-m-d');
$total_clientes = count($clientes);
$activos = 0;
$inactivos = 0;

foreach ($clientes as $row) {
    $activo = !empty($row['fecha_vencimiento']) && $row['fecha_vencimiento'] >= $hoy;
    if ($activo) {
        $activos++;
    } else {
        $inactivos++;
    }
}

$page_class = 'ga-inicio-page';
$menu_activo = 'inicio';
$mostrar_filtros = true;
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/../reutilizable/menu.php'; ?>

<main class="ga-inicio-main">
    <section class="ga-inicio-hero">
        <section class="container">
            <section class="row align-items-center gy-4">
                <section class="col-lg-6">
                    <span class="ga-kicker">Panel operativo</span>
                    <h1 class="ga-hero-title">Control total de tus clientes</h1>
                    <p class="ga-hero-sub">
                        Visualiza estados, planes y vencimientos al instante. Usa los filtros
                        del menu para afinar el listado y actuar rapido.
                    </p>
                </section>

                <section class="col-lg-6">
                    <section class="ga-inicio-stats">
                        <section class="ga-stat-card">
                            <span class="ga-stat-label">Clientes totales</span>
                            <span class="ga-stat-value"><?= $total_clientes ?></span>
                        </section>
                        <section class="ga-stat-card">
                            <span class="ga-stat-label">Activos</span>
                            <span class="ga-stat-value"><?= $activos ?></span>
                        </section>
                        <section class="ga-stat-card">
                            <span class="ga-stat-label">Inactivos</span>
                            <span class="ga-stat-value"><?= $inactivos ?></span>
                        </section>
                    </section>
                </section>
            </section>
        </section>
    </section>

    <section class="container ga-inicio-table-section">
        <section class="ga-inicio-table-header">
            <section>
                <p class="ga-result-eyebrow">Listado</p>
                <h3>Clientes registrados</h3>
            </section>
            <section class="ga-inicio-summary">
                <span class="ga-summary-pill">Activos: <?= $activos ?></span>
                <span class="ga-summary-pill">Inactivos: <?= $inactivos ?></span>
            </section>
        </section>

        <section class="ga-inicio-table-card">
            <?php if (empty($clientes)): ?>
                <section class="ga-empty">
                    <h4>No hay clientes para mostrar</h4>
                    <p>Registra un cliente nuevo o ajusta los filtros del menu.</p>
                    <a href="frontend/registro.php" class="btn ga-btn-primary">Registrar cliente</a>
                </section>
            <?php else: ?>
                <section class="table-responsive">
                    <table class="table table-hover align-middle mb-0 ga-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DNI</th>
                                <th>Estado</th>
                                <th>Dias</th>
                                <th>Ultimo pago</th>
                                <th>Plan</th>
                                <th>Vencimiento</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($clientes as $row) { ?>

                            <?php
                                $activo = !empty($row['fecha_vencimiento']) && $row['fecha_vencimiento'] >= $hoy;
                                $estado = $activo ? 'Activo' : 'Inactivo';
                                $status_class  = $activo ? 'ga-status ga-status-ok' : 'ga-status ga-status-warn';
                            ?>

                            <tr class="<?= $activo ? '' : 'ga-row-muted' ?>">
                                <td>
                                    <img
                                        src="img/clientes/<?= htmlspecialchars($row['foto_carnet'] ?: 'sinfoto.webp') ?>"
                                        alt="Foto cliente"
                                        class="cliente-foto"
                                    >
                                </td>

                                <td><?= htmlspecialchars($row['nombre']) ?></td>
                                <td><?= htmlspecialchars($row['apellido']) ?></td>
                                <td><?= htmlspecialchars($row['dni']) ?></td>

                                <td>
                                    <span class="<?= $status_class ?>">
                                        <?= $estado ?>
                                    </span>
                                </td>

                                <td><?= $row['dias_pagados'] ?? '-' ?></td>
                                <td><?= $row['fecha_pago'] ?? '-' ?></td>
                                <td><?= $row['nombre_plan'] ?? '-' ?></td>

                                <td class="<?= $activo ? 'text-success' : 'text-danger fw-bold' ?>">
                                    <?= $row['fecha_vencimiento'] ?? 'Sin pago' ?>
                                </td>
                            </tr>

                        <?php } ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>
        </section>
    </section>
</main>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>



