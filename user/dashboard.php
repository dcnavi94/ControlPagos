<?php
session_start();
// Verifica que el usuario esté autenticado y sea alumno
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'alumno') {
    header("Location: ../login/login.php");
    exit;
}

require_once '../db_connection.php';

$id_usuario = $_SESSION['id_usuario'];

// Consulta los pagos asignados a este alumno
$sqlPagos = "SELECT * FROM pagos WHERE id_usuario = $id_usuario ORDER BY fecha_vencimiento DESC";
$resultPagos = mysqli_query($conn, $sqlPagos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Alumno</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="styles.css">
  <style>
    html, body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .container {
        flex: 1;
    }

    .footer {
        background-color: #0d6efd;
        color: white;
        text-align: center;
        padding: 10px 0;
        width: 100%;
        margin-top: auto;
    }
  </style>
</head>
<body>
<?php include 'menu.php'; ?>
  
  <div class="container mt-4">
    <h2 class="mb-4 text-center">Mis Pagos</h2>
    <table class="table table-bordered table-hover">
      <thead class="table-primary">
        <tr>
          <th>ID Pago</th>
          <th>Concepto</th>
          <th>Monto</th>
          <th>Fecha Vencimiento</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($resultPagos) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($resultPagos)): 
            $esAdeudo = ($row['estado'] == 'pendiente' || $row['estado'] == 'vencido');
          ?>
            <tr <?php if($esAdeudo) echo 'class="table-danger"'; ?>>
              <td><?php echo $row['id_pago']; ?></td>
              <td><?php echo htmlspecialchars($row['concepto']); ?></td>
              <td><?php echo $row['monto']; ?></td>
              <td><?php echo $row['fecha_vencimiento']; ?></td>
              <td><?php echo htmlspecialchars($row['estado']); ?></td>
              <td>
                <?php if($esAdeudo): ?>
                  <a href="pagar.php?id_pago=<?php echo $row['id_pago']; ?>" class="btn btn-success btn-sm">Pagar</a>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center">No tienes pagos asignados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php include 'chatwidget.php'; ?>

  <footer class="footer">
    <div class="container text-center">
      <p>© 2025 Ciencias Artes y Metaeducación San José. Todos los derechos reservados.</p>
    </div>
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>