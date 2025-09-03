<?php
// procesar.php: Verifica usuario y contraseña en la base de datos y aplica seguridad

session_start(); // Inicia la sesión para manejar variables de usuario y seguridad

require_once __DIR__ . '/funciones.php'; // Incluye funciones reutilizables, como la notificación al admin

// Conexión a la base de datos usando función reutilizable
$conn = conectar_db();

// Obtener datos enviados desde el formulario de login
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';
$ip = $_SERVER['REMOTE_ADDR']; // IP del usuario que intenta acceder

// --- Seguridad: Limitar intentos de login por usuario e IP ---
// Si no existe el array de intentos en la sesión, lo crea
if (!isset($_SESSION['intentos'])) {
    $_SESSION['intentos'] = [];
}
// Si no existe el contador para el usuario, lo inicializa en 0
if (!isset($_SESSION['intentos'][$usuario])) {
    $_SESSION['intentos'][$usuario] = 0;
}
// Si no existe el contador para la IP, lo inicializa en 0
if (!isset($_SESSION['intentos'][$ip])) {
    $_SESSION['intentos'][$ip] = 0;
}

// Si el usuario o la IP están bloqueados por superar los intentos
if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
    // Muestra mensaje de bloqueo y notifica al admin
    echo '<div class="alert alert-danger">Acceso bloqueado por demasiados intentos fallidos. Contacta al administrador.</div>';
    notificar_admin($usuario, $ip); // Envía correo y SMS al admin
    exit;
}

// Consulta SQL para verificar si el usuario existe en la base de datos
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql); // Prepara la consulta para evitar inyección SQL
$stmt->bind_param('s', $usuario); // Asocia el parámetro usuario
$stmt->execute(); // Ejecuta la consulta
$result = $stmt->get_result(); // Obtiene el resultado

if ($row = $result->fetch_assoc()) {
    // Si el usuario existe, verifica la contraseña
    if (password_verify($password, $row['password'])) {
        // Si la contraseña es correcta, reinicia los contadores de intentos
        $_SESSION['intentos'][$usuario] = 0;
        $_SESSION['intentos'][$ip] = 0;
        // Guarda datos del usuario en la sesión
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['rol'] = $row['rol'];
        session_regenerate_id(true); // Regenera el ID de sesión por seguridad
        // Redirige al panel principal
        header('Location: panel.php');
        exit;
    } else {
        // Si la contraseña es incorrecta, incrementa los contadores de intentos
        $_SESSION['intentos'][$usuario]++;
        $_SESSION['intentos'][$ip]++;
        // Muestra mensaje genérico de error
        echo '<div class="alert alert-danger">Credenciales incorrectas.</div>';
        // Si se alcanzan los 5 intentos, notifica al admin
        if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
            notificar_admin($usuario, $ip);
        }
    }
} else {
    // Si el usuario no existe, incrementa los contadores de intentos
    $_SESSION['intentos'][$usuario]++;
    $_SESSION['intentos'][$ip]++;
    // Muestra mensaje genérico de error
    echo '<div class="alert alert-danger">Credenciales incorrectas.</div>';
    // Si se alcanzan los 5 intentos, notifica al admin
    if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
        notificar_admin($usuario, $ip);
    }
}

$stmt->close(); // Cierra la consulta preparada
desconectar_db($conn); // Cierra la conexión usando función reutilizable
?>
