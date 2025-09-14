<?php
session_start();
require_once '../php/funciones.php';

// ================================
// 1️⃣ CONSTANTES Y FUNCIONES AUXILIARES
// ================================
//Esto sirve para restringir el tamaño  de la fotos y tipos 
define('MAX_FILE_SIZE', 2 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['jpg','jpeg','png','gif']);
define('ALLOWED_MIME_TYPES', ['image/jpeg','image/png','image/gif']);

// Función para validar foto
function validate_file($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $size = $file['size'];

    if (in_array($ext, ALLOWED_EXTENSIONS) && $size <= MAX_FILE_SIZE) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        return in_array($mime, ALLOWED_MIME_TYPES);
    }
    return false;
}

// Función para redireccionar con mensaje
function redirect_with_message($url, $message, $success = true) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_success'] = $success;
    header("Location: $url");
    exit;
}

// ================================
// 2️⃣ OBTENER Y VALIDAR DATOS DEL FORMULARIO
// ================================
$nombre       = trim($_POST['nombre'] ?? '');
$apellido     = trim($_POST['apellido'] ?? '');
$dni          = trim($_POST['dni'] ?? '');
$telefono     = trim($_POST['telefono'] ?? '');
$metodo_pago  = trim($_POST['metodo_pago'] ?? '');
$dias_pagados = (int) ($_POST['dias'] ?? 0);
$plan         = trim($_POST['plan'] ?? '');
$fecha_pago   = $_POST['fecha_pago'] ?? null;
$monto        = (float) ($_POST['monto'] ?? 0);
$foto         = $_FILES['foto'] ?? null;

// Campos obligatorios
if (empty($nombre) || empty($apellido) || empty($dni) || $monto <= 0) {
    redirect_with_message('registro.php', 'Por favor complete los campos obligatorios correctamente.', false);
}

// ================================
// 3️⃣ PROCESAR FOTO (OPCIONAL)
// ================================
$foto_path = null;
if ($foto && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
    if (validate_file($foto)) {
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        $foto_name = uniqid() . '.' . $ext;
        $foto_path = 'uploads/' . $foto_name;
        move_uploaded_file($foto['tmp_name'], $foto_path);
    } else {
        redirect_with_message('registro.php', 'La foto no es válida o es demasiado grande.', false);
    }
}

// ================================
// 4️⃣ GUARDAR DATOS EN LA BASE DE DATOS (PDO)
// ================================
try {
    $pdo = conectar_db();

    $sql = "INSERT INTO clientes
            (nombres, apellidos, dni, telefono, fecha_registro, metodo_pago, dias_pagados, plan, fecha_pago, monto, foto)
            VALUES
            (:nombre, :apellido, :dni, :telefono, NOW(), :modo_pago, :dias_pagados, :plan, :fecha_pago, :monto, :foto)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre'       => $nombre,
        ':apellido'     => $apellido,
        ':dni'          => $dni,
        ':telefono'     => $telefono,
        ':metodo_pago'  => $metodo_pago,
        ':dias_pagados' => $dias_pagados,
        ':plan'         => $plan,
        ':fecha_pago'   => $fecha_pago,
        ':monto'        => $monto,
        ':foto'         => $foto_path
    ]);

    redirect_with_message('inicio.php', '✅ Datos cargados correctamente');

} catch (PDOException $e) {
    redirect_with_message('registro.php', '❌ Error al guardar los datos: ' . $e->getMessage(), false);
}
?>
