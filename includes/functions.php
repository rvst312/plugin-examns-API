<?php
require_once __DIR__ . '/class-api-handler.php';

/**
 * Request to API
 *
 * @param array $data Data to send to API
 * @return array $response API response to array 
 */
function send_data_to_api($data) {
    $base_url = 'https://formaciomiro-cercadorapi-ne-prd-ckccggh5heckbxf7.northeurope-01.azurewebsites.net';
    $endpoint = '/cerca';
    
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
 * @param string $comunitat La comunidad autónoma.
 * @param string $tipus_prova El tipo de prueba (ej. "selectivitat").
 * @param string $assignatura La asignatura (ej. "química").
 * @param string $convocatoria La convocatoria (ej. "juny").
 * @param int $any El año (ej. 2015).
 * @param string $tematica La tematica (ej. "taula periòdica").
 * @param int $pagina Paginación (ej. 1).
 */
function get_exams_data($comunitat, $tipus_prova, $assignatura, $convocatoria, $any, $tematica, $pagina) {
    $data = [
        "tipus_cerca" => "pregunta",
        "comunitat" => $comunitat,
        "tipus_prova" => $tipus_prova,
        "assignatura" => $assignatura,
        "convocatoria" => $convocatoria,
        "any" => $any,
        "incloure_anys_posteriors" => true,
        "tematica" => $tematica,
        "pagina" => $pagina,
        "mida_pagina" => 10
    ];

    return send_data_to_api($data);
}