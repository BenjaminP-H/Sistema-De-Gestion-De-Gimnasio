<?php
require_once 'session.php';
verificarSesion();

if (empty($_GET['foto'])) {
    header('Location: ../registro.php');
    exit;
}

$foto_temp = basename($_GET['foto']); // seguridad: evitar rutas raras
$ruta_temp = __DIR__ . '/../img/temporal/' . $foto_temp;

if (file_exists($ruta_temp)) {
    unlink($ruta_temp); // borra el archivo
}

// Opcional: mensaje flash
$_SESSION['flash_message'] = 'Registro cancelado y foto eliminada';
$_SESSION['flash_type'] = 'danger';

header('Location: ../registro.php');
exit;
?>