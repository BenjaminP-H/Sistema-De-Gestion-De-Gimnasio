<?php
// inicio.php: Página de inicio
require_once 'php/header.php';
?>

<div class="container">
    <nav class="navbar navbar-expand-lg navbar-light bg-warning">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Drago Gym</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="registro.php">Registro</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="renovacion.php">Renovación</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<?php
require_once 'php/footer.php';
?>