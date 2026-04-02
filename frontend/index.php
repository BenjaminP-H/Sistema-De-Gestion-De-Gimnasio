<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

$page_class = 'ga-login-page';
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>

<main class="ga-login-main">
    <section class="ga-login-hero">
        <section class="container">
            <section class="row justify-content-center">
                <section class="col-lg-5 col-md-7">
                    <article class="ga-login-panel">
                        <header class="ga-panel-header text-center">
                            <span class="ga-kicker">Acceso seguro</span>
                            <h3>Iniciar sesion</h3>
                            <p>Ingresa tus credenciales para continuar.</p>
                        </header>

                        <?php verificarLogueo(); ?>

                        <form action="backend/procesar.php" method="POST" novalidate data-ga-login-form>
                            <section class="ga-login-field">
                                <label class="ga-label">Usuario</label>
                                <section class="ga-input-wrap">
                                    <i class="bi bi-person-fill"></i>
                                    <input
                                        type="text"
                                        class="ga-input"
                                        name="usuario"
                                        placeholder="Ingresa tu usuario"
                                        required
                                        autocomplete="off"
                                    >
                                </section>
                            </section>

                            <section class="ga-login-field">
                                <label class="ga-label">Contrasena</label>
                                <section class="ga-input-wrap">
                                    <i class="bi bi-lock-fill"></i>
                                    <input
                                        type="password"
                                        class="ga-input"
                                        name="password"
                                        placeholder="Ingresa tu contrasena"
                                        required
                                    >
                                </section>
                            </section>

                            <button type="submit" class="btn ga-btn-primary w-100" data-ga-login-btn>
                                Ingresar
                            </button>
                        </form>

                        <p class="ga-login-helper">Si olvidaste tus credenciales, contacta al administrador.</p>
                    </article>
                </section>
            </section>
        </section>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ga-login-form]');
    if (!form) return;

    const button = form.querySelector('[data-ga-login-btn]');

    form.addEventListener('submit', () => {
        if (!button) return;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Ingresando...';
    });
});
</script>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>


