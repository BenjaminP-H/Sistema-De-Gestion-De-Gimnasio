<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$directorio_temp = __DIR__ . '/img/temporal/';
$tiempo_maximo   = 3600; // 1 hora en segundos

if (is_dir($directorio_temp)) {
    foreach (glob($directorio_temp . '*') as $archivo) {
        if (is_file($archivo) && (time() - filemtime($archivo)) > $tiempo_maximo) {
            unlink($archivo); // borra archivos viejos
        }
    }
}
require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

verificarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit;
}

/* ===============================
   DATOS
=============================== */
$nombre       = $_POST['nombre'] ?? null;
$apellido     = $_POST['apellido'] ?? null;
$dni          = $_POST['dni'] ?? null;
$telefono     = $_POST['telefono'] ?? null;
$plan         = $_POST['plan'] ?? null;
$dias         = $_POST['dias'] ?? null;
$monto        = $_POST['monto'] ?? null;
$metodo_pago  = $_POST['metodo_pago'] ?? null;

if (!$nombre || !$apellido || !$dni || !$plan || !$dias || !$monto || !$metodo_pago) {
    die('Datos incompletos');
}

/* ===============================
   FOTO TEMPORAL
=============================== */
$foto_temp = null;

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

    $directorio = __DIR__ . '/../img/temporal/';

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $permitidas)) {
        die('Formato de imagen no permitido');
    }

    $foto_temp = uniqid('tmp_') . '.' . $ext;
    $ruta_temp = $directorio . $foto_temp;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_temp)) {
        die('Error al subir la imagen');
    }
}

/* ===============================
   VENCIMIENTO VISUAL
=============================== */
$hoy = new DateTime();
$hoy->modify("+$dias days");
$vencimiento = $hoy->format('Y-m-d');
?>

<section class="confirmacion-page">

    <article class="confirmacion-card card-animada">

        <header class="confirmacion-header">
            Confirmar registro de cliente
        </header>

        <section class="confirmacion-body">

            <section class="confirmacion-foto">
                <?php if ($foto_temp): ?>
                    <img src="img/temporal/<?= $foto_temp ?>" alt="Foto cliente">
                <?php else: ?>
                    <span class="text-muted">Sin foto</span>
                <?php endif; ?>
            </section>

            <section class="confirmacion-info">
                <h4><?= htmlspecialchars("$nombre $apellido") ?></h4>

                <p><strong>DNI:</strong> <?= htmlspecialchars($dni) ?></p>
                <p><strong>Tel:</strong> <?= htmlspecialchars($telefono ?: '-') ?></p>

                <hr>

                <p><strong>Plan:</strong> <?= htmlspecialchars($plan) ?></p>
                <p><strong>Días:</strong> <?= $dias ?></p>
                <p><strong>Monto:</strong> $<?= number_format($monto, 0, ',', '.') ?></p>
                <p><strong>Método:</strong> <?= htmlspecialchars($metodo_pago) ?></p>

                <p class="vencimiento">
                    Vence el <strong><?= $vencimiento ?></strong>
                </p>
            </section>

        </section>

        <footer class="confirmacion-footer">

            <a href="php/cancelar_registro.php?foto=<?= $foto_temp ?>" class="btn btn-outline-danger">
                ❌ Cancelar
            </a>

            <form action="php/cargar_usuario.php" method="POST">
                <input type="hidden" name="foto_temp" value="<?= $foto_temp ?>">
                <input type="hidden" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
                <input type="hidden" name="apellido" value="<?= htmlspecialchars($apellido) ?>">
                <input type="hidden" name="dni" value="<?= htmlspecialchars($dni) ?>">
                <input type="hidden" name="telefono" value="<?= htmlspecialchars($telefono) ?>">
                <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">
                <input type="hidden" name="dias" value="<?= $dias ?>">
                <input type="hidden" name="monto" value="<?= $monto ?>">
                <input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago) ?>">

                <button class="btn btn-success">
                    ✅ Confirmar registro
                </button>
            </form>

        </footer>

    </article>

</section>

<?php require_once 'footer.php'; ?>