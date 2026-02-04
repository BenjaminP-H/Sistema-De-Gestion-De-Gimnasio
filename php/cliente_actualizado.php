<?php
require_once '../php/header.php';
require_once '../php/session.php';
require_once '../php/funciones.php';

verificarSesion();

$id_cliente = $_GET['id_cliente'] ?? null;

if (!$id_cliente) {
    header('Location: inicio.php');
    exit;
}

$pdo = conectar_db();

/* CLIENTE */
$stmt = $pdo->prepare("
    SELECT id_cliente, nombres, apellidos, dni, telefono, foto_carnet, fecha_registro
    FROM clientes
    WHERE id_cliente = ?
");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch();

/* ÚLTIMO PAGO */
$stmt = $pdo->prepare("
    SELECT 
        p.fecha_pago,
        p.dias_pagados,
        pl.nombre_plan
    FROM pagos p
    JOIN planes pl ON p.id_plan = pl.id_plan
    WHERE p.id_cliente = ?
    ORDER BY p.fecha_pago DESC
    LIMIT 1
");
$stmt->execute([$id_cliente]);
$pago = $stmt->fetch();

?>

<main class="container mt-5">

    <h2 class="mb-4 text-success">Membresía renovada</h2>

    <div class="card shadow p-4" style="max-width: 600px;">

        <div class="row align-items-center">

            <!-- FOTO -->
            <div class="col-4 text-center">
                <?php if ($cliente['foto_carnet']): ?>
                    <img
                        src="../img/<?= htmlspecialchars($cliente['foto_carnet']) ?>"
                        class="img-fluid rounded border"
                        style="max-height:150px"
                    >
                <?php else: ?>
                    <div class="text-muted">Sin foto</div>
                <?php endif; ?>

                <small class="d-block mt-2">
                    <strong>Registro:</strong><br>
                    <?= htmlspecialchars($cliente['fecha_registro']) ?>
                </small>
            </div>

            <!-- DATOS -->
            <div class="col-8">
                <h4>
                    <?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['apellidos']) ?>
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
            <a href="inicio.php" class="btn btn-primary btn-lg">
                LISTO
            </a>
        </div>

    </div>

</main>

<?php require_once '../php/footer.php'; ?>