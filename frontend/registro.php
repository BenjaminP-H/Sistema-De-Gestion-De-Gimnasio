<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();

// Traer planes desde la BD (por gym)
$pdo = conectar_db();
$planes = [];
$gymId = $_SESSION['gym_id'] ?? null;

if ($gymId !== null) {
    $stmt = $pdo->prepare("
        SELECT gp.id AS gym_plan_id, pl.nombre
        FROM gym_planes gp
        JOIN planes pl ON gp.plan_id = pl.id
        WHERE gp.gym_id = :gym_id AND gp.activo = 1
        ORDER BY pl.nombre
    ");
    $stmt->execute([':gym_id' => $gymId]);
    $planes = $stmt->fetchAll();
}

$menu_activo = 'registro';
$page_class = 'ga-register-page';
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/../reutilizable/menu.php'; ?>

<main class="ga-register-main">
    <section class="ga-register-hero">
        <section class="container">
            <section class="row justify-content-center">
                <section class="col-lg-7 col-md-10">
                    <article class="ga-register-panel">
                        <header class="ga-panel-header text-center">
                            <span class="ga-kicker">Alta de clientes</span>
                            <h3>Registro de nuevo cliente</h3>
                            <p>Completa los datos y avanza a la confirmacion.</p>
                        </header>

                        <?php if (!empty($_SESSION['flash_message'])): ?>
                            <section class="ga-alert ga-alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
                                <?= $_SESSION['flash_message'] ?>
                                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                            </section>
                        <?php endif; ?>

                        <form action="backend/confirmar_registro.php" method="POST" enctype="multipart/form-data" data-ga-register-form>
                            <section class="ga-form-section">
                                <section class="ga-section-title">
                                    <h5>Datos personales</h5>
                                    <span class="ga-section-chip">Paso 1</span>
                                </section>

                                <section class="row g-3">
                                    <article class="col-md-6">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" class="form-control" required autocomplete="off">
                                    </article>

                                    <article class="col-md-6">
                                        <label class="form-label">Apellido</label>
                                        <input type="text" name="apellido" class="form-control" required autocomplete="off">
                                    </article>

                                    <article class="col-md-6">
                                        <label class="form-label">DNI</label>
                                        <input type="text" name="dni" class="form-control" required inputmode="numeric" data-ga-dni>
                                    </article>

                                    <article class="col-md-6">
                                        <label class="form-label">Telefono</label>
                                        <input type="text" name="telefono" class="form-control" inputmode="numeric" data-ga-telefono>
                                    </article>

                                    <article class="col-12">
                                        <label class="form-label">Foto carnet (opcional)</label>
                                        <input type="file" name="foto" class="form-control" accept="image/*">
                                    </article>
                                </section>
                            </section>

                            <section class="ga-form-section">
                                <section class="ga-section-title">
                                    <h5>Datos de membresia</h5>
                                    <span class="ga-section-chip">Paso 2</span>
                                </section>

                                <section class="row g-3">
                                    <article class="col-md-4">
                                        <label class="form-label">Monto</label>
                                        <input type="number" name="monto" class="form-control" min="0" required>
                                    </article>

                                    <article class="col-md-4">
                                        <label class="form-label">Dias que paga</label>
                                        <input type="number" name="dias" class="form-control" min="1" max="31" required>
                                        <small class="text-muted">Maximo 31 dias</small>
                                    </article>

                                    <article class="col-md-4">
                                        <label class="form-label">Metodo de pago</label>
                                        <select name="metodo_pago" class="form-select" required>
                                            <option value="Efectivo">Efectivo</option>
                                            <option value="Transferencia">Transferencia</option>
                                        </select>
                                    </article>

                                    <article class="col-md-6">
                                        <label class="form-label">Tipo de plan</label>
                                        <select name="plan" class="form-select" required>
                                            <option value="">Seleccionar plan</option>
                                            <?php foreach ($planes as $plan): ?>
                                                <option value="<?= (int)$plan['gym_plan_id'] ?>">
                                                    <?= htmlspecialchars($plan['nombre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </article>
                                </section>

                                <p class="ga-register-hint">La fecha de vencimiento se calcula al confirmar.</p>
                            </section>

                            <section class="ga-form-actions">
                                <button type="submit" class="btn ga-btn-primary" data-ga-register-btn>
                                    Continuar a confirmacion
                                </button>
                                <a href="frontend/inicio.php" class="btn ga-btn-ghost">
                                    Cancelar
                                </a>
                            </section>
                        </form>
                    </article>
                </section>
            </section>
        </section>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ga-register-form]');
    if (!form) return;

    const dniInput = form.querySelector('[data-ga-dni]');
    const phoneInput = form.querySelector('[data-ga-telefono]');
    const button = form.querySelector('[data-ga-register-btn]');

    if (dniInput) {
        dniInput.addEventListener('input', () => {
            dniInput.value = dniInput.value.replace(/[^0-9]/g, '');
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '');
        });
    }

    form.addEventListener('submit', () => {
        if (!button) return;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enviando...';
    });
});
</script>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>


