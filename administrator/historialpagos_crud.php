<?php
session_start();
// Verifica que el usuario esté autenticado y sea alumno
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../login/login.php");
    exit;
}
// historialpagos_crud.php
require_once '../db_connection.php';

$error_message   = "";
$success_message = "";

// ---------------------------------
// Eliminar historial (Delete)
// ---------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id_historial'])) {
    $id_historial = intval($_GET['id_historial']);
    $sqlDelete = "DELETE FROM historialpagos WHERE id_historial = $id_historial";
    if (mysqli_query($conn, $sqlDelete)) {
        $success_message = "Historial eliminado correctamente.";
    } else {
        $error_message = "Error al eliminar historial: " . mysqli_error($conn);
    }
    header("Location: historialpagos_crud.php");
    exit;
}

// ---------------------------------
// Editar historial (Update)
// ---------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id_historial'])) {
    $id_historial = intval($_GET['id_historial']);
    $sqlHist = "SELECT * FROM historialpagos WHERE id_historial = $id_historial";
    $resultHist = mysqli_query($conn, $sqlHist);
    if (!$resultHist || mysqli_num_rows($resultHist) == 0) {
        $error_message = "Historial no encontrado.";
    } else {
        $historial = mysqli_fetch_assoc($resultHist);
        // Procesar actualización cuando se envíe el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
            $id_pago = intval($_POST['id_pago']);
            $id_estudiante = intval($_POST['id_estudiante']);
            $fecha_transaccion = mysqli_real_escape_string($conn, $_POST['fecha_transaccion']);
            $metodo_pago = mysqli_real_escape_string($conn, $_POST['metodo_pago']);
            $monto_pago = floatval($_POST['monto_pago']);
            
            $sqlUpdate = "UPDATE historialpagos SET 
                            id_pago = $id_pago,
                            id_estudiante = $id_estudiante,
                            fecha_transaccion = '$fecha_transaccion',
                            metodo_pago = '$metodo_pago',
                            monto_pago = $monto_pago
                          WHERE id_historial = $id_historial";
            if (mysqli_query($conn, $sqlUpdate)) {
                $success_message = "Historial actualizado correctamente.";
                header("Location: historialpagos_crud.php");
                exit;
            } else {
                $error_message = "Error al actualizar historial: " . mysqli_error($conn);
            }
        }
        // Consultar pagos para el select
        $sqlPagos = "SELECT id_pago, concepto FROM pagos";
        $resultPagos = mysqli_query($conn, $sqlPagos);
        // Consultar alumnos para el select
        $sqlAlumnos = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE rol = 'alumno'";
        $resultAlumnos = mysqli_query($conn, $sqlAlumnos);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <title>Editar Historial de Pago - Ciencias Artes y Metaeducación San José</title>
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
          <link rel="stylesheet" href="styles.css">
        </head>
        <body>
          <?php include 'menu.php'; ?>
          <div class="container mt-4">
            <h2 class="mb-4">Editar Historial de Pago</h2>
            <?php if ($error_message): ?>
              <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="historialpagos_crud.php?action=edit&id_historial=<?php echo $id_historial; ?>" method="POST">
              <div class="mb-3">
                <label for="id_pago" class="form-label">Pago</label>
                <select name="id_pago" class="form-select" required>
                  <option value="">Seleccione un pago</option>
                  <?php while ($rowPago = mysqli_fetch_assoc($resultPagos)): ?>
                    <option value="<?php echo $rowPago['id_pago']; ?>" <?php if($rowPago['id_pago'] == $historial['id_pago']) echo 'selected'; ?>>
                      <?php echo "Pago #" . $rowPago['id_pago'] . " - " . htmlspecialchars($rowPago['concepto']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="id_estudiante" class="form-label">Estudiante</label>
                <select name="id_estudiante" class="form-select" required>
                  <option value="">Seleccione un estudiante</option>
                  <?php while ($rowAlumno = mysqli_fetch_assoc($resultAlumnos)): ?>
                    <option value="<?php echo $rowAlumno['id_usuario']; ?>" <?php if($rowAlumno['id_usuario'] == $historial['id_estudiante']) echo 'selected'; ?>>
                      <?php echo htmlspecialchars($rowAlumno['nombre'] . ' ' . $rowAlumno['apellido']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="fecha_transaccion" class="form-label">Fecha de Transacción</label>
                <input type="date" name="fecha_transaccion" class="form-control" value="<?php echo htmlspecialchars($historial['fecha_transaccion']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="metodo_pago" class="form-label">Método de Pago</label>
                <input type="text" name="metodo_pago" class="form-control" value="<?php echo htmlspecialchars($historial['metodo_pago']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="monto_pago" class="form-label">Monto del Pago</label>
                <input type="number" step="0.01" name="monto_pago" class="form-control" value="<?php echo htmlspecialchars($historial['monto_pago']); ?>" required>
              </div>
              <button type="submit" name="actualizar" class="btn btn-warning">Actualizar Historial</button>
              <a href="historialpagos_crud.php" class="btn btn-secondary">Cancelar</a>
            </form>
          </div>
          <?php include 'footer.php'; ?>
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
          <script src="script.js"></script>
        </body>
        </html>
        <?php
        exit;
    }
}

// ---------------------------------
// Procesar inserción de nuevo historial
// ---------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $id_pago = intval($_POST['id_pago']);
    $id_estudiante = intval($_POST['id_estudiante']);
    $fecha_transaccion = mysqli_real_escape_string($conn, $_POST['fecha_transaccion']);
    $metodo_pago = mysqli_real_escape_string($conn, $_POST['metodo_pago']);
    $monto_pago = floatval($_POST['monto_pago']);
    
    $sqlInsert = "INSERT INTO historialpagos (id_pago, id_estudiante, fecha_transaccion, metodo_pago, monto_pago)
                  VALUES ($id_pago, $id_estudiante, '$fecha_transaccion', '$metodo_pago', $monto_pago)";
    if (mysqli_query($conn, $sqlInsert)) {
        $success_message = "Historial insertado correctamente.";
    } else {
        $error_message = "Error al insertar historial: " . mysqli_error($conn);
    }
}

// Consulta para listar todos los registros de historial
$sqlList = "SELECT hp.*, 
                   p.concepto AS pago_concepto, 
                   u.nombre AS alumno_nombre, 
                   u.apellido AS alumno_apellido 
            FROM historialpagos hp
            LEFT JOIN pagos p ON hp.id_pago = p.id_pago
            LEFT JOIN usuarios u ON hp.id_estudiante = u.id_usuario";
$resultList = mysqli_query($conn, $sqlList);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Pagos - CRUD - Ciencias Artes y Metaeducación San José</title>
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
    <h2 class="mb-4">Administrar Historial de Pagos</h2>
    
    <!-- Mensajes -->
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <!-- Formulario para insertar nuevo historial -->
    <div class="card mb-4">
      <div class="card-header">Insertar Nuevo Historial</div>
      <div class="card-body">
        <form action="historialpagos_crud.php" method="POST">
          <div class="mb-3">
            <label for="id_pago" class="form-label">Pago</label>
            <select name="id_pago" class="form-select" required>
              <option value="">Seleccione un pago</option>
              <?php 
              $sqlPagosList = "SELECT id_pago, concepto FROM pagos";
              $resultPagosList = mysqli_query($conn, $sqlPagosList);
              while ($rowPago = mysqli_fetch_assoc($resultPagosList)): ?>
                <option value="<?php echo $rowPago['id_pago']; ?>">
                  <?php echo "Pago #" . $rowPago['id_pago'] . " - " . htmlspecialchars($rowPago['concepto']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="id_estudiante" class="form-label">Estudiante</label>
            <select name="id_estudiante" class="form-select" required>
              <option value="">Seleccione un estudiante</option>
              <?php 
              $sqlAlumnosList = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE rol = 'alumno'";
              $resultAlumnosList = mysqli_query($conn, $sqlAlumnosList);
              while ($rowAlumno = mysqli_fetch_assoc($resultAlumnosList)): ?>
                <option value="<?php echo $rowAlumno['id_usuario']; ?>">
                  <?php echo htmlspecialchars($rowAlumno['nombre'] . ' ' . $rowAlumno['apellido']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="fecha_transaccion" class="form-label">Fecha de Transacción</label>
            <input type="date" name="fecha_transaccion" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="metodo_pago" class="form-label">Método de Pago</label>
            <input type="text" name="metodo_pago" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="monto_pago" class="form-label">Monto del Pago</label>
            <input type="number" step="0.01" name="monto_pago" class="form-control" required>
          </div>
          <button type="submit" name="guardar" class="btn btn-primary">Guardar Historial</button>
        </form>
      </div>
    </div>
    
    <!-- Lista de registros de historial -->
    <div class="card">
      <div class="card-header">Lista de Historial de Pagos</div>
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead class="table-primary">
            <tr>
              <th>ID Historial</th>
              <th>Pago</th>
              <th>Estudiante</th>
              <th>Fecha Transacción</th>
              <th>Método de Pago</th>
              <th>Monto</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultList)): ?>
              <tr>
                <td><?php echo $row['id_historial']; ?></td>
                <td><?php echo "Pago #" . $row['id_pago'] . " - " . htmlspecialchars($row['pago_concepto']); ?></td>
                <td><?php echo htmlspecialchars($row['alumno_nombre'] . ' ' . $row['alumno_apellido']); ?></td>
                <td><?php echo $row['fecha_transaccion']; ?></td>
                <td><?php echo htmlspecialchars($row['metodo_pago']); ?></td>
                <td><?php echo $row['monto_pago']; ?></td>
                <td>
                  <a href="historialpagos_crud.php?action=edit&id_historial=<?php echo $row['id_historial']; ?>" class="btn btn-sm btn-warning">Editar</a>
                  <a href="historialpagos_crud.php?action=delete&id_historial=<?php echo $row['id_historial']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este historial?')">Eliminar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    
  </div>
  <?php include '../footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/script.js"></script>
</body>
</html>
