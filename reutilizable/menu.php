<?php
$menu_activo = $menu_activo ?? '';
$mostrar_filtros = $mostrar_filtros ?? false;
$filtro_estado = $_GET['f_estado'] ?? '';
$filtro_plan = $_GET['f_plan'] ?? '';
?>
<nav class="navbar navbar-expand-xl navbar-dark inicio-navbar ga-navbar">
    <section class="container-fluid ga-navbar-shell">
        <header class="navbar-brand ga-brand">
            <a href="frontend/inicio.php" class="ga-brand-link">
                <span class="ga-brand-title">Gimnasio</span>
                <span class="ga-brand-sub">Panel operativo</span>
            </a>
        </header>

        <button class="navbar-toggler ga-navbar-toggle" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuNavbar"
            aria-controls="menuNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <section class="collapse navbar-collapse" id="menuNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 inicio-menu ga-nav">
                <li class="nav-item">
                    <a class="nav-link ga-nav-link<?= $menu_activo === 'inicio' ? ' active' : '' ?>" href="frontend/inicio.php">
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ga-nav-link<?= $menu_activo === 'registro' ? ' active' : '' ?>" href="frontend/registro.php">
                        Registrar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link ga-nav-link<?= $menu_activo === 'renovacion' ? ' active' : '' ?>" href="frontend/buscar_cliente.php">
                        Renovar
                    </a>
                </li>
            </ul>

            <?php if ($mostrar_filtros): ?>
                <form method="get" class="d-flex filtros-navbar inicio-filtros ga-nav-filters">
                    <span class="ga-filter-label">Filtros</span>
                    <select name="f_estado" class="form-select form-select-sm">
                        <option value="">Estado</option>
                        <option value="Activo" <?= $filtro_estado === 'Activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $filtro_estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>

                    <select name="f_plan" class="form-select form-select-sm">
                        <option value="">Plan</option>
                        <option value="Aparatos" <?= $filtro_plan === 'Aparatos' ? 'selected' : '' ?>>Aparatos</option>
                        <option value="Funcional" <?= $filtro_plan === 'Funcional' ? 'selected' : '' ?>>Funcional</option>
                        <option value="Zumba" <?= $filtro_plan === 'Zumba' ? 'selected' : '' ?>>Zumba</option>
                        <option value="Pase Libre" <?= $filtro_plan === 'Pase Libre' ? 'selected' : '' ?>>Pase Libre</option>
                        <option value="Dia" <?= $filtro_plan === 'Dia' ? 'selected' : '' ?>>Dia</option>
                    </select>

                    <button class="btn btn-warning btn-sm ga-filter-btn">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </section>
</nav>
