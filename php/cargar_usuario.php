<?php
require_once 'session.php';
require_once 'funciones.php';

verificarSesion();

/* ==============================
   FUNCIONES AUX
============================== */
function redirect_with_message($url, $message, $success = true) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $success ? 'success' : 'danger';
    header("Location: $url");
    exit;
}

/* ==============================
   DATOS DEL FORM
============================== */
$nombre       = trim($_POST['nombre'] ?? '');
$apellido     = trim($_POST['apellido'] ?? '');
$dni          = trim($_POST['dni'] ?? '');
$telefono     = trim($_POST['telefono'] ?? '');
$plan_nombre  = trim($_POST['plan'] ?? '');
$modo_pago    = trim($_POST['metodo_pago'] ?? '');
$dias_pagados = (int)($_POST['dias'] ?? 0);
$monto        = (float)($_POST['monto'] ?? 0);
$foto_temp    = $_POST['foto_temp'] ?? null; // 👈 FOTO TEMPORAL DEL FORM
$foto_file    = $_FILES['foto'] ?? null;

/* ==============================
   VALIDACIÓN
============================== */
if (!$nombre || !$apellido || !$dni || !$plan_nombre || $dias_pagados <= 0 || $monto <= 0) {
    redirect_with_message('registro.php', 'Datos obligatorios incompletos', false);
}

/* ==============================
   FOTO FINAL
============================== */
$foto_path = 'sinfoto.webp'; // FOTO POR DEFECTO
$carpeta_clientes = '../img/clientes/';

// Si viene foto temporal de confirmar_registro
if ($foto_temp) {
    $ruta_temp = __DIR__ . '/../img/temporal/' . basename($foto_temp);

    if (file_exists($ruta_temp)) {
        if (!is_dir($carpeta_clientes)) {
            mkdir($carpeta_clientes, 0777, true);
        }

        $ext = pathinfo($foto_temp, PATHINFO_EXTENSION);
        $foto_path = uniqid('cliente_') . '.' . $ext;
        $ruta_final = $carpeta_clientes . $foto_path;

        if (!rename($ruta_temp, $ruta_final)) {
            redirect_with_message('registro.php', 'Error al mover la foto a clientes', false);
        }
    }
}
// Si no, si subieron una foto directamente (opcional)
elseif ($foto_file && $foto_file['error'] !== UPLOAD_ERR_NO_FILE) {
    $ext = strtolower(pathinfo($foto_file['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $permitidas)) {
        redirect_with_message('registro.php', 'Formato de imagen no válido', false);
    }

    $foto_path = uniqid('cliente_') . '.' . $ext;

    if (!move_uploaded_file($foto_file['tmp_name'], $carpeta_clientes . $foto_path)) {
        redirect_with_message('registro.php', 'Error al subir la imagen', false);
    }
}

/* ==============================
   BD
============================== */
try {
    $pdo = conectar_db();
    $pdo->beginTransaction();

    // 🔹 Obtener ID del plan
    $id_plan = obtenerPlanID($pdo, $plan_nombre);
    if ($id_plan === 0) {
        throw new Exception("El plan seleccionado no existe.");
    }

    // 🔹 Insertar cliente
    $stmt = $pdo->prepare("
        INSERT INTO clientes 
        (nombres, apellidos, dni, telefono, foto_carnet, fecha_registro)
        VALUES 
        (:n, :a, :dni, :tel, :foto, CURDATE())
    ");
    $stmt->execute([
        ':n'    => $nombre,
        ':a'    => $apellido,
        ':dni'  => $dni,
        ':tel'  => $telefono,
        ':foto' => $foto_path
    ]);

    $id_cliente = $pdo->lastInsertId();

    // 🔹 Insertar pago inicial
    $stmt = $pdo->prepare("
        INSERT INTO pagos
        (id_cliente, id_plan, fecha_pago, dias_pagados, monto, modo_pago, estado)
        VALUES
        (:cliente, :plan, CURDATE(), :dias, :monto, :modo, 'Pagado')
    ");
    $stmt->execute([
        ':cliente' => $id_cliente,
        ':plan'    => $id_plan,
        ':dias'    => $dias_pagados,
        ':monto'   => $monto,
        ':modo'    => $modo_pago
    ]);

    $pdo->commit();
    redirect_with_message('inicio.php', 'Cliente registrado correctamente');

} catch (Exception $e) {
    $pdo->rollBack();
    redirect_with_message('registro.php', 'Error: ' . $e->getMessage(), false);
}