<?php
// pagos_crud.php
session_start();
// Verifica que el usuario esté autenticado y sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../login/login.php");
    exit;
}
require_once '../db_connection.php';

$error_message   = "";
$success_message = "";

// ------------------------
// Procesar eliminación de pago
// ------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id_pago'])) {
    $id_pago = intval($_GET['id_pago']);
    $sqlDelete = "DELETE FROM pagos WHERE id_pago = $id_pago";
    if (mysqli_query($conn, $sqlDelete)) {
        $success_message = "Pago eliminado correctamente.";
    } else {
        $error_message = "Error al eliminar pago: " . mysqli_error($conn);
    }
    header("Location: pagos_crud.php");
    exit;
}

// ------------------------
// Procesar inserción de nuevo pago
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $id_usuario = intval($_POST['id_usuario']);
    $concepto = mysqli_real_escape_string($conn, $_POST['concepto']);
    $monto = floatval($_POST['monto']);
    $fecha_vencimiento = mysqli_real_escape_string($conn, $_POST['fecha_vencimiento']);
    $estado = mysqli_real_escape_string($conn, $_POST['estado']);
    
    $sqlInsert = "INSERT INTO pagos (id_usuario, concepto, monto, fecha_vencimiento, estado)
                  VALUES ($id_usuario, '$concepto', $monto, '$fecha_vencimiento', '$estado')";
    if (mysqli_query($conn, $sqlInsert)) {
        $success_message = "Pago insertado correctamente.";
    } else {
        $error_message = "Error al insertar pago: " . mysqli_error($conn);
    }
}

// ------------------------
// Procesar actualización de pago
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_pago = intval($_POST['id_pago']);
    $id_usuario = intval($_POST['id_usuario']);
    $concepto = mysqli_real_escape_string($conn, $_POST['concepto']);
    $monto = floatval($_POST['monto']);
    $fecha_vencimiento = mysqli_real_escape_string($conn, $_POST['fecha_vencimiento']);
    $estado = mysqli_real_escape_string($conn, $_POST['estado']);
    
    $sqlUpdate = "UPDATE pagos SET 
                  id_usuario = $id_usuario,
                  concepto = '$concepto',
                  monto = $monto,
                  fecha_vencimiento = '$fecha_vencimiento',
                  estado = '$estado'
                  WHERE id_pago = $id_pago";
    if (mysqli_query($conn, $sqlUpdate)) {
        $success_message = "Pago actualizado correctamente.";
    } else {
        $error_message = "Error al actualizar pago: " . mysqli_error($conn);
    }
}

// Consulta para listar todos los pagos
$sqlPagos = "SELECT p.*, u.nombre AS nombre_usuario, u.apellido AS apellido_usuario 
             FROM pagos p
             LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario";
$resultPagos = mysqli_query($conn, $sqlPagos);

// Consultar alumnos para llenar el select
$sqlUsuarios = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE rol = 'alumno'";
$resultUsuarios = mysqli_query($conn, $sqlUsuarios);

// Obtener datos del pago a editar si se está en modo edición
$pago = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id_pago'])) {
    $id_pago = intval($_GET['id_pago']);
    $sqlPago = "SELECT * FROM pagos WHERE id_pago = $id_pago";
    $resultPago = mysqli_query($conn, $sqlPago);
    if ($resultPago && mysqli_num_rows($resultPago) > 0) {
        $pago = mysqli_fetch_assoc($resultPago);
    } else {
        $error_message = "Pago no encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CRUD Pagos - Ciencias Artes y Metaeducación San José</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <!-- Incluir menú -->
  <?php include 'menu.php'; ?>

  <div class="container mt-4">
    <h2 class="mb-4">Administrar Pagos</h2>
    
    <!-- Mostrar mensajes de error o éxito -->
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <!-- Formulario para insertar o editar un pago -->
    <div class="card mb-4">
      <div class="card-header">
        <?php echo $pago ? 'Editar Pago' : 'Insertar Nuevo Pago'; ?>
      </div>
      <div class="card-body">
        <form action="pagos_crud.php<?php echo $pago ? '?action=edit&id_pago=' . $pago['id_pago'] : ''; ?>" method="POST">
          <?php if ($pago): ?>
            <input type="hidden" name="id_pago" value="<?php echo $pago['id_pago']; ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label for="id_usuario" class="form-label">Estudiante</label>
            <select name="id_usuario" class="form-select" required>
              <option value="">Seleccione un estudiante</option>
              <?php while ($rowUsuario = mysqli_fetch_assoc($resultUsuarios)): ?>
                <option value="<?php echo $rowUsuario['id_usuario']; ?>" <?php if ($pago && $rowUsuario['id_usuario'] == $pago['id_usuario']) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($rowUsuario['nombre'] . ' ' . $rowUsuario['apellido']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="concepto" class="form-label">Concepto</label>
            <input type="text" name="concepto" class="form-control" value="<?php echo $pago ? htmlspecialchars($pago['concepto']) : ''; ?>" required>
          </div>
          <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" value="<?php echo $pago ? htmlspecialchars($pago['monto']) : ''; ?>" required>
          </div>
          <div class="mb-3">
            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
            <input type="date" name="fecha_vencimiento" class="form-control" value="<?php echo $pago ? htmlspecialchars($pago['fecha_vencimiento']) : ''; ?>" required>
          </div>
          <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" class="form-select" required>
              <option value="pendiente" <?php if ($pago && $pago['estado'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
              <option value="pagado" <?php if ($pago && $pago['estado'] == 'pagado') echo 'selected'; ?>>Pagado</option>
              <option value="vencido" <?php if ($pago && $pago['estado'] == 'vencido') echo 'selected'; ?>>Vencido</option>
            </select>
          </div>
          <button type="submit" name="<?php echo $pago ? 'actualizar' : 'guardar'; ?>" class="btn btn-primary"><?php echo $pago ? 'Actualizar Pago' : 'Guardar Pago'; ?></button>
          <?php if ($pago): ?>
            <a href="pagos_crud.php" class="btn btn-secondary">Cancelar</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
    
    <!-- Lista de Pagos -->
    <div class="card">
      <div class="card-header">
        Lista de Pagos
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead class="table-primary">
            <tr>
              <th>ID</th>
              <th>Estudiante</th>
              <th>Concepto</th>
              <th>Monto</th>
              <th>Fecha de Vencimiento</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultPagos)): ?>
              <tr>
                <td><?php echo $row['id_pago']; ?></td>
                <td><?php echo htmlspecialchars($row['nombre_usuario'] . ' ' . $row['apellido_usuario']); ?></td>
                <td><?php echo htmlspecialchars($row['concepto']); ?></td>
                <td><?php echo $row['monto']; ?></td>
                <td><?php echo $row['fecha_vencimiento']; ?></td>
                <td><?php echo htmlspecialchars($row['estado']); ?></td>
                <td>
                  <a href="pagos_crud.php?action=edit&id_pago=<?php echo $row['id_pago']; ?>" class="btn btn-sm btn-warning">Editar</a>
                  <a href="pagos_crud.php?action=delete&id_pago=<?php echo $row['id_pago']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este pago?')">Eliminar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    
  </div>
  
  <!-- Incluir footer -->
  <?php include '../footer.php'; ?>
  
  <!-- Bootstrap JS Bundle (incluye Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Archivo JavaScript personalizado -->
  <script src="../js/script.js"></script>
</body>
</html>
