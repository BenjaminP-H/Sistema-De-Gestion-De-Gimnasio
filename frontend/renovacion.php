<?php
require_once __DIR__ . '/../reutilizable/session.php';
require_once __DIR__ . '/../reutilizable/funciones.php';

verificarSesion();

$id_cliente = $_GET['id_cliente'] ?? null;
$gymId = $_SESSION['gym_id'] ?? null;

if (!$id_cliente || $gymId === null) {
    $_SESSION['flash_message'] = 'Cliente no valido.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: inicio.php');
    exit;
}

$pdo = conectar_db();
$stmt = $pdo->prepare("
    SELECT id
    FROM clientes
    WHERE id = ? AND gym_id = ?
    LIMIT 1
");
$stmt->execute([$id_cliente, $gymId]);
if (!$stmt->fetchColumn()) {
    $_SESSION['flash_message'] = 'Cliente no encontrado.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: inicio.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT gp.id AS gym_plan_id, pl.nombre
    FROM gym_planes gp
    JOIN planes pl ON gp.plan_id = pl.id
    WHERE gp.gym_id = :gym_id AND gp.activo = 1
    ORDER BY pl.nombre
");
$stmt->execute([':gym_id' => $gymId]);
$planes = $stmt->fetchAll();

$page_class = 'ga-renovacion-page';
$menu_activo = 'renovacion';
?>

<?php require_once __DIR__ . '/../reutilizable/header.php'; ?>
<?php require_once __DIR__ . '/../reutilizable/menu.php'; ?>

<main class="container mt-5" style="max-width: 500px;">

    <h2 class="mb-4 text-center">Renovar membresia</h2>

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
    <form action="backend/confirmar_renovacion.php" method="POST">

        <!-- ID CLIENTE -->
        <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($id_cliente) ?>">

        <!-- PLAN -->
        <div class="mb-3">
            <label class="form-label">Plan</label>
            <select name="gym_plan_id" class="form-select" required>
                <option value="">Seleccionar plan</option>
                <?php foreach ($planes as $plan): ?>
                    <option value="<?= (int)$plan['gym_plan_id'] ?>">
                        <?= htmlspecialchars($plan['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- DIAS -->
        <div class="mb-3">
            <label class="form-label">Dias a pagar</label>
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

        <!-- METODO DE PAGO -->
        <div class="mb-3">
            <label class="form-label">Metodo de pago</label>
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

            <a href="frontend/inicio.php" class="btn btn-secondary">
                Cancelar
            </a>
        </div>

    </form>

</main>

<?php require_once __DIR__ . '/../reutilizable/footer.php'; ?>
