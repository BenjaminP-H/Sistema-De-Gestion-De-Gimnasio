<?php
if (session_status() === PHP_SESSION_NONE) session_start();

unset($_SESSION['impersonando_gym_id'], $_SESSION['impersonando_gym_nombre']);

header('Location: ../panel_inicio.php');
exit;
