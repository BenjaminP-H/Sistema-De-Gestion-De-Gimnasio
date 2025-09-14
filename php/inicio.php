<?php
// inicio.php
require_once '../php/header.php';
require_once '../php/funciones.php'; // tu función conectar_db()

try {
    $pdo = conectar_db();

    $sql = "SELECT c.id_cliente, c.nombres, c.apellidos, c.dni, c.foto_carnet,
                   p.dias_pagados, p.fecha_pago, p.estado
            FROM clientes c
            LEFT JOIN pagos p ON c.id_cliente = p.id_cliente
            ORDER BY c.id_cliente DESC";
    $stmt = $pdo->query($sql);
} catch (PDOException $e) {
    die("Error al conectar o consultar: " . $e->getMessage());
}
?>
<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg" style="background-color: #ffc107;"> <!-- amarillo -->
  <div class="container-fluid">
    <a class="navbar-brand text-white fw-bold" href="inicio.php">🏋️ Gimnasio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link text-white" href="registro.php">Registro</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="renovar.php">Renovar</a>
        </li>
      </ul>
    </div>
  </div>
</nav>


<div class="container mt-4">
    <h2 class="mb-4">Registro de Clientes y Pagos</h2>
    <table class="table table-striped table-bordered text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>Foto</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>DNI</th>
                <th>Estado</th>
                <th>Días Pagados</th>
                <th>Fecha de Pago</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <!-- FOTO -->
                    <td>
                        <?php if (!empty($row['foto_carnet'])) { ?>
                            <img src="uploads/<?php echo $row['foto_carnet']; ?>" 
                                 alt="Foto" width="60" height="60" 
                                 class="rounded-circle border border-2 border-dark">
                        <?php } else { ?>
                            <span class="text-muted">Sin foto</span>
                        <?php } ?>
                    </td>

                    <!-- NOMBRE -->
                    <td><?php echo $row['nombres']; ?></td>

                    <!-- APELLIDO -->
                    <td><?php echo $row['apellidos']; ?></td>

                    <!-- DNI -->
                    <td><?php echo $row['dni']; ?></td>

                    <!-- ESTADO -->
                    <td>
                        <?php 
                        if (!empty($row['estado'])) {
                            switch ($row['estado']) {
                                case 'Pagado':
                                    echo "<span class='badge bg-success'>Pagado</span>";
                                    break;
                                case 'Vencido':
                                    echo "<span class='badge bg-danger'>Vencido</span>";
                                    break;
                                case 'Cancelado':
                                    echo "<span class='badge bg-secondary'>Cancelado</span>";
                                    break;
                                default:
                                    echo "<span class='badge bg-secondary'>Ninguno</span>";
                            }
                        } else {
                            echo "<span class='badge bg-secondary'>Ninguno</span>";
                        }
                        ?>
                    </td>

                    <!-- DIAS PAGADOS -->
                    <td>
                        <?php 
                        if (!empty($row['dias_pagados'])) {
                            echo "<span class='badge bg-info'>{$row['dias_pagados']}</span>";
                        } else {
                            echo "<span class='badge bg-secondary'>-</span>";
                        }
                        ?>
                    </td>

                    <!-- FECHA DE PAGO -->
                    <td>
                        <?php 
                        if (!empty($row['fecha_pago'])) {
                            echo "<span class='badge bg-warning text-dark'>{$row['fecha_pago']}</span>";
                        } else {
                            echo "<span class='badge bg-secondary'>-</span>";
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php require_once '../php/footer.php'; ?>
