<?php
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

    // Asegurarse que la página sea un número válido
    $atts['pagina'] = isset($atts['pagina']) ? max(1, intval($atts['pagina'])) : 1;

    $formulario = mostrar_filtros($atts); // Display filters

    // Call the API with the filtered attributes
    $response = get_exams_data(
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

    // Preparar los parámetros actuales para la paginación
    $current_params = array_filter($atts, function($value) {
        return $value !== null;
    });
    unset($current_params['pagina']); 

    $pagination = view_pagination($response['num_resultats'], 12, $current_params);

    // Construct interface
    ob_start();
    echo $formulario;
    echo $items;
    echo $pagination;
    return ob_get_clean();
}
add_shortcode('mostrar_datos_api', 'mostrar_datos_api_shortcode'); // Register shortcode "mostrar_datos_api"