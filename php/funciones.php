<?php
// ===============================
// FUNCIONES GENERALES - GYM
// ===============================

// Mostrar errores de login
function verificarLogueo() {
    if (isset($_SESSION['error_login'])) {
        echo '<div class="alert alert-danger">'.$_SESSION['error_login'].'</div>';
        unset($_SESSION['error_login']);
    }
}

// Conexión PDO
function conectar_db() {
    $host = 'localhost';
    $db   = 'registrogym';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("❌ Error al conectar a la base de datos: " . $e->getMessage());
    }
}

// Verificar sesión
function verificarSesion() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: ../index.php');
        exit;
    }
}

// ===============================
// NOTIFICACIONES (opcional)
// ===============================
$admin_email = 'benjaminpereina@gmail.com';
$admin_sms = '3815849276@sms.claro.com.ar';

function notificar_admin($usuario, $ip) {
    global $admin_email, $admin_sms;

    $asunto = 'Alerta de seguridad - Gym';
    $mensaje = "Acceso bloqueado\nUsuario: $usuario\nIP: $ip\nFecha: ".date('Y-m-d H:i:s');

    @mail($admin_email, $asunto, $mensaje);
    @mail($admin_sms, '', $mensaje);
}
/**
 * Obtiene el ID del plan a partir del nombre del plan
 * Ej: "Aparatos" → 1
 * Lanza excepción si no existe
 */
function obtenerPlanId(PDO $pdo, string $nombrePlan): int
{
    $stmt = $pdo->prepare("
        SELECT id_plan
        FROM planes
        WHERE LOWER(nombre_plan) = LOWER(:plan)
        LIMIT 1
    ");
    $stmt->execute([
        ':plan' => trim($nombrePlan)
    ]);

    $id_plan = $stmt->fetchColumn();

    if (!$id_plan) {
        throw new Exception("El plan seleccionado no existe.");
    }

    return (int)$id_plan;
}
// ===============================
// FUNCION DE FECHA DE VENCIMIENTO MAS DIAS
// ===============================
function calcularNuevaFechaVencimiento($fecha_vencimiento_actual, $dias_nuevos) {
    $hoy = date('Y-m-d');

    // Si tiene vencimiento y aún no venció → sumar desde ahí
    if ($fecha_vencimiento_actual && $fecha_vencimiento_actual >= $hoy) {
        return date(
            'Y-m-d',
            strtotime($fecha_vencimiento_actual . " +$dias_nuevos days")
        );
    }

    // Si está vencido o no tiene pago → sumar desde hoy
    return date(
        'Y-m-d',
        strtotime($hoy . " +$dias_nuevos days")
    );
}