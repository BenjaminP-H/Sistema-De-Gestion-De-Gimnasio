<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand fw-bold" href="panel_inicio.php">
        <span class="ga-admin-nav-icon" aria-hidden="true">
            <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M9.605.172a.5.5 0 0 0-.477.148l-.87.87a5.535 5.535 0 0 0-1.316 0l-.87-.87a.5.5 0 0 0-.477-.148l-1.495.25a.5.5 0 0 0-.37.258l-.602 1.075a5.55 5.55 0 0 0-.932.932L.108 3.762a.5.5 0 0 0-.258.37l-.25 1.495a.5.5 0 0 0 .148.477l.87.87a5.535 5.535 0 0 0 0 1.316l-.87.87a.5.5 0 0 0-.148.477l.25 1.495a.5.5 0 0 0 .258.37l1.075.602c.27.37.58.7.932.932l.602 1.075a.5.5 0 0 0 .37.258l1.495.25a.5.5 0 0 0 .477-.148l.87-.87a5.535 5.535 0 0 0 1.316 0l.87.87a.5.5 0 0 0 .477.148l1.495-.25a.5.5 0 0 0 .37-.258l.602-1.075a5.55 5.55 0 0 0 .932-.932l1.075-.602a.5.5 0 0 0 .258-.37l.25-1.495a.5.5 0 0 0-.148-.477l-.87-.87a5.535 5.535 0 0 0 0-1.316l.87-.87a.5.5 0 0 0 .148-.477l-.25-1.495a.5.5 0 0 0-.258-.37l-1.075-.602a5.55 5.55 0 0 0-.932-.932L12.21.68a.5.5 0 0 0-.37-.258L10.345.172a.5.5 0 0 0-.74.457l.87.87a.5.5 0 0 0 .13.146zm-1.605 4.328a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7z"/></svg>
        </span>
        GymAdmin
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuAdmin">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuAdmin">
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link" href="panel_inicio.php">
                    <span class="ga-admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 2 7.5V14a.5.5 0 0 0 .5.5H6a.5.5 0 0 0 .5-.5V10h3v4a.5.5 0 0 0 .5.5h3.5a.5.5 0 0 0 .5-.5V7.5a.5.5 0 0 0-.146-.354l-6-6z"/></svg>
                    </span>
                    Inicio
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="nuevo_gym.php">
                    <span class="ga-admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M8 1a.5.5 0 0 1 .5.5V7h5.5a.5.5 0 0 1 0 1H8.5v5.5a.5.5 0 0 1-1 0V8H2a.5.5 0 0 1 0-1h5.5V1.5A.5.5 0 0 1 8 1z"/></svg>
                    </span>
                    Nuevo Gym
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cobros.php">
                    <span class="ga-admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 2h16v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6zm3 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H3z"/></svg>
                    </span>
                    Cobros
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            <?php if (isset($_SESSION['impersonando_gym_id'])): ?>
                <li class="nav-item me-2">
                    <span class="badge bg-warning text-dark">
                        <span class="ga-admin-nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z"/><path d="M8 5a3 3 0 1 1 0 6A3 3 0 0 1 8 5z"/></svg>
                        </span>
                        Viendo: <?= htmlspecialchars($_SESSION['impersonando_gym_nombre']) ?>
                    </span>
                </li>
                <li class="nav-item me-3">
                    <a class="btn btn-sm btn-outline-warning" href="backend/salir_gym.php">← Volver a mi panel</a>
                </li>
            <?php endif; ?>

            <li class="nav-item me-3">
                <span class="text-light small">
                    <span class="ga-admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 16 16" class="ga-admin-icon"><path d="M8 8a3 3 0 1 0-2.999-3A3 3 0 0 0 8 8zm0 1c-2.67 0-5 1.34-5 3v1h10v-1c0-1.66-2.33-3-5-3z"/></svg>
                    </span>
                    <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                </span>
            </li>
            <li class="nav-item">
                <a class="btn btn-sm btn-outline-danger" href="logout.php">Salir</a>
            </li>
        </ul>
    </div>
</nav>
