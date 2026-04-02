<?php
require_once __DIR__ . '/reutilizable/session_admin.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

$titulo_pagina = 'Nuevo Gym';

// Recuperar datos del formulario si hubo error
$datos = $_SESSION['form_nuevo_gym'] ?? [];
unset($_SESSION['form_nuevo_gym']);

$error   = $_SESSION['error_nuevo_gym'] ?? '';
$exito   = $_SESSION['exito_nuevo_gym'] ?? '';
unset($_SESSION['error_nuevo_gym'], $_SESSION['exito_nuevo_gym']);
?>
<?php require_once __DIR__ . '/reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/reutilizable/menu.php'; ?>

<div class="container py-4" style="max-width: 700px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="panel_inicio.php" class="btn btn-sm btn-outline-secondary">← Volver</a>
        <h4 class="mb-0 fw-bold">➕ Registrar nuevo gym</h4>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
        <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <form action="backend/guardar_gym.php" method="POST">

        <!-- DATOS DEL GYM -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-semibold">🏋️ Datos del Gym</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre del gym <span class="text-danger">*</span></label>
                        <input type="text" name="gym_nombre" class="form-control" required
                               value="<?= htmlspecialchars($datos['gym_nombre'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="gym_direccion" class="form-control"
                               value="<?= htmlspecialchars($datos['gym_direccion'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="gym_telefono" class="form-control"
                               value="<?= htmlspecialchars($datos['gym_telefono'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="gym_email" class="form-control"
                               value="<?= htmlspecialchars($datos['gym_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vencimiento suscripción</label>
                        <input type="date" name="gym_suscripcion_vence" class="form-control"
                               value="<?= htmlspecialchars($datos['gym_suscripcion_vence'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- DATOS DEL USUARIO ADMIN_GYM -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-semibold">👤 Usuario administrador del gym</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="user_nombre" class="form-control" required
                               value="<?= htmlspecialchars($datos['user_nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre de usuario <span class="text-danger">*</span></label>
                        <input type="text" name="user_usuario" class="form-control" required
                               value="<?= htmlspecialchars($datos['user_usuario'] ?? '') ?>">
                        <div class="form-text">Con este nombre entrará al sistema.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="user_email" class="form-control"
                               value="<?= htmlspecialchars($datos['user_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="user_password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Repetir contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="user_password2" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Registrar gym y usuario</button>
    </form>

</div>

<?php require_once __DIR__ . '/reutilizable/footer.php'; ?>
