<?php
// usuarios_crud.php

require_once '../db_connection.php';

$error_message   = "";
$success_message = "";

// ------------------------
// Procesar eliminación
// ------------------------
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id_usuario'])) {
    $id_usuario = intval($_GET['id_usuario']);
    $sqlDelete = "DELETE FROM usuarios WHERE id_usuario = $id_usuario";
    if (mysqli_query($conn, $sqlDelete)) {
        $success_message = "Usuario eliminado correctamente.";
    } else {
        $error_message = "Error al eliminar usuario: " . mysqli_error($conn);
    }
    header("Location: usuarios_crud.php");
    exit;
}

// ------------------------
// Procesar edición (mostrar formulario para editar)
// ------------------------
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id_usuario'])) {
    $id_usuario = intval($_GET['id_usuario']);
    $sqlUser = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
    $resultUser = mysqli_query($conn, $sqlUser);
    if (!$resultUser || mysqli_num_rows($resultUser) == 0) {
        $error_message = "Usuario no encontrado.";
    } else {
        $usuario = mysqli_fetch_assoc($resultUser);
        // Procesar actualización cuando se envíe el formulario de edición
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            // Actualizar contraseña solo si se ingresa un nuevo valor
            $newPassword = $_POST['password'];
            if (!empty($newPassword)) {
                $password = password_hash($newPassword, PASSWORD_DEFAULT);
                $password_sql = ", password = '$password'";
            } else {
                $password_sql = "";
            }
            $rol      = mysqli_real_escape_string($conn, $_POST['rol']);
            $nombre   = mysqli_real_escape_string($conn, $_POST['nombre']);
            $apellido = mysqli_real_escape_string($conn, $_POST['apellido']);
            $email    = mysqli_real_escape_string($conn, $_POST['email']);
            $estatus  = mysqli_real_escape_string($conn, $_POST['estatus']);
            $nivel    = mysqli_real_escape_string($conn, $_POST['nivel']);
            $beca     = isset($_POST['beca']) ? 1 : 0;
            
            $sqlUpdate = "UPDATE usuarios SET 
                            username = '$username'
                            $password_sql,
                            rol = '$rol',
                            nombre = '$nombre',
                            apellido = '$apellido',
                            email = '$email',
                            estatus = '$estatus',
                            nivel = '$nivel',
                            beca = $beca
                          WHERE id_usuario = $id_usuario";
            if (mysqli_query($conn, $sqlUpdate)) {
                $success_message = "Usuario actualizado correctamente.";
                header("Location: usuarios_crud.php");
                exit;
            } else {
                $error_message = "Error al actualizar usuario: " . mysqli_error($conn);
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
          <meta charset="UTF-8">
          <title>Editar Usuario - Ciencias Artes y Metaeducación San José</title>
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <!-- Bootstrap CSS -->
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
          <!-- CSS Personalizado -->
          <link rel="stylesheet" href="..//css/styles.css">
        </head>
        <body>
          <?php include 'menu.php'; ?>
          <div class="container mt-4">
            <h2 class="mb-4">Editar Usuario</h2>
            <?php if ($error_message): ?>
              <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="usuarios_crud.php?action=edit&id_usuario=<?php echo $id_usuario; ?>" method="POST">
              <div class="row mb-3">
                <div class="col">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                </div>
                <div class="col">
                  <label for="password" class="form-label">Contraseña (dejar vacío para no modificar)</label>
                  <input type="password" name="password" class="form-control">
                </div>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="rol" class="form-label">Rol</label>
                  <select name="rol" class="form-select" required>
                    <option value="alumno" <?php if($usuario['rol'] == 'alumno') echo 'selected'; ?>>Alumno</option>
                    <option value="administrador" <?php if($usuario['rol'] == 'administrador') echo 'selected'; ?>>Administrador</option>
                  </select>
                </div>
                <div class="col">
                  <label for="estatus" class="form-label">Estatus</label>
                  <select name="estatus" class="form-select" required>
                    <option value="inscrito" <?php if($usuario['estatus'] == 'inscrito') echo 'selected'; ?>>Inscrito</option>
                    <option value="no inscrito" <?php if($usuario['estatus'] == 'no inscrito') echo 'selected'; ?>>No Inscrito</option>
                    <option value="egresado" <?php if($usuario['estatus'] == 'egresado') echo 'selected'; ?>>Egresado</option>
                  </select>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="nombre" class="form-label">Nombre</label>
                  <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>
                <div class="col">
                  <label for="apellido" class="form-label">Apellido</label>
                  <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                <div class="col">
                  <label for="nivel" class="form-label">Nivel</label>
                  <select name="nivel" class="form-select" required>
                    <option value="universidad" <?php if($usuario['nivel'] == 'universidad') echo 'selected'; ?>>Universidad</option>
                    <option value="preparatoria" <?php if($usuario['nivel'] == 'preparatoria') echo 'selected'; ?>>Preparatoria</option>
                  </select>
                </div>
              </div>
              <div class="mb-3 form-check">
                <input type="checkbox" name="beca" class="form-check-input" id="beca" <?php if($usuario['beca'] == 1) echo 'checked'; ?>>
                <label class="form-check-label" for="beca">Tiene Beca</label>
              </div>
              <button type="submit" name="actualizar" class="btn btn-warning">Actualizar Usuario</button>
              <a href="usuarios_crud.php" class="btn btn-secondary">Cancelar</a>
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

// ------------------------
// Procesar inserción de nuevo usuario
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = mysqli_real_escape_string($conn, $_POST['rol']);
    $nombre   = mysqli_real_escape_string($conn, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conn, $_POST['apellido']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $estatus  = mysqli_real_escape_string($conn, $_POST['estatus']);
    $nivel    = mysqli_real_escape_string($conn, $_POST['nivel']);
    $beca     = isset($_POST['beca']) ? 1 : 0;
    
    $sqlInsert = "INSERT INTO usuarios (username, password, rol, nombre, apellido, email, estatus, nivel, beca)
                  VALUES ('$username', '$password', '$rol', '$nombre', '$apellido', '$email', '$estatus', '$nivel', $beca)";
    
    if (mysqli_query($conn, $sqlInsert)) {
        $success_message = "Usuario insertado correctamente.";
    } else {
        $error_message = "Error al insertar usuario: " . mysqli_error($conn);
    }
}

// Consulta para listar todos los usuarios
$sqlUsuarios = "SELECT * FROM usuarios";
$resultUsuarios = mysqli_query($conn, $sqlUsuarios);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CRUD Usuarios - Ciencias Artes y Metaeducación San José</title>
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
    <h2 class="mb-4">Administrar Usuarios</h2>
    
    <!-- Mostrar mensajes -->
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <!-- Formulario para insertar un nuevo usuario -->
    <div class="card mb-4">
      <div class="card-header">
        Insertar Nuevo Usuario
      </div>
      <div class="card-body">
        <form action="usuarios_crud.php" method="POST">
          <div class="row mb-3">
            <div class="col">
              <label for="username" class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label for="rol" class="form-label">Rol</label>
              <select name="rol" class="form-select" required>
                <option value="alumno">Alumno</option>
                <option value="administrador">Administrador</option>
              </select>
            </div>
            <div class="col">
              <label for="estatus" class="form-label">Estatus</label>
              <select name="estatus" class="form-select" required>
                <option value="inscrito">Inscrito</option>
                <option value="no inscrito">No Inscrito</option>
                <option value="egresado">Egresado</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col">
              <label for="apellido" class="form-label">Apellido</label>
              <input type="text" name="apellido" class="form-control" required>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col">
              <label for="nivel" class="form-label">Nivel</label>
              <select name="nivel" class="form-select" required>
                <option value="universidad">Universidad</option>
                <option value="preparatoria">Preparatoria</option>
              </select>
            </div>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" name="beca" class="form-check-input" id="beca">
            <label class="form-check-label" for="beca">Tiene Beca</label>
          </div>
          <button type="submit" name="guardar" class="btn btn-primary">Guardar Usuario</button>
        </form>
      </div>
    </div>
    
    <!-- Lista de Usuarios con botones Editar y Eliminar -->
    <div class="card">
      <div class="card-header">
        Lista de Usuarios
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead class="table-primary">
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Rol</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>Email</th>
              <th>Estatus</th>
              <th>Nivel</th>
              <th>Beca</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($resultUsuarios)): ?>
              <tr>
                <td><?php echo $row['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['rol']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                <td><?php echo htmlspecialchars($row['apellido']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['estatus']); ?></td>
                <td><?php echo htmlspecialchars($row['nivel']); ?></td>
                <td><?php echo $row['beca'] ? 'Sí' : 'No'; ?></td>
                <td>
                  <a href="usuarios_crud.php?action=edit&id_usuario=<?php echo $row['id_usuario']; ?>" class="btn btn-sm btn-warning">Editar</a>
                  <a href="usuarios_crud.php?action=delete&id_usuario=<?php echo $row['id_usuario']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</a>
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
