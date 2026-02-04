<?php
$page_class = 'inicio-page'; // Define clase para body de esta página
require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

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

<!-- =========================
     NAVBAR
========================= -->
<nav class="navbar navbar-expand-xl navbar-dark bg-dark inicio-navbar">

    <section class="container-fluid">

        <header class="navbar-brand fw-bold">
            <a href="php/inicio.php" class="text-decoration-none text-white">
                🏋️ Gimnasio
            </a>
        </header>

        <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuNavbar"
            aria-controls="menuNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <section class="collapse navbar-collapse" id="menuNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0 inicio-menu">
                <li class="nav-item">
                    <a class="nav-link active" href="php/inicio.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="php/registro.php">Registrar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="php/buscar_cliente.php">Renovar</a>
                </li>
            </ul>

            <!-- FILTROS -->
            <form method="get" class="d-flex filtros-navbar inicio-filtros">
                <select name="f_estado" class="form-select form-select-sm">
                    <option value="">Estado</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>

                <select name="f_plan" class="form-select form-select-sm">
                    <option value="">Plan</option>
                    <option value="Aparatos">Aparatos</option>
                    <option value="Funcional">Funcional</option>
                    <option value="Zumba">Zumba</option>
                    <option value="Pase Libre">Pase Libre</option>
                    <option value="Día">Día</option>
                </select>

                <button class="btn btn-warning btn-sm">
                    <i class="bi bi-funnel-fill"></i> Filtrar
                </button>
            </form>

        </section>

    </section>

</nav>
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

<?php require_once 'footer.php'; ?>