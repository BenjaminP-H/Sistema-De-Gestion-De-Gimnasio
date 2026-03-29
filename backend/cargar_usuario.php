<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

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
$gym_plan_id  = (int)($_POST['plan'] ?? 0);
$modo_pago    = trim($_POST['metodo_pago'] ?? '');
$dias_pagados = (int)($_POST['dias'] ?? 0);
$monto        = (float)($_POST['monto'] ?? 0);
$foto_temp    = $_POST['foto_temp'] ?? null; // 👈 FOTO TEMPORAL DEL FORM
$foto_file    = $_FILES['foto'] ?? null;

/* ==============================
   VALIDACIÓN
============================== */
if (!$nombre || !$apellido || !$dni || !$gym_plan_id || $dias_pagados <= 0 || $monto <= 0) {
    redirect_with_message('../frontend/registro.php', 'Datos obligatorios incompletos', false);
}

$gymId = $_SESSION['gym_id'] ?? null;
if ($gymId === null) {
    redirect_with_message('../frontend/registro.php', 'Gym inválido', false);
}

/* ==============================
   FOTO FINAL
============================== */
$foto_path = 'sinfoto.webp'; // FOTO POR DEFECTO
$carpeta_clientes = __DIR__ . '/../img/clientes/';

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
            redirect_with_message('../frontend/registro.php', 'Error al mover la foto a clientes', false);
        }
    }
}
// Si no, si subieron una foto directamente (opcional)
elseif ($foto_file && $foto_file['error'] !== UPLOAD_ERR_NO_FILE) {
    $ext = strtolower(pathinfo($foto_file['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg','jpeg','png','webp'];

    if (!in_array($ext, $permitidas)) {
        redirect_with_message('../frontend/registro.php', 'Formato de imagen no válido', false);
    }

    $foto_path = uniqid('cliente_') . '.' . $ext;

    if (!move_uploaded_file($foto_file['tmp_name'], $carpeta_clientes . $foto_path)) {
        redirect_with_message('../frontend/registro.php', 'Error al subir la imagen', false);
    }
}

/* ==============================
   BD
============================== */
try {
    $pdo = conectar_db();
    $pdo->beginTransaction();

    // 🔹 Verificar plan del gym
    $stmt = $pdo->prepare("
        SELECT id
        FROM gym_planes
        WHERE id = :gym_plan_id AND gym_id = :gym_id AND activo = 1
        LIMIT 1
    ");
    $stmt->execute([
        ':gym_plan_id' => $gym_plan_id,
        ':gym_id' => $gymId
    ]);
    if (!$stmt->fetchColumn()) {
        throw new Exception("El plan seleccionado no existe.");
    }

    // 🔹 Insertar cliente
    $stmt = $pdo->prepare("
        INSERT INTO clientes 
        (gym_id, nombre, apellido, dni, telefono, fecha_alta)
        VALUES 
        (:gym_id, :n, :a, :dni, :tel, CURDATE())
    ");
    $stmt->execute([
        ':gym_id' => $gymId,
        ':n'    => $nombre,
        ':a'    => $apellido,
        ':dni'  => $dni,
        ':tel'  => $telefono
    ]);

    $id_cliente = $pdo->lastInsertId();

    $fecha_vencimiento = calcularNuevaFechaVencimiento(null, $dias_pagados);

    // 🔹 Insertar pago inicial
    $stmt = $pdo->prepare("
        INSERT INTO pagos
        (gym_id, cliente_id, gym_plan_id, monto, fecha_pago, fecha_vencimiento)
        VALUES
        (:gym_id, :cliente, :gym_plan_id, :monto, CURDATE(), :fecha_vencimiento)
    ");
    $stmt->execute([
        ':gym_id' => $gymId,
        ':cliente' => $id_cliente,
        ':gym_plan_id' => $gym_plan_id,
        ':monto'   => $monto,
        ':fecha_vencimiento' => $fecha_vencimiento
    ]);

    $pdo->commit();
    redirect_with_message('../frontend/inicio.php', 'Cliente registrado correctamente');

} catch (Exception $e) {
    $pdo->rollBack();
    redirect_with_message('../frontend/registro.php', 'Error: ' . $e->getMessage(), false);
}

