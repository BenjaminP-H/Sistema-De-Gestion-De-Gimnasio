<?php
require_once __DIR__ . '/../reutilizable/header.php';
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();

$id_cliente = $_GET['id_cliente'] ?? null;
$gymId = $_SESSION['gym_id'] ?? null;

if (!$id_cliente || $gymId === null) {
    header('Location: inicio.php');
    exit;
}

$pdo = conectar_db();

/* CLIENTE */
$stmt = $pdo->prepare("
    SELECT id, nombre, apellido, dni, telefono, fecha_alta
    FROM clientes
    WHERE id = ? AND gym_id = ?
");
$stmt->execute([$id_cliente, $gymId]);
$cliente = $stmt->fetch();

/* ÚLTIMO PAGO */
$stmt = $pdo->prepare("
    SELECT 
        p.fecha_pago,
        DATEDIFF(p.fecha_vencimiento, p.fecha_pago) AS dias_pagados,
        pl.nombre AS nombre_plan
    FROM pagos p
    JOIN gym_planes gp ON p.gym_plan_id = gp.id
    JOIN planes pl ON gp.plan_id = pl.id
    WHERE p.cliente_id = ? AND p.gym_id = ?
    ORDER BY p.fecha_pago DESC
    LIMIT 1
");
$stmt->execute([$id_cliente, $gymId]);
$pago = $stmt->fetch();

$menu_activo = 'inicio';
require_once __DIR__ . '/../reutilizable/menu.php';

?>

<main class="container mt-5">

    <h2 class="mb-4 text-success">Membresía renovada</h2>

    <div class="card shadow p-4" style="max-width: 600px;">

        <div class="row align-items-center">

            <!-- FOTO -->
            <div class="col-4 text-center">
                <img
                    src="img/clientes/sinfoto.webp"
                    class="img-fluid rounded border"
                    style="max-height:150px"
                    alt="Foto cliente"
                >

                <small class="d-block mt-2">
                    <strong>Registro:</strong><br>
                    <?= htmlspecialchars($cliente['fecha_alta']) ?>
                </small>
            </div>

            <!-- DATOS -->
            <div class="col-8">
                <h4>
                    <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                </h4>

                <p class="mb-1"><strong>DNI:</strong> <?= $cliente['dni'] ?></p>
                <p class="mb-1"><strong>Tel:</strong> <?= $cliente['telefono'] ?></p>

                <?php if ($pago): ?>
                    <p class="mb-1"><strong>Plan:</strong> <?= $pago['nombre_plan'] ?></p>
                    <p class="mb-1"><strong>Días pagados:</strong> <?= $pago['dias_pagados'] ?></p>
                    <p class="mb-1"><strong>Último pago:</strong> <?= $pago['fecha_pago'] ?></p>
                <?php endif; ?>
            </div>

        </div>

        <div class="d-grid mt-4">
            <a href="frontend/inicio.php" class="btn btn-primary btn-lg">
                LISTO
            </a>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>
