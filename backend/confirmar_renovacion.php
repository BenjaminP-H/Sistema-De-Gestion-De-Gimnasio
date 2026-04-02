<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/inicio.php');
    exit;
}

$pdo = conectar_db();

$id_cliente  = $_POST['id_cliente'] ?? null;
$gym_plan_id = $_POST['gym_plan_id'] ?? null;
$dias        = $_POST['dias'] ?? null;
$monto       = $_POST['monto'] ?? null;
$metodo_pago = $_POST['metodo_pago'] ?? null;

if (!$id_cliente || !$gym_plan_id || !$dias || !$monto || !$metodo_pago) {
    die('Datos incompletos');
}

$gymId = $_SESSION['gym_id'] ?? null;
if ($gymId === null) {
    die('Gym invalido');
}

/* ===============================
   CLIENTE
=============================== */
$stmt = $pdo->prepare("
    SELECT id, nombre, apellido, dni, telefono
    FROM clientes
    WHERE id = ? AND gym_id = ?
    LIMIT 1
");
$stmt->execute([$id_cliente, $gymId]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die('Cliente no encontrado');
}

/* ===============================
   PLAN
=============================== */
$stmt = $pdo->prepare("
    SELECT pl.nombre
    FROM gym_planes gp
    JOIN planes pl ON gp.plan_id = pl.id
    WHERE gp.id = ? AND gp.gym_id = ? AND gp.activo = 1
    LIMIT 1
");
$stmt->execute([$gym_plan_id, $gymId]);
$nombre_plan = $stmt->fetchColumn();

if (!$nombre_plan) {
    die('Plan no encontrado');
}

/* ===============================
   ULTIMO PAGO
=============================== */
$stmt = $pdo->prepare("
    SELECT fecha_vencimiento
    FROM pagos
    WHERE cliente_id = ? AND gym_id = ?
    ORDER BY fecha_pago DESC
    LIMIT 1
");
$stmt->execute([$id_cliente, $gymId]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===============================
   NUEVO VENCIMIENTO (VISUAL)
=============================== */
$fecha_actual = $pago['fecha_vencimiento'] ?? null;
$hoy = date('Y-m-d');

if ($fecha_actual && $fecha_actual >= $hoy) {
    $estado = 'Activo';
    $badge = 'ga-status-ok';
} elseif ($fecha_actual) {
    $estado = 'Vencido';
    $badge = 'ga-status-warn';
} else {
    $estado = 'Sin pagos';
    $badge = 'ga-status-muted';
}

$nuevo_vencimiento = calcularNuevaFechaVencimiento($fecha_actual, (int)$dias);

$page_class = 'ga-confirm-page';
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>
<script>window.scrollTo(0, 0);</script>

<main class="ga-confirm-main">
    <section class="ga-confirm-hero">
        <section class="container">
            <span class="ga-kicker">Confirmacion</span>
            <h1>Renovacion de membresia</h1>
            <p>
                Revisa el plan seleccionado, el monto y la nueva fecha de vencimiento
                antes de confirmar el pago.
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
                    <img src="img/clientes/sinfoto.webp" alt="Foto cliente">
                </section>

                <section class="ga-confirm-info">
                    <section class="ga-confirm-top">
                        <h2><?= htmlspecialchars($cliente['nombre'].' '.$cliente['apellido']) ?></h2>
                        <span class="ga-status <?= $badge ?>">
                            <?= $estado ?>
                        </span>
                    </section>

                    <section class="ga-info-block">
                        <section>
                            <span>DNI</span>
                            <strong><?= htmlspecialchars($cliente['dni']) ?></strong>
                        </section>
                        <section>
                            <span>Telefono</span>
                            <strong><?= htmlspecialchars($cliente['telefono'] ?: '-') ?></strong>
                        </section>
                    </section>

                    <section class="ga-summary-grid">
                        <section>
                            <span>Plan</span>
                            <strong><?= htmlspecialchars($nombre_plan) ?></strong>
                        </section>
                        <section>
                            <span>Dias a agregar</span>
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
                        Nuevo vencimiento: <strong><?= $nuevo_vencimiento ?></strong>
                    </section>
                </section>
            </section>

            <section class="ga-confirm-actions">
                <a href="frontend/renovacion.php?id_cliente=<?= $id_cliente ?>" class="btn ga-btn-ghost">
                    Cancelar
                </a>

                <form action="backend/procesar_renovacion.php" method="POST" data-ga-confirm-form>
                    <input type="hidden" name="id_cliente" value="<?= $id_cliente ?>">
                    <input type="hidden" name="gym_plan_id" value="<?= (int)$gym_plan_id ?>">
                    <input type="hidden" name="dias" value="<?= $dias ?>">
                    <input type="hidden" name="monto" value="<?= $monto ?>">
                    <input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago) ?>">

                    <button type="submit" class="btn ga-btn-primary" data-ga-confirm-btn>
                        Confirmar renovacion
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


