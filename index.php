<?php
require_once 'php/header.php';
require_once 'php/session.php';
require_once 'php/funciones.php';
?>

<section class="login-page">

    <article class="login-card">

        <h2>Iniciar sesión</h2>

        <?php verificarLogueo(); ?>

        <form action="php/procesar.php" method="POST" novalidate>

            <!-- USUARIO -->
            <section class="mb-3">
                <label class="form-label">Usuario</label>

                <section class="input-group login-input">
                    <span class="input-group-text">
                        <i class="bi bi-person-fill"></i>
                    </span>

                    <input
                        type="text"
                        class="form-control"
                        name="usuario"
                        placeholder="Ingresá tu usuario"
                        required
                    >
                </section>
            </section>

            <!-- CONTRASEÑA -->
            <section class="mb-3">
                <label class="form-label">Contraseña</label>

                <section class="input-group login-input">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>

                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        placeholder="Ingresá tu contraseña"
                        required
                    >
                </section>
            </section>

            <button type="submit" class="btn btn-warning w-100 login-btn">
                Ingresar
            </button>
        </form>

    </article>

</section>

<?php
require_once 'php/footer.php';
?>