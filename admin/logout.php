<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar toda la sesión del admin general
session_unset();
session_destroy();

header('Location: login.php');
exit;
