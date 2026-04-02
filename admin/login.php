<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado como admin_general, ir directo al panel
if (isset($_SESSION['usuario']) && $_SESSION['rol'] === 'admin_general') {
    header('Location: panel_inicio.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — GymAdmin</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center min-vh-100">

    <div class="card shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4">

            <h4 class="text-center mb-1 fw-bold">Panel Administrador</h4>
            <p class="text-center text-muted mb-4 small">Acceso exclusivo</p>

            <?php if (isset($_SESSION['error_login_admin'])): ?>
                <div class="alert alert-danger py-2">
                    <?= $_SESSION['error_login_admin'] ?>
                </div>
                <?php unset($_SESSION['error_login_admin']); ?>
            <?php endif; ?>

            <form action="backend/procesar_login.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input
                        type="text"
                        class="form-control"
                        id="usuario"
                        name="usuario"
                        required
                        autocomplete="username"
                    >
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>

        </div>
    </div>

    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
