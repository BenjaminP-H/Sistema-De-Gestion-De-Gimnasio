<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();

$cliente = null;
$pago = null;
$error = null;
$estado_real = 'Inactivo';
$fecha_vencimiento = null;
$gymId = $_SESSION['gym_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni'] ?? '');

    if ($dni === '') {
        $error = 'Ingresa un DNI valido.';
    } else {
        try {
            if ($gymId === null) {
                throw new Exception('Gym invalido.');
            }
            $pdo = conectar_db();

            // CLIENTE
            $stmt = $pdo->prepare("
                SELECT id, nombre, apellido, dni, telefono, fecha_alta
                FROM clientes
                WHERE dni = :dni AND gym_id = :gym_id
                LIMIT 1
            ");
            $stmt->execute(['dni' => $dni, 'gym_id' => $gymId]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                // ULTIMO PAGO
                $stmt = $pdo->prepare("
                    SELECT 
                        fecha_pago,
                        fecha_vencimiento,
                        pl.nombre AS nombre_plan
                    FROM pagos p
                    JOIN gym_planes gp ON p.gym_plan_id = gp.id
                    JOIN planes pl ON gp.plan_id = pl.id
                    WHERE p.cliente_id = :id_cliente AND p.gym_id = :gym_id
                    ORDER BY fecha_pago DESC
                    LIMIT 1
                ");
                $stmt->execute([
                    'id_cliente' => $cliente['id'],
                    'gym_id' => $gymId
                ]);
                $pago = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($pago) {
                    $fecha_vencimiento = $pago['fecha_vencimiento'];

                    if ($fecha_vencimiento && $fecha_vencimiento >= date('Y-m-d')) {
                        $estado_real = 'Activo';
                    }
                }
            } else {
                $error = 'Cliente no encontrado.';
            }

        } catch (Throwable $e) {
            $error = 'Error al buscar el cliente.';
        }
    }
}

$menu_activo = 'renovacion';
$page_class = 'ga-search-page';
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/../reutilizable/menu.php'; ?>

<main class="ga-search-main">

    <section class="ga-hero">
        <section class="container">
            <section class="row align-items-center gy-4">
                <section class="col-lg-6">
                    <section class="ga-hero-copy">
                        <span class="ga-kicker">Renovaciones y membresias</span>
                        <h1 class="ga-hero-title">Encontra clientes en segundos</h1>
                        <p class="ga-hero-sub">
                            Busca por DNI y obtene al instante estado, plan vigente y fecha de vencimiento.
                            Todo listo para continuar con la renovacion.
                        </p>
                    </section>
                </section>

                <section class="col-lg-6">
                    <aside class="ga-search-panel">
                        <section class="ga-panel-header">
                            <h3>Buscar por DNI</h3>
                            <p>Ingresa el documento para ver la ficha completa del cliente.</p>
                        </section>

                        <form method="POST" class="ga-search-form" data-ga-search-form>
                            <label for="dni" class="ga-label">DNI del cliente</label>
                            <section class="ga-input-wrap">
                                <i class="bi bi-person-vcard"></i>
                                <input
                                    type="text"
                                    id="dni"
                                    name="dni"
                                    class="ga-input"
                                    placeholder="Ej: 30123456"
                                    required
                                    autocomplete="off"
                                    inputmode="numeric"
                                    data-ga-dni
                                >
                            </section>

                            <section class="ga-search-actions">
                                <button class="btn ga-btn-primary" data-ga-submit>
                                    Buscar cliente
                                </button>
                                <a href="frontend/inicio.php" class="btn ga-btn-ghost">
                                    Cancelar
                                </a>
                            </section>
                        </form>

                        <?php if ($error): ?>
                            <section class="ga-alert ga-alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </section>
                        <?php endif; ?>

                        <p class="ga-panel-hint">Tip: si el cliente no aparece, revisa que el DNI este cargado sin puntos.</p>
                    </aside>
                </section>
            </section>
        </section>
    </section>

    <section class="container ga-results">
        <?php if ($cliente): ?>
            <article class="ga-result-card">
                <header class="ga-result-header">
                    <section>
                        <p class="ga-result-eyebrow">Resultado</p>
                        <h3>Cliente encontrado</h3>
                    </section>
                    <?php if ($pago): ?>
                        <span class="ga-status <?= $estado_real === 'Activo' ? 'ga-status-ok' : 'ga-status-warn' ?>">
                            <?= $estado_real ?>
                        </span>
                    <?php else: ?>
                        <span class="ga-status ga-status-muted">Sin pagos</span>
                    <?php endif; ?>
                </header>

                <section class="ga-result-body">
                    <section class="ga-result-grid">
                        <section class="ga-result-media">
                            <img src="img/clientes/sinfoto.webp" alt="Foto cliente">
                            <section class="ga-result-meta">
                                <span>Registrado</span>
                                <strong><?= htmlspecialchars($cliente['fecha_alta']) ?></strong>
                                <?php if ($pago): ?>
                                    <span>Ultimo pago</span>
                                    <strong><?= htmlspecialchars($pago['fecha_pago']) ?></strong>
                                <?php endif; ?>
                            </section>
                        </section>

                        <section class="ga-result-info">
                            <h4><?= htmlspecialchars($cliente['nombre'].' '.$cliente['apellido']) ?></h4>

                            <section class="ga-info-grid">
                                <section>
                                    <span>DNI</span>
                                    <strong><?= htmlspecialchars($cliente['dni']) ?></strong>
                                </section>
                                <section>
                                    <span>Telefono</span>
                                    <strong><?= htmlspecialchars($cliente['telefono'] ?: '-') ?></strong>
                                </section>
                                <section>
                                    <span>Plan</span>
                                    <strong><?= htmlspecialchars($pago['nombre_plan'] ?? 'Sin pagos registrados') ?></strong>
                                </section>
                                <section>
                                    <span>Vencimiento</span>
                                    <strong><?= htmlspecialchars($fecha_vencimiento ?: '-') ?></strong>
                                </section>
                            </section>
                        </section>
                    </section>

                    <section class="ga-result-actions">
                        <a href="frontend/renovacion.php?id_cliente=<?= $cliente['id'] ?>" class="btn ga-btn-primary">
                            Renovar membresia
                        </a>
                    </section>
                </section>
            </article>
        <?php endif; ?>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ga-search-form]');
    if (!form) return;

    const input = form.querySelector('[data-ga-dni]');
    const button = form.querySelector('[data-ga-submit]');

    if (input) {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/[^0-9]/g, '');
        });
    }

    form.addEventListener('submit', () => {
        if (!button) return;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Buscando...';
    });
});
</script>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>


