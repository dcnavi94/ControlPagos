<?php
session_start();
require_once '../db_connection.php';

if (!$conn) {
  die("Error de conexión a la base de datos. Por favor, revisa las credenciales y la configuración.");
}

if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['rol'] == 'alumno') {
        header("Location: ../user/dashboard.php");
        exit;
    } elseif ($_SESSION['rol'] == 'administrador') {
        header("Location: ../administrator/usuarios_crud.php");
        exit;
    }
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id_usuario, username, password, rol FROM usuarios WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['rol']        = $user['rol'];

            if ($user['rol'] == 'alumno') {
                header("Location: ../user/dashboard.php");
                exit;
            } elseif ($user['rol'] == 'administrador') {
                header("Location: ../administrator/usuarios_crud.php");
                exit;
            }
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "Usuario no encontrado.";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php require_once 'menulogin.php'; ?>

  <div class="container mt-5">
    <h2 class="mb-4 text-center">Iniciar Sesión</h2>
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST" class="mx-auto" style="max-width: 400px;">
      <div class="mb-3">
        <label for="username" class="form-label">Usuario</label>
        <input type="text" name="username" class="form-control" id="username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" id="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
    </form>
  </div>

  <?php include '../footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/script.js"></script>
</body>
</html>
