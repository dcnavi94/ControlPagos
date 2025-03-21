<?php
// configuracion_pagos.php
require_once 'db_connection.php';

$error_message   = "";
$success_message = "";

// ---------------------------------
// Eliminar configuración (Delete)
// ---------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['nivel']) && isset($_GET['tiene_beca'])) {
    $nivel = mysqli_real_escape_string($conn, $_GET['nivel']);
    $tiene_beca = intval($_GET['tiene_beca']);
    $sqlDelete = "DELETE FROM configuracion_pagos WHERE nivel = '$nivel' AND tiene_beca = $tiene_beca";
    if (mysqli_query($conn, $sqlDelete)) {
        $success_message = "Configuración eliminada correctamente.";
    } else {
        $error_message = "Error al eliminar configuración: " . mysqli_error($conn);
    }
    header("Location: configuracion_pagos.php");
    exit;
}

// ---------------------------------
// Editar configuración (Update)
// ---------------------------------
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['nivel']) && isset($_GET['tiene_beca'])) {
    $nivel = mysqli_real_escape_string($conn, $_GET['nivel']);
    $tiene_beca = intval($_GET['tiene_beca']);
    $sqlConf = "SELECT * FROM configuracion_pagos WHERE nivel = '$nivel' AND tiene_beca = $tiene_beca";
    $resultConf = mysqli_query($conn, $sqlConf);
    if (!$resultConf || mysqli_num_rows($resultConf) == 0) {
        $error_message = "Configuración no encontrada.";
    } else {
        $config = mysqli_fetch_assoc($resultConf);
        // Procesar actualización cuando se envía el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
            // En este caso se actualizará el monto; para cambiar la clave primaria se recomienda borrar e insertar.
            $monto = floatval($_POST['monto']);
            $sqlUpdate = "UPDATE configuracion_pagos SET monto = $monto 
                          WHERE nivel = '$nivel' AND tiene_beca = $tiene_beca";
            if (mysqli_query($conn, $sqlUpdate)) {
                $success_message = "Configuración actualizada correctamente.";
                header("Location: configuracion_pagos.php");
                exit;
            } else {
                $error_message = "Error al actualizar configuración: " . mysqli_error($conn);
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <title>Editar Configuración de Pagos - Ciencias Artes y Metaeducación San José</title>
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
          <link rel="stylesheet" href="styles.css">
        </head>
        <body>
          <?php include 'menu.php'; ?>
          <div class="container mt-4">
            <h2 class="mb-4">Editar Configuración de Pagos</h2>
            <?php if ($error_message): ?>
              <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="configuracion_pagos.php?action=edit&nivel=<?php echo $nivel; ?>&tiene_beca=<?php echo $tiene_beca; ?>" method="POST">
              <div class="mb-3">
                <label class="form-label">Nivel</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nivel); ?>" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label">Tiene Beca</label>
                <input type="text" class="form-control" value="<?php echo $tiene_beca ? 'Sí' : 'No'; ?>" disabled>
              </div>
              <div class="mb-3">
                <label for="monto" class="form-label">Monto</label>
                <input type="number" step="0.01" name="monto" class="form-control" value="<?php echo htmlspecialchars($config['monto']); ?>" required>
              </div>
              <button type="submit" name="actualizar" class="btn btn-warning">Actualizar Configuración</button>
              <a href="configuracion_pagos.php" class="btn btn-secondary">Cancelar</a>
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
// Procesar inserción de nueva configuración
// ---------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nivel = mysqli_real_escape_string($conn, $_POST['nivel']);
    $tiene_beca = isset($_POST['tiene_beca']) ? 1 : 0;
    $monto = floatval($_POST['monto']);
    
    $sqlInsert = "INSERT INTO configuracion_pagos (nivel, tiene_beca, monto)
                  VALUES ('$nivel', $tiene_beca, $monto)";
    if (mysqli_query($conn, $sqlInsert)) {
        $success_message = "Configuración insertada correctamente.";
    } else {
        $error_message = "Error al insertar configuración: " . mysqli_error($conn);
    }
}

// Consulta para listar todas las configuraciones
$sqlList = "SELECT * FROM configuracion_pagos";
$resultList = mysqli_query($conn, $sqlList);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Configuración de Pagos - CRUD - Ciencias Artes y Metaeducación San José</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'menu.php'; ?>
  <div class="container mt-4">
    <h2 class="mb-4">Administrar Configuración de Pagos</h2>
    
    <!-- Mensajes -->
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <!-- Formulario para insertar nueva configuración -->
    <div class="card mb-4">
      <div class="card-header">Insertar Nueva Configuración</div>
      <div class="card-body">
        <form action="configuracion_pagos.php" method="POST">
          <div class="row mb-3">
            <div class="col">
              <label for="nivel" class="form-label">Nivel</label>
              <select name="nivel" class="form-select" required>
                <option value="">Seleccione un nivel</option>
                <option value="universidad">Universidad</option>
                <option value="preparatoria">Preparatoria</option>
              </select>
            </div>
            <div class="col">
              <label for="tiene_beca" class="form-label">¿Tiene Beca?</label>
              <div class="form-check">
                <input type="checkbox" name="tiene_beca" class="form-check-input" id="tiene_beca">
                <label class="form-check-label" for="tiene_beca">Sí</label>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" required>
          </div>
          <button type="submit" name="guardar" class="btn btn-primary">Guardar Configuración</button>
        </form>
      </div>
    </div>
    
    <!-- Lista de Configuraciones -->
    <div class="card">
      <div class="card-header">Lista de Configuración de Pagos</div>
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead class="table-primary">
            <tr>
              <th>Nivel</th>
              <th>Tiene Beca</th>
              <th>Monto</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultList)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['nivel']); ?></td>
                <td><?php echo $row['tiene_beca'] ? 'Sí' : 'No'; ?></td>
                <td><?php echo $row['monto']; ?></td>
                <td>
                  <a href="configuracion_pagos.php?action=edit&nivel=<?php echo $row['nivel']; ?>&tiene_beca=<?php echo $row['tiene_beca']; ?>" class="btn btn-sm btn-warning">Editar</a>
                  <a href="configuracion_pagos.php?action=delete&nivel=<?php echo $row['nivel']; ?>&tiene_beca=<?php echo $row['tiene_beca']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta configuración?')">Eliminar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    
  </div>
  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>
