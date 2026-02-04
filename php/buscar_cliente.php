<?php
require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

verificarSesion();

$cliente = null;
$pago = null;
$error = null;
$estado_real = 'Inactivo';
$fecha_vencimiento = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);

    if ($dni === '') {
        $error = 'Ingresá un DNI válido.';
    } else {
        try {
            $pdo = conectar_db();

            // CLIENTE
            $stmt = $pdo->prepare("
                SELECT id_cliente, nombres, apellidos, dni, telefono, foto_carnet, fecha_registro
                FROM clientes
                WHERE dni = :dni
                LIMIT 1
            ");
            $stmt->execute(['dni' => $dni]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                // ÚLTIMO PAGO
                $stmt = $pdo->prepare("
                    SELECT 
                        fecha_pago,
                        dias_pagados,
                        pl.nombre_plan
                    FROM pagos p
                    JOIN planes pl ON p.id_plan = pl.id_plan
                    WHERE p.id_cliente = :id_cliente
                    ORDER BY fecha_pago DESC
                    LIMIT 1
                ");
                $stmt->execute(['id_cliente' => $cliente['id_cliente']]);
                $pago = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($pago) {
                    $fecha_vencimiento = date(
                        'Y-m-d',
                        strtotime($pago['fecha_pago'] . ' +' . $pago['dias_pagados'] . ' days')
                    );

                    if ($fecha_vencimiento >= date('Y-m-d')) {
                        $estado_real = 'Activo';
                    }
                }
            } else {
                $error = 'Cliente no encontrado.';
            }

        } catch (PDOException $e) {
            $error = 'Error al buscar el cliente.';
        }
    }
}
?>

<main class="container mt-5">

<h2 class="mb-4">Buscar cliente</h2>

<form method="POST" class="mb-4">
    <div class="input-group">
        <input type="text" name="dni" class="form-control" placeholder="Ingresar DNI" required>
        <button class="btn btn-dark">Buscar</button>
        <a href="php/inicio.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($cliente): ?>
<div class="card shadow p-3" style="max-width: 650px;">
    <div class="row g-3 align-items-center">

        <!-- FOTO -->
        <div class="col-4 text-center">
            <?php if (!empty($cliente['foto_carnet'])): ?>
                <img src="img/clientes/<?= htmlspecialchars($cliente['foto_carnet']) ?>"
                     class="img-fluid border rounded"
                     style="max-height:140px;">
            <?php else: ?>
                <div class="text-muted">Sin foto</div>
            <?php endif; ?>

            <small class="d-block mt-2">
                <strong>Registrado:</strong><br>
                <?= htmlspecialchars($cliente['fecha_registro']) ?>
            </small>

            <?php if ($pago): ?>
                <small class="d-block mt-1">
                    <strong>Último pago:</strong><br>
                    <?= htmlspecialchars($pago['fecha_pago']) ?>
                </small>
            <?php endif; ?>
        </div>

        <!-- DATOS -->
        <div class="col-8">
            <h5><?= htmlspecialchars($cliente['nombres'].' '.$cliente['apellidos']) ?></h5>

            <p class="mb-1"><strong>DNI:</strong> <?= $cliente['dni'] ?></p>
            <p class="mb-1"><strong>Tel:</strong> <?= $cliente['telefono'] ?></p>

            <?php if ($pago): ?>
                <p class="mb-1"><strong>Plan:</strong> <?= $pago['nombre_plan'] ?></p>
                <p class="mb-1"><strong>Días pagados:</strong> <?= $pago['dias_pagados'] ?></p>
                <p class="mb-1"><strong>Vence:</strong> <?= $fecha_vencimiento ?></p>

                <span class="badge <?= $estado_real === 'Activo' ? 'bg-success' : 'bg-danger' ?>">
                    <?= $estado_real ?>
                </span>
            <?php else: ?>
                <p class="text-warning">Sin pagos registrados</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-grid mt-3">
        <a href="php/renovacion.php?id_cliente=<?= $cliente['id_cliente'] ?>" class="btn btn-success">
            Renovar membresía
        </a>
    </div>
</div>
<?php endif; ?>

</main>

<?php require_once '../php/footer.php'; ?>