<?php
// db_connection.php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "sistema_pagos";  // Asegúrate de que la base de datos exista

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
