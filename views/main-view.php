<?php
// This file contains the code for the main view of the plugin, which is accessed via a shortcode. The main view is
// the user interface for the API, which shows the different parameters of the API and allows the user to select
// them.
function mostrar_datos_api_shortcode()
{
    // Default attributes
    $atts = [
        'tipus_cerca' => 'pregunta',
        'comunitat' => 'catalunya',
        'tipus_prova' => 'selectivitat',
        'assignatura' => null,
        'convocatoria' => null,
        'any' => null,
        'tematica' => null,
        'pagina' => 1
    ];

    // Logic for handling the GET request of the filter form
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        foreach ($atts as $key => $default_value) {
            if (isset($_GET[$key])) {
                if ($_GET[$key] !== '') {
                    $atts[$key] = sanitize_text_field($_GET[$key]);
                } else {
                    $atts[$key] = null;
                }
            }
        }
    }

    // Ensure that the page is a valid number
    // Prevents the page number from being invalid or maliciously set
    $atts['pagina'] = isset($atts['pagina']) ? max(1, intval($atts['pagina'])) : 1;

    $formulario = mostrar_filtros($atts); // Display filters

    $base_url = 'https://formaciomiro-cercadorapi-ne-prd-ckccggh5heckbxf7.northeurope-01.azurewebsites.net';
    $endpoint = '/cerca';

    // Call the API with the filtered attributes
    $response = get_exams_data(
        $base_url,
        $endpoint,
        $atts['tipus_cerca'],
        $atts['comunitat'],
        $atts['tipus_prova'],
        $atts['assignatura'],
        $atts['convocatoria'],
        $atts['any'],
        $atts['tematica'],
        $atts['pagina']
    );

    $items = mostrar_resultados($response); // Display results

    // Prepare the current parameters for pagination
    // Filter out null values from the attributes
    $current_params = array_filter($atts, function ($value) {
        return $value !== null;
    });
    unset($current_params['pagina']); // Remove the current page parameter for pagination

    // Generate pagination links
    // This function will create pagination based on the total number of results and the current parameters
    $pagination = view_pagination($response['num_resultats'], 12, $current_params);

    //=====================//
    // Construct interface //
    //=====================//
    ob_start();
    echo $formulario;
    echo $items;
    echo $pagination;
    echo render_seo_buttons();
    return ob_get_clean();
}
add_shortcode('mostrar_datos_api', 'mostrar_datos_api_shortcode'); // Register shortcode "mostrar_datos_api"