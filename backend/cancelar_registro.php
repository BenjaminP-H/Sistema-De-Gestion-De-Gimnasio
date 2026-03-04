<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';
verificarSesion();

if (empty($_GET['foto'])) {
    header('Location: ../frontend/registro.php');
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

header('Location: ../frontend/registro.php');
exit;
?>
