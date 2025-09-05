<?php
// procesar.php: Verifica usuario y contraseña en la base de datos y aplica seguridad

session_start(); // Inicia la sesión para manejar variables de usuario y seguridad

require_once __DIR__ . '/funciones.php'; // Incluye funciones reutilizables

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
if (!isset($_SESSION['intentos'][$usuario])) {                     //VERIFICAR LUEGOOOOO!!!!!
    $_SESSION['intentos'][$usuario] = 0;
}
// Si no existe el contador para la IP, lo inicializa en 0
if (!isset($_SESSION['intentos'][$ip])) {
    $_SESSION['intentos'][$ip] = 0;
}

// Si el usuario o la IP están bloqueados por superar los intentos
// Ahora, en vez de mostrar el mensaje aquí, lo guardamos en la sesión y redirigimos a index.php
if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
    // Guarda el mensaje de bloqueo en la sesión
    $_SESSION['error_login'] = 'Acceso bloqueado por demasiados intentos fallidos. Vuelve a intentar en 30 minutos.';
    // Notifica al admin por correo y SMS
    notificar_admin($usuario, $ip);
    // Redirige al formulario principal para mostrar el mensaje
    header('Location: ../index.php');
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
        // Guarda mensaje de error en la sesión para mostrarlo en index.php
        $_SESSION['error_login'] = 'Usuario o contraseña incorrectos.';
        // Si se alcanzan los 5 intentos, bloquea y notifica
        if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
            $_SESSION['error_login'] = 'Acceso bloqueado por demasiados intentos fallidos. Vuelve a intentar en 30 minutos.';
            notificar_admin($usuario, $ip);
        }
        // Redirige al formulario principal para mostrar el mensaje
        header('Location: ../index.php');
        exit;
    }
} else {
    // Si el usuario no existe, incrementa los contadores de intentos
    $_SESSION['intentos'][$usuario]++;
    $_SESSION['intentos'][$ip]++;
    // Guarda mensaje de error en la sesión para mostrarlo en index.php
    $_SESSION['error_login'] = 'Usuario o contraseña incorrectos.';
    // Si se alcanzan los 5 intentos, bloquea y notifica
    if ($_SESSION['intentos'][$usuario] >= 5 || $_SESSION['intentos'][$ip] >= 5) {
        $_SESSION['error_login'] = 'Acceso bloqueado por demasiados intentos fallidos. Vuelve a intentar en 30 minutos.';
        notificar_admin($usuario, $ip);
    }
    // Redirige al formulario principal para mostrar el mensaje
    header('Location: ../index.php');
    exit;
}

$stmt->close(); // Cierra la consulta preparada
desconectar_db($conn); // Cierra la conexión usando función reutilizable
?>
