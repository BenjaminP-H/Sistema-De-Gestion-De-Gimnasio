<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../reutilizable/funciones.php';

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$usuario  = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($usuario) || empty($password)) {
    $_SESSION['error_login_admin'] = 'Completá usuario y contraseña.';
    header('Location: ../login.php');
    exit;
}

$pdo = conectar_db();

$stmt = $pdo->prepare("
    SELECT id, nombre, usuario, password, rol
    FROM usuarios
    WHERE usuario = ?
      AND rol = 'admin_general'
      AND activo = 1
    LIMIT 1
");
$stmt->execute([$usuario]);
$user = $stmt->fetch();

// Verificar que existe y que la contraseña es correcta
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error_login_admin'] = 'Usuario o contraseña incorrectos.';
    header('Location: ../login.php');
    exit;
}

// Login exitoso — guardar sesión
$_SESSION['usuario']    = $user['usuario'];
$_SESSION['nombre']     = $user['nombre'];
$_SESSION['usuario_id'] = $user['id'];
$_SESSION['rol']        = $user['rol'];
$_SESSION['admin_login'] = true;

session_regenerate_id(true);

header('Location: ../panel_inicio.php');
exit;
