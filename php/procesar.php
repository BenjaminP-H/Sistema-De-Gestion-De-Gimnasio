<?php
// procesar.php: Verifica usuario y contraseña en la base de datos
session_start();

// Configuración de la base de datos
$host = 'localhost';
$db = 'registrogym';
$user = 'root';
$pass = '';

// Conexión
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

// Obtener datos del formulario
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

// Consulta para verificar usuario
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verificar contraseña
    if (password_verify($password, $row['password'])) {
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['rol'] = $row['rol'];
        header('Location: panel.php');
        exit;
    } else {
        echo '<div class="alert alert-danger">Contraseña incorrecta.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Usuario no encontrado.</div>';
}
$stmt->close();
$conn->close();
?>
