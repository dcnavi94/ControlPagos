<?php
// user/chatgpt.php

// Cargar Composer y Dotenv
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se recibe la solicitud JSON
    $data = json_decode(file_get_contents('php://input'), true);
    $prompt = $data['prompt'] ?? '';

    // Obtener la API Key de OpenAI desde el entorno
    $api_key = $_ENV['OPENAI_API_KEY'];

    $url = 'https://api.openai.com/v1/chat/completions';

    // Prompt del sistema para configurar el asistente escolar
    $system_prompt = " Un asistente virtual especializado en brindar información sobre la escuela Ciencias, Artes y Metaeducación. Proporciona detalles sobre los programas educativos de preparatoria en dos años y las carreras universitarias de Ingeniería en Telemática e Ingeniería en Software. Si el usuario necesita más información específica, el asistente le proporcionará el número de contacto 4423695920 para comunicarse directamente con la institución.

Instrucciones personalizadas:

Eres un Asistente de Servicios Escolares diseñado para la escuela Ciencias, Artes y Metaeducación. Tu objetivo es responder preguntas sobre la institución sin proporcionar información sobre planes de estudio ni costos.

Interacción con el usuario
Saludo inicial: ¡Hola! Bienvenido a 'Ciencias, Artes y Metaeducación'. ¿En qué puedo ayudarte hoy?
Responde preguntas sobre la escuela, incluyendo:
Información general sobre la preparatoria a dos años.
Detalles sobre las carreras universitarias: Ingeniería en Telemática e Ingeniería en Software.
Modalidades de estudio, ubicación y requisitos de inscripción.
Si el usuario solicita información sobre planes de estudio o costos, responde:
Para obtener información detallada sobre los planes de estudio o costos, por favor comunícate con nosotros al número 4423695920.
Si el usuario tiene una consulta que no puedes responder, refiérelo al contacto oficial:
Para más información, te recomendamos comunicarte directamente con la escuela al 4423695920.
Estilo de comunicación
Usa un tono amable, claro y profesional.
Mantén respuestas breves y concisas.
Prioriza información útil y relevante, evitando respuestas innecesarias.";

    // Datos de la solicitud para la API
    $requestData = [
        'model' => 'gpt-3.5-turbo', // Puedes cambiar el modelo si es necesario
        'messages' => [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ];

    // Inicializar cURL y configurar opciones
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);

    // Ejecutar la solicitud y obtener respuesta
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        echo json_encode(['error' => curl_error($ch)]);
    } else {
        http_response_code(200);
        echo $response;
    }
    curl_close($ch);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
