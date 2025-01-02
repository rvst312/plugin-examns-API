<?php

function mostrar_datos_api_shortcode()
{
    // Default attributes definition
    $atts = [
        'comunitat' => 'catalunya',
        'tipus_prova' => 'selectivitat',
        'assignatura' => 'química',
        'convocatoria' => 'juny',
        'any' => 2015,
        'tematica' => 'taula periòdica',
        'pagina' => 1
    ];

    // Logic for handling the GET request of the filter form
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $atts['comunitat'] = $_GET['comunitat'] ?? $atts['comunitat'];
        $atts['tipus_prova'] = $_GET['tipus_prova'] ?? $atts['tipus_prova'];
        $atts['assignatura'] = $_GET['assignatura'] ?? $atts['assignatura'];
        $atts['convocatoria'] = $_GET['convocatoria'] ?? $atts['convocatoria'];
        $atts['any'] = $_GET['any'] ?? $atts['any'];
        $atts['tematica'] = '';
        $atts['pagina'] = 1;
    }

    $formulario = mostrar_filtros($atts); // Display filters

    // Call the API with the filtered attributes
    $response = get_exams_data(
        $atts['comunitat'],
        $atts['tipus_prova'],
        $atts['assignatura'],
        $atts['convocatoria'],
        $atts['any'],
        $atts['tematica'],
        $atts['pagina']
    );

    $items = mostrar_resultados($response); // Display results

    // Construct default interface
    ob_start();
    echo $formulario;
    echo $items;
    return ob_get_clean();
}
add_shortcode('mostrar_datos_api', 'mostrar_datos_api_shortcode'); // Register shortcode "mostrar_datos_api" charge application 