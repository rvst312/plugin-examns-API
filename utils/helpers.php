<?php

/**
 * Load remote JSON configuration with caching
 *
 * @param string $json_url URL of the JSON configuration file
 * @return array|string Returns array with JSON data on success, error message string on failure
 */
function load_remote_json_config($json_url = 'https://formaciomiro.com/wp-content/uploads/examens/config/configuracio_assignatures.json') {
    $cache_key = 'examens_config_json_' . md5($json_url);
    
    // Try to get cached data first
    $json_data = get_transient($cache_key);
    if ($json_data !== false) {
        return $json_data;
    }
    
    // Cache miss - fetch from remote URL
    $response = wp_remote_get($json_url, array(
        'timeout' => 30,
        'headers' => array(
            'Accept' => 'application/json',
        )
    ));
    
    // Check for errors in the HTTP request
    if (is_wp_error($response)) {
        return 'Error: No se pudo cargar la configuración. ' . $response->get_error_message();
    }
    
    // Check HTTP response code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return 'Error: La configuración no está disponible (HTTP ' . $response_code . ').';
    }
    
    // Get and decode JSON data
    $json_body = wp_remote_retrieve_body($response);
    $json_data = json_decode($json_body, true);
    
    // Validate JSON data
    if (json_last_error() !== JSON_ERROR_NONE) {
        return 'Error: La configuración JSON no es válida.';
    }
    
    if (empty($json_data)) {
        return 'Error: La configuración está vacía.';
    }
    
    // Cache the data for 1 hour (3600 seconds)
    set_transient($cache_key, $json_data, 3600);
    
    return $json_data;
}
