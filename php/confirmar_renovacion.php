<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

verificarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inicio.php');
    exit;
}

$pdo = conectar_db();

$id_cliente  = $_POST['id_cliente'] ?? null;
$id_plan     = $_POST['id_plan'] ?? null;
$dias        = $_POST['dias'] ?? null;
$monto       = $_POST['monto'] ?? null;
$metodo_pago = $_POST['metodo_pago'] ?? null;

if (!$id_cliente || !$id_plan || !$dias || !$monto || !$metodo_pago) {
    die('Datos incompletos');
}

/* ===============================
   CLIENTE
=============================== */
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->execute([$id_cliente]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente no encontrado');
}

/* ===============================
   PLAN
=============================== */
$stmt = $pdo->prepare("
    SELECT nombre_plan
    FROM planes
    WHERE id_plan = ?
    LIMIT 1
");
$stmt->execute([$id_plan]);
$nombre_plan = $stmt->fetchColumn();

if (!$nombre_plan) {
    die('Plan no encontrado');
}

/* ===============================
   ÚLTIMO PAGO
=============================== */
$stmt = $pdo->prepare("
    SELECT *
    FROM pagos
    WHERE id_cliente = ?
    ORDER BY fecha_pago DESC
    LIMIT 1
");
$stmt->execute([$id_cliente]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===============================
   NUEVO VENCIMIENTO (VISUAL)
=============================== */
$hoy = new DateTime();

if ($pago) {
    $venc = new DateTime($pago['fecha_pago']);
    $venc->modify("+{$pago['dias_pagados']} days");

    if ($venc >= $hoy) {
        $base = clone $venc;
        $estado = 'Activo';
        $badge = 'bg-success';
    } else {
        $base = clone $hoy;
        $estado = 'Vencido';
        $badge = 'bg-danger';
    }
} else {
    $base = clone $hoy;
    $estado = 'Sin pagos';
    $badge = 'bg-secondary';
}

$base->modify("+$dias days");
$nuevo_vencimiento = $base->format('Y-m-d');
?>

<div class="container mt-5 d-flex justify-content-center">
    <div class="card shadow-lg border-0" style="max-width:720px;width:100%;">
        <div class="card-header bg-dark text-warning text-center fw-bold">
            Confirmar renovación de membresía
        </div>

        <div class="card-body">
            <div class="row g-4 align-items-center">

                <!-- FOTO -->
                <div class="col-md-4 text-center">
                    <?php if ($cliente['foto_carnet']): ?>
                        <img src="img/clientes/<?= htmlspecialchars($cliente['foto_carnet']) ?>"
                             class="img-fluid rounded shadow-sm"
                             style="max-height:150px;">
                    <?php else: ?>
                        <div class="text-muted">Sin foto</div>
                    <?php endif; ?>
                </div>

                <!-- INFO -->
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <?= htmlspecialchars($cliente['nombres'].' '.$cliente['apellidos']) ?>
                    </h5>

                    <p class="mb-1"><strong>DNI:</strong> <?= $cliente['dni'] ?></p>
                    <p class="mb-1"><strong>Tel:</strong> <?= $cliente['telefono'] ?></p>

                    <p class="mb-2">
                        <strong>Estado actual:</strong>
                        <span class="badge <?= $badge ?>"><?= $estado ?></span>
                    </p>

                    <hr>

                    <p class="mb-1"><strong>Plan seleccionado:</strong> <?= htmlspecialchars($nombre_plan) ?></p>
                    <p class="mb-1"><strong>Días a agregar:</strong> <?= $dias ?></p>
                    <p class="mb-1"><strong>Monto:</strong> $<?= number_format($monto, 0, ',', '.') ?></p>
                    <p class="mb-1"><strong>Método de pago:</strong> <?= htmlspecialchars($metodo_pago) ?></p>

                    <p class="mt-2">
                        <strong>Nuevo vencimiento:</strong>
                        <span class="text-primary fw-bold"><?= $nuevo_vencimiento ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="card-footer d-flex justify-content-between">
            <a href="php/renovacion.php?id_cliente=<?= $id_cliente ?>" class="btn btn-outline-danger">
                ❌ Cancelar
            </a>

            <form action="php/procesar_renovacion.php" method="POST">
                <input type="hidden" name="id_cliente" value="<?= $id_cliente ?>">
                <input type="hidden" name="id_plan" value="<?= $id_plan ?>">
                <input type="hidden" name="dias" value="<?= $dias ?>">
                <input type="hidden" name="monto" value="<?= $monto ?>">
                <input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago) ?>">

                <button type="submit" class="btn btn-success">
                    ✅ Confirmar renovación
                </button>
            </form>
        </div>
    </div>
</div>