<?php
require_once '../../reutilizable/funciones.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin_general') {
    header('Location: ../login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cobros.php'); exit;
}

$gym_id       = (int)($_POST['gym_id'] ?? 0);
$monto        = (float)($_POST['monto'] ?? 0);
$fecha_pago   = trim($_POST['fecha_pago'] ?? '');
$periodo_desde= trim($_POST['periodo_desde'] ?? '');
$periodo_hasta= trim($_POST['periodo_hasta'] ?? '');
$notas        = trim($_POST['notas'] ?? '') ?: null;

if (!$gym_id || $monto <= 0 || !$fecha_pago || !$periodo_desde || !$periodo_hasta) {
    $_SESSION['error_cobro'] = 'Completá todos los campos obligatorios.';
    header('Location: ../cobros.php'); exit;
}

if ($periodo_hasta < $periodo_desde) {
    $_SESSION['error_cobro'] = 'La fecha "hasta" no puede ser anterior a "desde".';
    header('Location: ../cobros.php'); exit;
}

$pdo = conectar_db();

try {
    $pdo->beginTransaction();

    // Insertar el cobro
    $stmt = $pdo->prepare("
        INSERT INTO pagos_suscripciones
            (gym_id, monto, fecha_pago, periodo_desde, periodo_hasta, notas, registrado_por)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$gym_id, $monto, $fecha_pago, $periodo_desde, $periodo_hasta, $notas, $_SESSION['usuario_id']]);

    // Actualizar suscripcion_vence en gyms con la fecha hasta del período
    $stmt = $pdo->prepare("UPDATE gyms SET suscripcion_vence = ? WHERE id = ?");
    $stmt->execute([$periodo_hasta, $gym_id]);

    // Registrar en auditoría
    $stmt = $pdo->prepare("
        INSERT INTO auditoria (gym_id, usuario_id, accion, detalle)
        VALUES (?, ?, 'registrar_cobro', ?)
    ");
    $stmt->execute([$gym_id, $_SESSION['usuario_id'], "Cobro de \${$monto} registrado. Período: {$periodo_desde} al {$periodo_hasta}"]);

    $pdo->commit();

    $_SESSION['exito_cobro'] = '✅ Cobro registrado correctamente.';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_cobro'] = 'Error al guardar el cobro. Intentá de nuevo.';
}

header('Location: ../cobros.php');
exit;
