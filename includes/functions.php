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
 * Resolve a secure path to the configuracio_assignatures.json file.
 * Allows overriding via constants and validates the path to avoid traversal.
 *
 * Optional constants (define in wp-config.php):
 * - EXAMENS_CONFIG_JSON: absolute path to the JSON file
 * - EXAMENS_CONFIG_BASE_DIR: base directory allowed for the JSON
 *
 * @return string|false Resolved absolute path or false if invalid/not readable
 */
function get_examens_config_json_path() {
    // Default path in uploads
    $uploads = wp_upload_dir();
    if (is_wp_error($uploads) || empty($uploads['basedir'])) {
        return false;
    }
    $default_path = rtrim($uploads['basedir'], '/'). '/examens/config/configuracio_assignatures.json';

    // Custom path via constant
    $custom_path = defined('EXAMENS_CONFIG_JSON') ? EXAMENS_CONFIG_JSON : $default_path;

    // Resolve real paths
    $resolved_file = realpath($custom_path);
    if ($resolved_file === false) {
        // Fall back to default if custom invalid
        $resolved_file = realpath($default_path);
    }
    if ($resolved_file === false) {
        return false;
    }

    // Allowed base dir
    $allowed_base = defined('EXAMENS_CONFIG_BASE_DIR') ? EXAMENS_CONFIG_BASE_DIR : dirname($default_path);
    $resolved_base = realpath($allowed_base);
    if ($resolved_base === false) {
        $resolved_base = dirname($default_path);
    }

    // Ensure file is inside allowed base
    if (strpos($resolved_file, $resolved_base) !== 0) {
        return false;
    }

    // Ensure readability
    if (!is_readable($resolved_file)) {
        return false;
    }

    return $resolved_file;
}

/**
 * Load and decode configuracio_assignatures.json securely.
 *
 * @return array|null Decoded JSON as array or null on error
 */
function load_examens_config_json() {
    $path = get_examens_config_json_path();
    if ($path === false || !file_exists($path)) {
        return null;
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        return null;
    }

    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return $data;
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
function get_exams_data($base_url, $endpoint, $tipus_cerca, $comunitat, $tipus_prova, $assignatura, $convocatoria, $any, $paraules_clau, $tematica, $pagina) {
    $data = [
        "tipus_cerca" => $tipus_cerca,
        "comunitat" => $comunitat,
        "tipus_prova" => $tipus_prova,
        "assignatura" => $assignatura,
        "convocatoria" => $convocatoria,
        "any" => $any,
        "paraules_clau" => $paraules_clau,
        "incloure_anys_posteriors" => false,
        "tematica" => $tematica,
        "pagina" => $pagina,
        "mida_pagina" => 12
    ];

    return send_data_to_api($data, $base_url, $endpoint);
}
