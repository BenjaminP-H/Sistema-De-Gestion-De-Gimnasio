<?php
$page_class = 'inicio-page'; // Define clase para body de esta página
require_once __DIR__ . '/../reutilizable/header.php';
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();
$pdo = conectar_db();

/* =========================
   FILTROS
========================= */
$where = [];
$params = [];

if (!empty($_GET['f_estado'])) {
    if ($_GET['f_estado'] === 'Activo') {
        $where[] = "p.id_pago IS NOT NULL";
    } elseif ($_GET['f_estado'] === 'Inactivo') {
        $where[] = "p.id_pago IS NULL";
    }
}

if (!empty($_GET['f_plan'])) {
    $where[] = "pl.nombre_plan = :plan";
    $params[':plan'] = $_GET['f_plan'];
}

/* =========================
   CONSULTA
========================= */
$sql = "
SELECT 
    c.id_cliente,
    c.nombres,
    c.apellidos,
    c.dni,
    c.foto_carnet,
    p.dias_pagados,
    p.fecha_pago,
    pl.nombre_plan,
    DATE_ADD(p.fecha_pago, INTERVAL p.dias_pagados DAY) AS fecha_vencimiento
FROM clientes c
LEFT JOIN pagos p 
    ON p.id_pago = (
        SELECT p2.id_pago
        FROM pagos p2
        WHERE p2.id_cliente = c.id_cliente
        ORDER BY p2.fecha_pago DESC
        LIMIT 1
    )
LEFT JOIN planes pl ON p.id_plan = pl.id_plan
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.id_cliente DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>

<?php
$menu_activo = 'inicio';
$mostrar_filtros = true;
require_once __DIR__ . '/../reutilizable/menu.php';
?>
<!-- =========================
     LISTADO DE CLIENTES
========================= -->
<section class="container mt-4 inicio-page">

    <header class="mb-3">
        <h3 class="text-warning fw-bold">Clientes registrados</h3>
    </header>

    <article class="table-responsive inicio-tabla shadow-sm rounded-4">

        <table class="table table-dark table-hover align-middle text-center mb-0">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>DNI</th>
                    <th>Estado</th>
                    <th>Días</th>
                    <th>Último pago</th>
                    <th>Plan</th>
                    <th>Vencimiento</th>
                </tr>
            </thead>

            <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                <?php
                    $hoy = date('Y-m-d');
                    $activo = !empty($row['fecha_vencimiento']) && $row['fecha_vencimiento'] >= $hoy;

                    $estado = $activo ? 'Activo' : 'Inactivo';
                    $class  = $activo ? 'badge bg-success' : 'badge bg-danger';
                ?>

                <tr class="<?= $activo ? '' : 'cliente-inactivo' ?>">
                    <td>
                        <img
                            src="img/clientes/<?= htmlspecialchars($row['foto_carnet'] ?: 'sinfoto.webp') ?>"
                            alt="Foto cliente"
                            class="cliente-foto"
                        >
                    </td>

                    <td><?= htmlspecialchars($row['nombres']) ?></td>
                    <td><?= htmlspecialchars($row['apellidos']) ?></td>
                    <td><?= htmlspecialchars($row['dni']) ?></td>

                    <td>
                        <span class="<?= $class ?> px-3 py-2">
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

    </article>

</section>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>
