<?php
    require_once 'php/header.php';
?>
    <div class="container mt-5">
        <h2 class="mb-4">Iniciar sesión</h2>
        <form action="php/procesar.php" method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </form>
    </div>
<?php
    require_once 'php/footer.php';
?> 