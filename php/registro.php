<?php
require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

verificarSesion();

// Traer planes desde la BD
$pdo = conectar_db();
$planes = $pdo->query("SELECT nombre_plan FROM planes ORDER BY nombre_plan")->fetchAll();
?>

<main class="container my-5 registrar-page">

    <article class="registrar-card">

        <header class="mb-4 text-center">
            <h2 class="text-warning fw-bold">Registro de nuevo usuario</h2>
        </header>

        <!-- Mensaje flash -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <section class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
                <?= $_SESSION['flash_message'] ?>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </section>
        <?php endif; ?>

        <form action="php/confirmar_registro.php" method="POST" enctype="multipart/form-data">

            <!-- =========================
                 DATOS PERSONALES
            ========================= -->
            <section class="mb-4">
                <h5 class="text-dark mb-3">Datos personales</h5>

                <section class="row g-3">

                    <article class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </article>

                    <article class="col-md-6">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required>
                    </article>

                    <article class="col-md-6">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" required>
                    </article>

                    <article class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </article>

                    <article class="col-12">
                        <label class="form-label">Foto carnet (opcional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </article>

                </section>
            </section>

            <hr>

            <!-- =========================
                 DATOS MEMBRESÍA
            ========================= -->
            <section class="mb-4">
                <h5 class="text-dark mb-3">Datos de la membresía</h5>

                <section class="row g-3">

                    <article class="col-md-4">
                        <label class="form-label">Monto</label>
                        <input type="number" name="monto" class="form-control" min="0" required>
                    </article>

                    <article class="col-md-4">
                        <label class="form-label">Días que paga</label>
                        <input type="number" name="dias" class="form-control" min="1" max="31"required>
                        <small class="text-muted">
                            Máximo 31 días
                        </small>
                    </article>

                    <article class="col-md-4">
                        <label class="form-label">Método de pago</label>
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
                                <option value="<?= htmlspecialchars($plan['nombre_plan']) ?>">
                                    <?= htmlspecialchars($plan['nombre_plan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </article>

                </section>
            </section>

            <!-- BOTÓN -->
            <section class="text-center">
                <button type="submit" class="btn btn-success px-5">
                    Registrar usuario
                </button>
            </section>

        </form>

    </article>

</main>

<?php require_once 'footer.php'; ?>