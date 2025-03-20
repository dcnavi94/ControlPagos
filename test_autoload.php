<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

if (class_exists('MercadoPago\SDK')) {
    echo "La clase MercadoPago\SDK se ha cargado correctamente.";
} else {
    echo "No se pudo cargar la clase MercadoPago\SDK.";
}
