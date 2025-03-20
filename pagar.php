pagar.php <?php
session_start();
// Verifica que el usuario esté autenticado y sea alumno
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'alumno') {
    header("Location: login.php");
    exit;
}

require_once 'db_connection.php';

// Verificar que se haya pasado el id del pago
if (!isset($_GET['id_pago'])) {
    header("Location: dashboard.php");
    exit;
}

$id_pago = (int) $_GET['id_pago'];

// Consultar los datos del pago asignado a este alumno
$sqlPago = "SELECT * FROM pagos WHERE id_pago = $id_pago AND id_usuario = " . $_SESSION['id_usuario'] . " LIMIT 1";
$resultPago = mysqli_query($conn, $sqlPago);
if (!$resultPago || mysqli_num_rows($resultPago) == 0) {
    echo "Pago no encontrado.";
    exit;
}
$pago = mysqli_fetch_assoc($resultPago);

// Configuración de PayPal
$clientId = "ATP4BHRGpdnC0lembbaZT8GOcOA0NJ0KYUvVXgLwZYU1fxNC2XsyC0KZT2J5fGbet7F7s-qiO9uKLL1O";
$clientSecret = "EHkXhOBMablcC3jN1lTVAYUn_sS0sPfRHQeF4Rm2qOz2zPOchQL9fMXfPMagcq1n9-R0QKQjj_h-329Y"; // Reemplaza por tu Client Secret real

// URL de los endpoints de Sandbox de PayPal
$oauthUrl = "https://api-m.sandbox.paypal.com/v1/oauth2/token";
$orderUrl = "https://api-m.sandbox.paypal.com/v2/checkout/orders";

// 1. Obtener token de acceso
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $oauthUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Accept-Language: en_US"
]);

$response = curl_exec($ch);
if(curl_errno($ch)) {
    die("Error al obtener token: " . curl_error($ch));
}
curl_close($ch);

$tokenData = json_decode($response, true);
if (!isset($tokenData['access_token'])) {
    die("No se pudo obtener el token de acceso de PayPal.");
}
$accessToken = $tokenData['access_token'];

// 2. Crear una orden en PayPal
$orderData = [
    "intent" => "CAPTURE",
    "purchase_units" => [
        [
            "reference_id" => $pago['id_pago'],
            "description" => $pago['concepto'],
            "amount" => [
                "currency_code" => "USD",
                "value" => number_format($pago['monto'], 2, '.', '')
            ]
        ]
    ],
    "application_context" => [
        "return_url" => "http://localhost/tu_proyecto/confirmacion.php", // URL a la que PayPal redirigirá al aprobar el pago
        "cancel_url" => "http://localhost/tu_proyecto/dashboard.php"      // URL a la que PayPal redirigirá si se cancela el pago
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $orderUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $accessToken
]);

$orderResponse = curl_exec($ch);
if(curl_errno($ch)) {
    die("Error al crear la orden: " . curl_error($ch));
}
curl_close($ch);

$orderResult = json_decode($orderResponse, true);
if (!isset($orderResult['links'])) {
    die("Error en la respuesta de PayPal: " . $orderResponse);
}

// Buscar la URL de aprobación (donde rel = approve)
$approvalUrl = "";
foreach ($orderResult['links'] as $link) {
    if ($link['rel'] == "approve") {
        $approvalUrl = $link['href'];
        break;
    }
}

if (empty($approvalUrl)) {
    die("No se encontró la URL de aprobación.");
}

// Redirigir al usuario a PayPal para aprobar el pago
header("Location: " . $approvalUrl);
exit;
?>