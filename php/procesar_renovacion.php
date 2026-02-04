<?php
// ===============================
// ERRORES (solo desarrollo)
// ===============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ===============================
// REQUIRES
// ===============================
require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

// ===============================
// SESIÓN
// ===============================
verificarSesion();

// ===============================
// VARIABLES DE ESTADO
// ===============================
$estado  = 'error'; // success | error
$mensaje = 'Ocurrió un error inesperado.';

// ===============================
// MÉTODO
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $mensaje = 'Método no permitido.';
    goto salida;
}

// ===============================
// CONEXIÓN
// ===============================
$pdo = conectar_db();
if (!$pdo) {
    $mensaje = 'No se pudo conectar a la base de datos.';
    goto salida;
}

// ===============================
// DATOS
// ===============================
$id_cliente  = $_POST['id_cliente'] ?? null;
$id_plan     = $_POST['id_plan'] ?? null;
$dias        = $_POST['dias'] ?? null;
$monto       = $_POST['monto'] ?? null;
$metodo_pago = $_POST['metodo_pago'] ?? null;

// ===============================
// VALIDACIÓN
// ===============================
$errores = [];

if (!$id_cliente)        $errores[] = 'Cliente inválido';
if (!$id_plan)           $errores[] = 'Plan inválido';
if (!is_numeric($dias))  $errores[] = 'Días inválidos';
if (!is_numeric($monto)) $errores[] = 'Monto inválido';
if (!$metodo_pago)       $errores[] = 'Método de pago faltante';

if ($errores) {
    $mensaje = implode('<br>', $errores);
    goto salida;
}

// ===============================
// PROCESO
// ===============================
try {
    $pdo->beginTransaction();

    // Último vencimiento
    $stmt = $pdo->prepare("
        SELECT DATE_ADD(fecha_pago, INTERVAL dias_pagados DAY)
        FROM pagos
        WHERE id_cliente = ?
        ORDER BY fecha_pago DESC
        LIMIT 1
    ");
    $stmt->execute([$id_cliente]);
    $fecha_actual = $stmt->fetchColumn();

    $hoy = new DateTime();
    $base = ($fecha_actual && new DateTime($fecha_actual) > $hoy)
        ? new DateTime($fecha_actual)
        : clone $hoy;

    $base->modify("+$dias days");

    // Insertar pago
    $stmt = $pdo->prepare("
        INSERT INTO pagos
        (id_cliente, id_plan, monto, modo_pago, dias_pagados, fecha_pago)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $id_cliente,
        $id_plan,
        $monto,
        $metodo_pago,
        $dias
    ]);

    $pdo->commit();

    $estado  = 'success';
    $mensaje = 'Renovación realizada correctamente.';

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $mensaje = 'Error al procesar la renovación.';
}

// ===============================
// SALIDA HTML
// ===============================
salida:
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Renovación</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script>
        let segundos = 5;
        setInterval(() => {
            document.getElementById('contador').innerText = segundos;
            segundos--;
            if (segundos < 0) {
                window.location.href = 'php/inicio.php';
            }
        }, 1000);
    </script>
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">

<div class="card shadow p-4 text-center" style="max-width:500px;">
    <?php if ($estado === 'success'): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">✅ Éxito</h4>
            <p><?= $mensaje ?></p>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">❌ Error</h4>
            <p><?= $mensaje ?></p>
        </div>
    <?php endif; ?>

    <p class="text-muted mb-0">
        Redirigiendo al inicio en <strong><span id="contador">5</span></strong> segundos…
    </p>
</div>

</body>
</html>