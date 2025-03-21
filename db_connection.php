<?php
require __DIR__ . '/vendor/autoload.php'; // Cargar Composer

use Dotenv\Dotenv;

// Cargar el archivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Obtener variables de entorno
$servidor = $_ENV['DB_HOST'];
$usuario = $_ENV['DB_USER'];
$clave = $_ENV['DB_PASS'];
$base_de_datos = $_ENV['DB_NAME'];

// Habilitar reportes de errores de MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servidor, $usuario, $clave, $base_de_datos);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
