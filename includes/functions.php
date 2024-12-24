<?php
// Asegúrate de incluir la clase de API
require_once __DIR__ . '/class-api-handler.php';

/**
 * Realiza una petición a la API para obtener o enviar datos.
 *
 * @param array $data Datos a enviar a la API.
 * @return array Respuesta de la API.
 */
function send_data_to_api($data) {
    $api_handler = new Exercises_API_handler();
    
    $response = $api_handler->post_data_from_api($data);

    return $response;
}

/**
 * Otra función ejemplo para mostrar datos desde la API
 */
function get_exams_data() {
    $data = [
        'tipus_resultat' => 'pregunta',
        'tipus_prova' => 'example',
        'any' => 2023,
        'convocatoria' => 'test',
        'serie' => 'serie_1',
        'assignatura' => 'math',
        'descripcio' => 'Example description',
        'url_pregunta' => 'https://example.com/question',
        'url_solucio' => 'https://example.com/solution',
        'url_miniatura' => 'https://example.com/thumbnail',
        'metadata' => [],
    ];

    return send_data_to_api($data);
}