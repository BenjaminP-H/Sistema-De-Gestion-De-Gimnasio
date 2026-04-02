<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo permite acceso a usuarios con rol admin_general
// Si no cumple, redirige al login del panel admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin_general' || empty($_SESSION['admin_login'])) {
    header('Location: ../login.php');
    exit;
}
?>
