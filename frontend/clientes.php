<?php
// clientes.php: Gestión de todos los clientes
require_once __DIR__ . '/../reutilizable/header.php';
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';
verificarSesion();

$menu_activo = 'inicio';
require_once __DIR__ . '/../reutilizable/menu.php';

require_once __DIR__ . '/../reutilizable/footer.php';
?>
