<?php
// registro.php: Formulario de registro de nuevos usuarios
require_once '../php/header.php';
session_start(); // Para manejar mensajes flash
?>
<div class="container mt-5">
    <h2 class="mb-4">Registro de nuevo usuario</h2>

    <!-- Mostrar mensaje flash si existe -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?>">
            <?php 
                echo $_SESSION['flash_message']; 
                unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <form action="cargar_usuario.php" method="POST" enctype="multipart/form-data">
        <!-- Datos personales -->
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombres</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>

        <div class="mb-3">
            <label for="apellido" class="form-label">Apellidos</label>
            <input type="text" class="form-control" id="apellido" name="apellido" required>
        </div>

        <div class="mb-3">
            <label for="dni" class="form-label">DNI</label>
            <input type="text" class="form-control" id="dni" name="dni" required>
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono">
        </div>

        <div class="mb-3">
            <label for="foto" class="form-label">Foto carnet (opcional)</label>
            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
        </div>

        <hr>

        <!-- Datos de pago -->
        <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" class="form-control" id="monto" name="monto" required>
        </div>

        <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de pago</label>
            <select class="form-control" id="metodo_pago" name="metodo_pago" required>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="dias" class="form-label">Días que paga</label>
            <input type="number" class="form-control" id="dias" name="dias" required>
        </div>

        <div class="mb-3">
            <label for="plan" class="form-label">Tipo de plan</label>
            <select class="form-control" id="plan" name="plan" required>
                <option value="aparatos">Aparatos</option>
                <option value="funcional">Funcional</option>
                <option value="zumba">Zumba</option>
                <option value="otro">Otro</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="fecha_pago" class="form-label">Fecha de pago</label>
            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" required>
        </div>

        <button type="submit" class="btn btn-success">Registrar usuario</button>
    </form>
</div>
<?php
require_once '../php/footer.php';
?>
