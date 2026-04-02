<?php
require_once '../../reutilizable/funciones.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Solo admin_general puede crear gyms
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin_general') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../nuevo_gym.php');
    exit;
}

// ─────────────────────────────────────────
// RECOGER Y VALIDAR DATOS
// ─────────────────────────────────────────
$gym_nombre           = trim($_POST['gym_nombre'] ?? '');
$gym_direccion        = trim($_POST['gym_direccion'] ?? '');
$gym_telefono         = trim($_POST['gym_telefono'] ?? '');
$gym_email            = trim($_POST['gym_email'] ?? '');
$gym_suscripcion_vence = trim($_POST['gym_suscripcion_vence'] ?? '') ?: null;

$user_nombre   = trim($_POST['user_nombre'] ?? '');
$user_usuario  = trim($_POST['user_usuario'] ?? '');
$user_email    = trim($_POST['user_email'] ?? '');
$user_password = $_POST['user_password'] ?? '';
$user_password2= $_POST['user_password2'] ?? '';

// Guardar datos para repoblar el form si hay error
$_SESSION['form_nuevo_gym'] = [
    'gym_nombre'            => $gym_nombre,
    'gym_direccion'         => $gym_direccion,
    'gym_telefono'          => $gym_telefono,
    'gym_email'             => $gym_email,
    'gym_suscripcion_vence' => $gym_suscripcion_vence,
    'user_nombre'           => $user_nombre,
    'user_usuario'          => $user_usuario,
    'user_email'            => $user_email,
];

function redirigir_error(string $msg): never {
    $_SESSION['error_nuevo_gym'] = $msg;
    header('Location: ../nuevo_gym.php');
    exit;
}

// Validaciones
if (empty($gym_nombre))   redirigir_error('El nombre del gym es obligatorio.');
if (empty($user_nombre))  redirigir_error('El nombre del administrador es obligatorio.');
if (empty($user_usuario)) redirigir_error('El nombre de usuario es obligatorio.');
if (empty($user_password)) redirigir_error('La contraseña es obligatoria.');
if ($user_password !== $user_password2) redirigir_error('Las contraseñas no coinciden.');
if (strlen($user_password) < 6) redirigir_error('La contraseña debe tener al menos 6 caracteres.');

$pdo = conectar_db();

// Verificar que el nombre de usuario no exista
$check = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
$check->execute([$user_usuario]);
if ($check->fetch()) redirigir_error("El nombre de usuario '$user_usuario' ya está en uso.");

// ─────────────────────────────────────────
// TRANSACCIÓN: INSERT gym + INSERT usuario
// Si algo falla, no queda nada a medias
// ─────────────────────────────────────────
try {
    $pdo->beginTransaction();

    // 1. Insertar el gym
    $stmt = $pdo->prepare("
        INSERT INTO gyms (nombre, direccion, telefono, email, estado, suscripcion_vence)
        VALUES (?, ?, ?, ?, 'activo', ?)
    ");
    $stmt->execute([$gym_nombre, $gym_direccion ?: null, $gym_telefono ?: null, $gym_email ?: null, $gym_suscripcion_vence]);
    $gym_id = $pdo->lastInsertId();

    // 2. Insertar el usuario admin_gym vinculado al gym
    $password_hash = password_hash($user_password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (gym_id, nombre, usuario, password, rol, activo)
        VALUES (?, ?, ?, ?, 'admin_gym', 1)
    ");
    $stmt->execute([$gym_id, $user_nombre, $user_usuario, $password_hash]);

    // 3. Registrar en auditoría
    $stmt = $pdo->prepare("
        INSERT INTO auditoria (gym_id, usuario_id, accion, detalle)
        VALUES (?, ?, 'crear_gym', ?)
    ");
    $stmt->execute([$gym_id, $_SESSION['usuario_id'], "Gym '$gym_nombre' creado con usuario '$user_usuario'"]);

    $pdo->commit();

    // Limpiar datos del form, todo salió bien
    unset($_SESSION['form_nuevo_gym']);
    $_SESSION['exito_nuevo_gym'] = "✅ Gym '$gym_nombre' creado correctamente.";
    header('Location: ../nuevo_gym.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    redirigir_error('Error al guardar. Intentá de nuevo.');
}
