<?php

function listing_view_shortcode() {
    // Default attributes
    $atts = [
        'tipus_cerca' => 'pregunta',
        'comunitat' => 'catalunya',
        'tipus_prova' => 'selectivitat',
        'assignatura' => null,
        'any' => null,
        'pagina' => 1
    ];

    // Get parameters from URL
    $subject = get_query_var('subject', '');
    $year = get_query_var('year', '');

    // Set the parameters from URL if they exist
    if (!empty($subject)) {
        $subject = urldecode($subject);
        $subject = html_entity_decode($subject, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $atts['assignatura'] = wp_unslash($subject);
    }
    if (!empty($year)) {
        $atts['any'] = urldecode($year);
    }

    if (empty($atts['assignatura']) && empty($atts['any'])) {
        return '<div class="error-message">No subject or year specified</div>';
    }

    // API configuration
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
        null, // convocatoria
        $atts['any'],
        null, // tematica
        $atts['pagina']
    );

    // Display results using the existing function
    $items = mostrar_resultados($response);

    // Prepare pagination parameters
    $current_params = array_filter($atts, function ($value) {
        return $value !== null;
    });
    unset($current_params['pagina']);

    // Generate pagination
    $pagination = view_pagination($response['num_resultats'], 12, $current_params);

    // Build the output
    ob_start();
    ?>
    <div class="listing-container">
        <h1><?= !empty($atts['assignatura']) ? "Exercises for " . esc_html($atts['assignatura']) : "Exercises from " . esc_html($atts['any']) ?></h1>
        <?php
        echo $items;
        echo $pagination;
        ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('listing_view', 'listing_view_shortcode');