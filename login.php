<?php
session_start();
require_once 'db_connection.php';

if (isset($_SESSION['id_usuario']))

    if ($_SESSION['rol'] == 'alumno') {
        header("Location: dashboard.php");
        exit;
    } elseif ($_SESSION['rol'] == 'administrador') {
        header("Location: usuarios_crud.php");
        exit;
    }

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['rol']        = $user['rol'];

            if ($user['rol'] == 'alumno') {
                header("Location: dashboard.php");
                exit;
            } elseif ($user['rol'] == 'administrador') {
                header("Location: usuarios_crud.php");
                exit;
            }
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión - Ciencias Artes y Metaeducación San José</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS Personalizado -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Incluir el menú superior (reutilizable) -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="logo_white.png" alt="Logo" style="width: 50px; height: auto;">
        Ciencias Artes y Metaeducación San José
      </a>
    </div>
  </nav>

  <div class="container mt-5">
    <h2 class="mb-4 text-center">Iniciar Sesión</h2>
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST" class="mx-auto" style="max-width: 400px;">
      <div class="mb-3">
        <label for="username" class="form-label">Usuario</label>
        <input type="text" name="username" class="form-control" id="username" placeholder="Ingrese su usuario" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" class="form-control" id="password" placeholder="Ingrese su contraseña" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
    </form>
  </div>
<br>
<br>
<br>
<br>
  <!-- Incluir el footer (reutilizable) -->
  <?php include 'footer.php'; ?>

  <!-- Bootstrap JS Bundle (incluye Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Archivo JavaScript personalizado -->
  <script src="script.js"></script>
</body>
</html>
