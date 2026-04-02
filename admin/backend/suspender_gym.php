<?php
// backend/suspender_gym.php
require_once '../../reutilizable/funciones.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin_general') {
    header('Location: ../login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel_inicio.php');
    exit;
}

$gym_id = (int)($_POST['id'] ?? 0);
if (!$gym_id) { header('Location: ../panel_inicio.php'); exit; }

$pdo = conectar_db();

$pdo->prepare("UPDATE gyms SET estado = 'suspendido' WHERE id = ?")->execute([$gym_id]);
$pdo->prepare("INSERT INTO auditoria (gym_id, usuario_id, accion, detalle) VALUES (?, ?, 'suspender', 'Gym suspendido por falta de pago u otro motivo')")
    ->execute([$gym_id, $_SESSION['usuario_id']]);

header('Location: ../panel_inicio.php');
exit;
