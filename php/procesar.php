<?php
require_once '../php/session.php';
require_once __DIR__ . '/funciones.php';

$conn = conectar_db();

$usuario  = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = [];
}

$_SESSION['intentos'][$usuario] = $_SESSION['intentos'][$usuario] ?? 0;
$_SESSION['intentos'][$ip] = $_SESSION['intentos'][$ip] ?? 0;

if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
    $_SESSION['error_login'] = 'Acceso bloqueado por demasiados intentos.';
    notificar_admin($usuario, $ip);
    header('Location: ../index.php');
    exit;
}

$sql = "SELECT id_usuario, usuario, password, rol FROM usuarios WHERE usuario = :usuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['intentos'][$usuario] = 0;
    $_SESSION['intentos'][$ip] = 0;

    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['rol'] = $user['rol'];

    session_regenerate_id(true);
    header('Location: inicio.php');
    exit;
}

$_SESSION['intentos'][$usuario]++;
$_SESSION['intentos'][$ip]++;

$_SESSION['error_login'] = 'Usuario o contraseña incorrectos.';

if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
    $_SESSION['error_login'] = 'Acceso bloqueado por demasiados intentos.';
    notificar_admin($usuario, $ip);
}

header('Location: ../index.php');
exit;
