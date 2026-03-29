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
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

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
$gym_plan_id = $_POST['gym_plan_id'] ?? null;
$dias        = $_POST['dias'] ?? null;
$monto       = $_POST['monto'] ?? null;
$metodo_pago = $_POST['metodo_pago'] ?? null;

$gymId = $_SESSION['gym_id'] ?? null;

// ===============================
// VALIDACIÓN
// ===============================
$errores = [];

if (!$id_cliente)        $errores[] = 'Cliente inválido';
if (!$gym_plan_id)       $errores[] = 'Plan inválido';
if (!is_numeric($dias))  $errores[] = 'Días inválidos';
if (!is_numeric($monto)) $errores[] = 'Monto inválido';
if (!$metodo_pago)       $errores[] = 'Método de pago faltante';
if ($gymId === null)     $errores[] = 'Gym inválido';

if ($errores) {
    $mensaje = implode('<br>', $errores);
    goto salida;
}

// ===============================
// PROCESO
// ===============================
try {
    $pdo->beginTransaction();

    // Validar cliente del gym
    $stmt = $pdo->prepare("
        SELECT id
        FROM clientes
        WHERE id = ? AND gym_id = ?
        LIMIT 1
    ");
    $stmt->execute([$id_cliente, $gymId]);
    if (!$stmt->fetchColumn()) {
        throw new Exception('Cliente inválido');
    }

    // Validar plan del gym
    $stmt = $pdo->prepare("
        SELECT id
        FROM gym_planes
        WHERE id = ? AND gym_id = ? AND activo = 1
        LIMIT 1
    ");
    $stmt->execute([$gym_plan_id, $gymId]);
    if (!$stmt->fetchColumn()) {
        throw new Exception('Plan inválido');
    }

    // Último vencimiento
    $stmt = $pdo->prepare("
        SELECT fecha_vencimiento
        FROM pagos
        WHERE cliente_id = ? AND gym_id = ?
        ORDER BY fecha_pago DESC
        LIMIT 1
    ");
    $stmt->execute([$id_cliente, $gymId]);
    $fecha_actual = $stmt->fetchColumn();

    $fecha_vencimiento = calcularNuevaFechaVencimiento($fecha_actual, (int)$dias);

    // Insertar pago
    $stmt = $pdo->prepare("
        INSERT INTO pagos
        (gym_id, cliente_id, gym_plan_id, monto, fecha_pago, fecha_vencimiento)
        VALUES (?, ?, ?, ?, CURDATE(), ?)
    ");
    $stmt->execute([
        $gymId,
        $id_cliente,
        $gym_plan_id,
        $monto,
        $fecha_vencimiento
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        let segundos = 5;
        setInterval(() => {
            document.getElementById('contador').innerText = segundos;
            segundos--;
            if (segundos < 0) {
                window.location.href = '../frontend/inicio.php';
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
