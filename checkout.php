<?php
require __DIR__ . '/vendor/autoload.php';

MercadoPago\SDK::setAccessToken("TEST-949513675902802-021211-f6b0fda151019853e4599029f5f30098-1160604684");

// Crear una preferencia de pago
$preference = new MercadoPago\Preference();

// Definir un producto de prueba
$item = new MercadoPago\Item();
$item->title = "Producto de prueba";
$item->quantity = 1;
$item->unit_price = 100;
$preference->items = [$item];

// URLs de redirección después del pago
$preference->back_urls = [
    "success" => "https://tusitio.com/pago-exitoso.php",
    "failure" => "https://tusitio.com/pago-fallido.php",
    "pending" => "https://tusitio.com/pago-pendiente.php"
];
$preference->auto_return = "approved";

$preference->save();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Pago de prueba con Mercado Pago</title>
</head>
<body>
    <h1>Realizar un pago de prueba</h1>
    <a href="<?= $preference->init_point ?>" target="_blank">
        <button>Pagar con Mercado Pago</button>
    </a>
</body>
</html>
