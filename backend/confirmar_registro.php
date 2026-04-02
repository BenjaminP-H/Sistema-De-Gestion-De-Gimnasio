<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$directorio_temp = __DIR__ . '/../img/temporal/';
$tiempo_maximo   = 3600; // 1 hora en segundos

if (is_dir($directorio_temp)) {
    foreach (glob($directorio_temp . '*') as $archivo) {
        if (is_file($archivo) && (time() - filemtime($archivo)) > $tiempo_maximo) {
            unlink($archivo); // borra archivos viejos
        }
    }
}
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/registro.php');
    exit;
}

/* ===============================
   DATOS
=============================== */
$nombre       = $_POST['nombre'] ?? null;
$apellido     = $_POST['apellido'] ?? null;
$dni          = $_POST['dni'] ?? null;
$telefono     = $_POST['telefono'] ?? null;
$gym_plan_id  = $_POST['plan'] ?? null;
$dias         = $_POST['dias'] ?? null;
$monto        = $_POST['monto'] ?? null;
$metodo_pago  = $_POST['metodo_pago'] ?? null;

if (!$nombre || !$apellido || !$dni || !$gym_plan_id || !$dias || !$monto || !$metodo_pago) {
    die('Datos incompletos');
}

if (!is_numeric($dias) || (int)$dias <= 0) {
    die('Dias invalidos');
}

if (!is_numeric($monto) || (float)$monto <= 0) {
    die('Monto invalido');
}

$gymId = $_SESSION['gym_id'] ?? null;
if ($gymId === null) {
    die('Gym invalido');
}

$pdo = conectar_db();
$stmt = $pdo->prepare("
    SELECT pl.nombre
    FROM gym_planes gp
    JOIN planes pl ON gp.plan_id = pl.id
    WHERE gp.id = :gym_plan_id AND gp.gym_id = :gym_id AND gp.activo = 1
    LIMIT 1
");
$stmt->execute([
    ':gym_plan_id' => $gym_plan_id,
    ':gym_id' => $gymId
]);
$nombre_plan = $stmt->fetchColumn();

if (!$nombre_plan) {
    die('Plan no encontrado');
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
$vencimiento = calcularNuevaFechaVencimiento(null, (int)$dias);

$page_class = 'ga-confirm-page';
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>
<script>window.scrollTo(0, 0);</script>

<main class="ga-confirm-main">
    <section class="ga-confirm-hero">
        <section class="container">
            <span class="ga-kicker">Confirmacion</span>
            <h1>Revisa antes de guardar</h1>
            <p>
                Este es el ultimo paso del alta. Verifica los datos personales y de pago
                antes de confirmar el registro.
            </p>
            <section class="ga-hero-tags">
                <span class="ga-chip">Plan: <?= htmlspecialchars($nombre_plan) ?></span>
                <span class="ga-chip">Dias: <?= (int)$dias ?></span>
                <span class="ga-chip">Monto: $<?= number_format($monto, 0, ',', '.') ?></span>
                <span class="ga-chip">Metodo: <?= htmlspecialchars($metodo_pago) ?></span>
            </section>
        </section>
    </section>

    <section class="container ga-confirm-shell">
        <article class="ga-confirm-card ga-card-animada">
            <section class="ga-confirm-grid">
                <section class="ga-confirm-media">
                    <?php if ($foto_temp): ?>
                        <img src="img/temporal/<?= $foto_temp ?>" alt="Foto cliente">
                    <?php else: ?>
                        <section class="ga-confirm-empty">
                            <span>Sin foto</span>
                        </section>
                    <?php endif; ?>
                </section>

                <section class="ga-confirm-info">
                    <h2><?= htmlspecialchars("$nombre $apellido") ?></h2>

                    <section class="ga-info-block">
                        <section>
                            <span>DNI</span>
                            <strong><?= htmlspecialchars($dni) ?></strong>
                        </section>
                        <section>
                            <span>Telefono</span>
                            <strong><?= htmlspecialchars($telefono ?: '-') ?></strong>
                        </section>
                    </section>

                    <section class="ga-summary-grid">
                        <section>
                            <span>Plan</span>
                            <strong><?= htmlspecialchars($nombre_plan) ?></strong>
                        </section>
                        <section>
                            <span>Dias</span>
                            <strong><?= (int)$dias ?></strong>
                        </section>
                        <section>
                            <span>Monto</span>
                            <strong>$<?= number_format($monto, 0, ',', '.') ?></strong>
                        </section>
                        <section>
                            <span>Metodo</span>
                            <strong><?= htmlspecialchars($metodo_pago) ?></strong>
                        </section>
                    </section>

                    <section class="ga-vencimiento">
                        Vence el <strong><?= $vencimiento ?></strong>
                    </section>
                </section>
            </section>

            <section class="ga-confirm-actions">
                <a href="backend/cancelar_registro.php?foto=<?= $foto_temp ?>" class="btn ga-btn-ghost">
                    Cancelar
                </a>

                <form action="backend/cargar_usuario.php" method="POST" data-ga-confirm-form>
                    <input type="hidden" name="foto_temp" value="<?= $foto_temp ?>">
                    <input type="hidden" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
                    <input type="hidden" name="apellido" value="<?= htmlspecialchars($apellido) ?>">
                    <input type="hidden" name="dni" value="<?= htmlspecialchars($dni) ?>">
                    <input type="hidden" name="telefono" value="<?= htmlspecialchars($telefono) ?>">
                    <input type="hidden" name="plan" value="<?= (int)$gym_plan_id ?>">
                    <input type="hidden" name="dias" value="<?= $dias ?>">
                    <input type="hidden" name="monto" value="<?= $monto ?>">
                    <input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago) ?>">

                    <button class="btn ga-btn-primary" data-ga-confirm-btn>
                        Confirmar registro
                    </button>
                </form>
            </section>
        </article>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ga-confirm-form]');
    if (!form) return;

    const button = form.querySelector('[data-ga-confirm-btn]');

    form.addEventListener('submit', () => {
        if (!button) return;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Confirmando...';
    });
});
</script>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>


