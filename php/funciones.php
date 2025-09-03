<?php
// funciones.php: Funciones reutilizables para el sistema Gym

// Función para conectar a la base de datos
function conectar_db() {
    $host = 'localhost';
    $db = 'registrogym';
    $user = 'root';
    $pass = '';
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die('Error de conexión: ' . $conn->connect_error);
    }
    return $conn;
}

// Función para desconectar la base de datos
function desconectar_db($conn) {
    if ($conn) {
        $conn->close();
    }
}

// Configuración de notificaciones
$admin_email = 'benjaminpereina@gmail.com';
$admin_sms = '3815849276@sms.claro.com.ar'; // Email-to-SMS para Claro

// Función para enviar notificación al admin
function notificar_admin($usuario, $ip) {
    global $admin_email, $admin_sms;
    $asunto = 'Alerta: Usuario/IP bloqueado en Gym';
    $mensaje = "Se ha bloqueado el acceso por demasiados intentos fallidos.\nUsuario: $usuario\nIP: $ip\nFecha: " . date('Y-m-d H:i:s');
    @mail($admin_email, $asunto, $mensaje);
    @mail($admin_sms, '', $mensaje); // SMS sin asunto
}
