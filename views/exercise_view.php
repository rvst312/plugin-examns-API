<?php

function item_view_shortcode(){
    // Get current URL path
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $is_solution = (strpos($current_path, '/solucio/') !== false);
    
    // Try to get ID from both pretty URL and query parameter
    $item_id = get_query_var('id', '');
    if (empty($item_id)) {
        $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    }
    
    if (empty($item_id)) {
        return 'No item ID provided';
    }
    
    // Prepare data for API call
    $data = [
        "tipus" => "pregunta",
        'id' => $item_id
    ];
    
    // API configuration and call
    $base_url = 'https://formaciomiro-cercadorapi-ne-prd-ckccggh5heckbxf7.northeurope-01.azurewebsites.net'; 
    $endpoint = '/detalls'; 
    $response = send_data_to_api($data, $base_url, $endpoint);
    
    if (isset($response['error'])) {
        return '<div class="error-message">' . esc_html($response['error']) . '</div>';
    }
    
    // Build the output HTML
    $output = '<div class="exercise-container">';
    
    // Header section
    $output .= '<div class="exercise-header">';
    $output .= '<h1>' . sprintf('%s - %s %s', 
        esc_html($response['assignatura']),
        esc_html($response['tipus_prova']),
        esc_html($response['any'])
    ) . '</h1>';
    $output .= '</div>';
    
    // Buttons section - Show opposite action button
    $output .= '<div class="exercise-buttons">';
    if ($is_solution) {
        $output .= sprintf(
            '<a href="%s" class="button-primary-exams">Ver Ejercicio</a>',
            esc_url(home_url("/exercici/{$item_id}"))
        );
    } else {
        $output .= sprintf(
            '<a href="%s" class="button-primary-exams">Ver Solución</a>',
            esc_url(home_url("/solucio/{$item_id}"))
        );
    }
    $output .= '</div>';
    
    // PDF Viewer - Load corresponding URL based on path
    $output .= '<div class="pdf-container">';
    $pdf_url = $is_solution ? $response['url_solucio'] : $response['url_pregunta'];
    $output .= sprintf(
        '<iframe src="%s" width="100%%" height="800px" frameborder="0"></iframe>',
        esc_url($pdf_url)
    );
    $output .= '</div>';
    
    // Description section - Only show for exercise, not for solution
    if (!$is_solution && !empty($response['descripcio'])) {
        $output .= '<div class="exercise-description">';
        $output .= '<p>' . nl2br(esc_html($response['descripcio'])) . '</p>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

add_shortcode('item_view', 'item_view_shortcode');