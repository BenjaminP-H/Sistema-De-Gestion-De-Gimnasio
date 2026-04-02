<?php
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
$gym = $pdo->prepare("SELECT id, nombre FROM gyms WHERE id = ? LIMIT 1");
$gym->execute([$gym_id]);
$gym = $gym->fetch();

if (!$gym) { header('Location: ../panel_inicio.php'); exit; }

// Guardar contexto de impersonation en sesión
$_SESSION['impersonando_gym_id']     = $gym['id'];
$_SESSION['impersonando_gym_nombre'] = $gym['nombre'];

// Redirigir al inicio del gym
header('Location: ../../frontend/inicio.php');
exit;
