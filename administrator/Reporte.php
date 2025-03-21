<?php
// Activa la visualización de errores (para desarrollo; recuerda desactivarlo en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Verifica que el usuario esté autenticado y sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: ../login/login.php");
    exit;
}

require_once '../db_connection.php'; // Ajusta la ruta según tu estructura
require_once '../vendor/autoload.php';   // Asegúrate de que la ruta al autoload de Composer sea la correcta

$error_message = "";
$success_message = "";

// Si se envía el formulario, se genera el reporte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar'])) {
    // Recibir datos del formulario
    $tipo_reporte  = $_POST['tipo_reporte'] ?? '';
    $fecha_inicio  = $_POST['fecha_inicio'] ?? '';
    $fecha_fin     = $_POST['fecha_fin'] ?? '';
    $alumno_id     = $_POST['alumno_id'] ?? '';

    // Validar fechas
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $error_message = "Debe seleccionar un rango de fechas.";
    } else {
        // Construir la consulta base
        $sql = "SELECT hp.*,
                       p.concepto AS pago_concepto,
                       u.nombre AS alumno_nombre,
                       u.apellido AS alumno_apellido,
                       u.nivel AS alumno_nivel
                FROM historialpagos hp
                LEFT JOIN pagos p ON hp.id_pago = p.id_pago
                LEFT JOIN usuarios u ON hp.id_estudiante = u.id_usuario
                WHERE hp.fecha_transaccion BETWEEN '$fecha_inicio' AND '$fecha_fin'";

        // Agregar filtros según el tipo de reporte
        switch ($tipo_reporte) {
            case 'alumno':
                // Filtrar por un alumno específico
                if (!empty($alumno_id)) {
                    $sql .= " AND u.id_usuario = $alumno_id";
                } else {
                    $error_message = "Debe seleccionar un alumno para el reporte.";
                }
                break;
            case 'universidad':
                $sql .= " AND u.nivel = 'universidad'";
                break;
            case 'preparatoria':
                $sql .= " AND u.nivel = 'preparatoria'";
                break;
            case 'todos':
                // Sin filtro adicional
                break;
            default:
                $error_message = "Seleccione un tipo de reporte válido.";
        }

        if (!$error_message) {
            $sql .= " ORDER BY hp.fecha_transaccion ASC";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                $error_message = "Error al generar el reporte: " . mysqli_error($conn);
            } else {
                // Construir el HTML para el PDF
                $html = '<h2 style="text-align:center;">Reporte de Historial de Pagos</h2>';
                $html .= "<p style='text-align:center;'>Rango de fechas: " 
                         . htmlspecialchars($fecha_inicio) . " - " 
                         . htmlspecialchars($fecha_fin) . "</p>";

                // Descripción del tipo de reporte
                switch ($tipo_reporte) {
                    case 'alumno':
                        $html .= "<p style='text-align:center;'>Tipo de reporte: Alumno específico</p>";
                        break;
                    case 'todos':
                        $html .= "<p style='text-align:center;'>Tipo de reporte: Todos</p>";
                        break;
                    case 'universidad':
                        $html .= "<p style='text-align:center;'>Tipo de reporte: Universidad</p>";
                        break;
                    case 'preparatoria':
                        $html .= "<p style='text-align:center;'>Tipo de reporte: Preparatoria</p>";
                        break;
                }

                $html .= '<table border="1" cellspacing="0" cellpadding="5" style="width:100%; border-collapse:collapse;">';
                $html .= '<thead>
                            <tr style="background-color:#f2f2f2;">
                              <th>ID Historial</th>
                              <th>Pago</th>
                              <th>Estudiante</th>
                              <th>Nivel</th>
                              <th>Fecha Transacción</th>
                              <th>Método de Pago</th>
                              <th>Monto</th>
                            </tr>
                          </thead><tbody>';

                while ($row = mysqli_fetch_assoc($result)) {
                    $html .= '<tr>';
                    $html .= '<td>' . $row['id_historial'] . '</td>';
                    $html .= '<td>Pago #' . $row['id_pago'] . ' - ' . htmlspecialchars($row['pago_concepto']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['alumno_nombre'] . ' ' . $row['alumno_apellido']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['alumno_nivel']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['fecha_transaccion']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($row['metodo_pago']) . '</td>';
                    $html .= '<td>' . number_format($row['monto_pago'], 2) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';

                // Generar el PDF con TCPDF
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                // Configurar la información del documento
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Tu Nombre o Empresa');
                $pdf->SetTitle('Reporte de Historial de Pagos');
                $pdf->SetSubject('Reporte de Historial de Pagos');

                // Eliminar encabezado y pie de página predeterminados
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);

                // Configurar márgenes
                $pdf->SetMargins(15, 15, 15);
                $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

                // Agregar una página
                $pdf->AddPage();

                // Escribir el contenido HTML en el PDF
                $pdf->writeHTML($html, true, false, true, false, '');

                // Nombre del archivo y salida del PDF forzando la descarga
                $nombreArchivo = "reporte_historial_" . date("YmdHis") . ".pdf";
                $pdf->Output($nombreArchivo, 'D');
                exit;  // Termina la ejecución tras descargar el PDF
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Generar Reporte</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    function toggleAlumnoField() {
      var tipoReporte = document.getElementById('tipo_reporte').value;
      var alumnoField = document.getElementById('alumno_field');
      // Mostrar el campo de alumno solo si se selecciona "alumno"
      if (tipoReporte === 'alumno') {
        alumnoField.style.display = 'block';
      } else {
        alumnoField.style.display = 'none';
      }
    }
  </script>
</head>
<body>
  <!-- Menú (ajusta la ruta según tu estructura) -->
  <?php include 'menu.php'; ?>
  
  <div class="container mt-4">
    <h2 class="mb-4">Generar Reporte de Historial de Pagos</h2>
    
    <!-- Mensajes de error o éxito -->
    <?php if ($error_message): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <!-- Formulario de filtros -->
    <div class="card mb-4">
      <div class="card-header">Filtros del Reporte</div>
      <div class="card-body">
        <form action="Reporte.php" method="POST">
          <!-- Tipo de reporte -->
          <div class="mb-3">
            <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
            <select name="tipo_reporte" id="tipo_reporte" class="form-select" onchange="toggleAlumnoField()" required>
              <option value="">Seleccione un tipo de reporte</option>
              <option value="alumno">Reporte de un Alumno</option>
              <option value="todos">Reporte de Todos</option>
              <option value="universidad">Reporte de Universidad</option>
              <option value="preparatoria">Reporte de Preparatoria</option>
            </select>
          </div>

          <!-- Seleccionar alumno (solo si se elige 'alumno') -->
          <div class="mb-3" id="alumno_field" style="display: none;">
            <label for="alumno_id" class="form-label">Seleccione Alumno</label>
            <select name="alumno_id" id="alumno_id" class="form-select">
              <option value="">Seleccione un alumno</option>
              <?php
              // Consulta de alumnos
              $sqlAlumnos = "SELECT id_usuario, nombre, apellido FROM usuarios WHERE rol='alumno' ORDER BY nombre ASC";
              $resultAlumnos = mysqli_query($conn, $sqlAlumnos);
              while ($rowA = mysqli_fetch_assoc($resultAlumnos)) {
                  echo '<option value="' . $rowA['id_usuario'] . '">' 
                       . htmlspecialchars($rowA['nombre'] . ' ' . $rowA['apellido']) 
                       . '</option>';
              }
              ?>
            </select>
          </div>

          <!-- Rango de fechas -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
              <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="fecha_fin" class="form-label">Fecha de Fin</label>
              <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
            </div>
          </div>

          <button type="submit" name="generar" class="btn btn-primary">Generar Reporte</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer (ajusta la ruta según tu estructura) -->
  <?php include '../footer.php'; ?>

  <!-- Bootstrap JS Bundle (incluye Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
