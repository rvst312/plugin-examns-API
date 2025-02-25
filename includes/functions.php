<?php

/**
 * Request to API
 *
 * @param array $data Data to send to API
 * @return array $response API response to array 
 */
function send_data_to_api($data, $base_url, $endpoint) {
    // Handler empty $data state 
    if (empty($data)) return ['error' => 'No se proporcionaron datos'];
    
    $api_handler = new Fetch_API_handler($base_url . $endpoint);
    
    $response = $api_handler->post_data_from_api($data);
    
    // Error handlers
    if (isset($response['error'])) {
        return ['error' => 'Error en la respuesta de la API'];
    }

    return $response;
}

/**
 * Data to send in headers for query  
 *
 * @param string $base_url URL base.
 * @param string $endpoint Endpoint to attack.
 * @param string $tipus_cerca El tipo de datos que carga (ej. "pregunta", "examen").
 * @param string $comunitat La comunidad autónoma.
 * @param string $tipus_prova El tipo de prueba (ej. "selectivitat").
 * @param string $assignatura La asignatura (ej. "química").
 * @param string $convocatoria La convocatoria (ej. "juny").
 * @param int $any El año (ej. 2015).
 * @param string $tematica La tematica (ej. "taula periòdica").
 * @param int $pagina Paginación (ej. 1).
 */
function get_exams_data($base_url, $endpoint, $tipus_cerca, $comunitat, $tipus_prova, $assignatura, $convocatoria, $any, $tematica, $pagina) {
    $data = [
        "tipus_cerca" => $tipus_cerca,
        "comunitat" => $comunitat,
        "tipus_prova" => $tipus_prova,
        "assignatura" => $assignatura,
        "convocatoria" => $convocatoria,
        "any" => $any,
        "incloure_anys_posteriors" => false,
        "tematica" => $tematica,
        "pagina" => $pagina,
        "mida_pagina" => 12
    ];

    return send_data_to_api($data, $base_url, $endpoint);
}
