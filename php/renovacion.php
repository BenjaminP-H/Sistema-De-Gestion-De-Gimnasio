<?php
require_once 'header.php';
require_once 'session.php';
require_once 'funciones.php';

verificarSesion();

$id_cliente = $_GET['id_cliente'] ?? null;

if (!$id_cliente) {
    $_SESSION['flash_message'] = 'Cliente no válido.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: inicio.php');
    exit;
}
?>

<main class="container mt-5" style="max-width: 500px;">

    <h2 class="mb-4 text-center">Renovar membresía</h2>

    <!-- MENSAJE FLASH -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?> text-center">
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <form action="php/confirmar_renovacion.php" method="POST">

        <!-- ID CLIENTE -->
        <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente) ?>">

        <!-- PLAN -->
        <div class="mb-3">
            <label class="form-label">Plan</label>
            <select name="id_plan" class="form-select" required>
                <option value="">Seleccionar plan</option>
                <option value="1">Pase libre</option>
                <option value="2">Zumba</option>
                <option value="3">Día</option>
                <option value="4">Funcional</option>
                <option value="5">Aparatos</option>
            </select>
        </div>

        <!-- DÍAS -->
        <div class="mb-3">
            <label class="form-label">Días a pagar</label>
            <input
                type="number"
                name="dias"
                class="form-control"
                min="1"
                required
            >
        </div>

        <!-- MONTO -->
        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input
                type="number"
                name="monto"
                class="form-control"
                min="0"
                step="0.01"
                required
            >
        </div>

        <!-- MÉTODO DE PAGO -->
        <div class="mb-3">
            <label class="form-label">Método de pago</label>
            <select name="metodo_pago" class="form-select" required>
                <option value="">Seleccionar</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Transferencia">Transferencia</option>
            </select>
        </div>

        <!-- BOTONES -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success">
                Continuar
            </button>

            <a href="php/inicio.php" class="btn btn-secondary">
                Cancelar
            </a>
        </div>

    </form>

</main>

<?php require_once 'footer.php'; ?>