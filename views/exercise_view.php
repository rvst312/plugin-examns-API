<?php

function item_view_shortcode($atts = [])
{
    // Procesar atributos del shortcode
    $atts = shortcode_atts([
        'tipus' => 'pregunta', // Valor por defecto: 'pregunta'
    ], $atts);

    // Obtener la URL actual
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $is_solution = (strpos($current_path, '/solucio/') !== false);

    // Obtener ID del ejercicio
    $item_id = get_query_var('id', '');
    if (empty($item_id)) {
        $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    }

    if (empty($item_id)) {
        return 'No item ID provided';
    }

    // Guardar el ID para usarlo en la función SEO - usar un ID único para cada página
    $transient_key = 'exam_item_' . md5($item_id . ($is_solution ? '_solution' : '_exercise'));
    set_transient($transient_key, [
        'id' => $item_id,
        'is_solution' => $is_solution,
        'tipus' => $atts['tipus'] // Guardar también el tipus para usarlo en SEO
    ], 5 * MINUTE_IN_SECONDS);

    // Llamada a la API con el tipus proporcionado en el shortcode
    $data = ["tipus" => $atts['tipus'], 'id' => $item_id];
    $base_url = 'https://formaciomiro-cercador-api-ne-prd-dbebg8bnemhte3f9.northeurope-01.azurewebsites.net';
    $endpoint = '/detalls';
    $response = send_data_to_api($data, $base_url, $endpoint);

    if (isset($response['error'])) {
        return '<div class="error-message">' . esc_html($response['error']) . '</div>';
    }

    // Determinar si es un examen
    $is_exam = ($atts['tipus'] === 'examen');

    // Generate the meta title format that will be used for both H1 and meta title
    $meta_format = '';
    if ($is_exam) {
        $meta_format = $is_solution
            ? "Solució de l'examen %s de %s de %s"
            : "Examen %s de %s de %s";
    } else {
        $meta_format = $is_solution
            ? "Solució de l'exercici %s de la %s de %s de %s"
            : "Exercici %s de la %s de %s de %s";
    }

    // Formatear el título según el tipo
    $page_title = '';
    if ($is_exam) {
        $page_title = sprintf(
            $meta_format,
            $response['assignatura'],
            $response['convocatoria'],
            $response['any']
        );
    } else {
        $page_title = sprintf(
            $meta_format,
            $response['assignatura'],
            $response['tipus_prova'],
            $response['convocatoria'],
            $response['any']
        );
    }

    // Trigger SEO setup immediately with the response data
    exam_item_seo_setup_with_data($response, $is_solution, $transient_key, $is_exam);

    // Construcción del HTML
    $output = '<div class="exercise-container">';

    // h1 - now using the same format as the meta title
    $output .= '<div class="exercise-header">';
    $output .= '<h1>' . esc_html($page_title) . '</h1>';
    // $output .= '<div class="debug-info">';
    // $output .= '<pre>' . print_r($response, true) . '</pre>';
    // $output .= '</div>';
    $output .= '</div>';

    $clean_item_id = sanitize_title($item_id);

    // Botón de acción - Modificado para mostrar también en exámenes
    $output .= '<div class="exercise-buttons">';
    if ($is_solution) {
        $output .= sprintf(
            '<a href="%s" class="button-primary-exams">Veure %s</a>',
            esc_url(home_url($is_exam ? "/examenes/{$clean_item_id}" : "/exercicis-selectivitat/exercici/{$clean_item_id}")),
            $is_exam ? "Examen" : "Exercici"
        );
    } else {
        $output .= sprintf(
            '<a href="%s" class="button-primary-exams">Veure Solució</a>',
            esc_url(home_url($is_exam ? "/examenes/solucio/{$clean_item_id}" : "/exercicis-selectivitat/solucio/{$clean_item_id}"))
        );
    }
    $output .= '</div>';

    // Visor de PDF
    $output .= '<div class="pdf-container">';
    $pdf_url = $is_solution ? $response['url_solucio'] : $response['url_pregunta'];
    $output .= sprintf(
        '<iframe src="%s" width="100%%" height="800px" frameborder="0"></iframe>',
        esc_url($pdf_url)
    );
    $output .= '</div>';

    // Descripción
    if (!$is_solution && !empty($response['descripcio'])) {
        $output .= '<div class="exercise-description">';
        $output .= '<p>' . nl2br(esc_html($response['descripcio'])) . '</p>';
        $output .= '</div>';
    }

    // Add structured data for SEO
    $output .= generate_structured_data($response, $is_solution, $is_exam);

    $output .= '</div>';

    return $output;
}

/**
 * Generates structured data for better SEO
 */
function generate_structured_data($response, $is_solution, $is_exam = false)
{
    $type = '';
    if ($is_exam) {
        $type = $is_solution ? 'ExamSolution' : 'Exam';
    } else {
        $type = $is_solution ? 'Solution' : 'Exercise';
    }

    $name = '';
    if ($is_exam) {
        $name = sprintf(
            '%s - %s %s',
            $response['assignatura'],
            $response['convocatoria'],
            $response['any']
        );
    } else {
        $name = sprintf(
            '%s - %s %s %s',
            $response['assignatura'],
            $response['tipus_prova'],
            $response['convocatoria'],
            $response['any']
        );
    }

    $description = !empty($response['descripcio'])
        ? $response['descripcio']
        : sprintf(
            '%s de %s de %s %s',
            $is_exam ? ($is_solution ? 'Solució de l\'examen' : 'Examen') : ($is_solution ? 'Solució de l\'exercici' : 'Exercici'),
            $response['assignatura'],
            $response['tipus_prova'],
            $response['any']
        );

    $structured_data = [
        '@context' => 'https://schema.org',
        '@type' => 'LearningResource',
        'name' => $name,
        'description' => $description,
        'learningResourceType' => $type,
        'educationalLevel' => 'Selectivitat',
        'about' => [
            '@type' => 'Thing',
            'name' => $response['assignatura']
        ],
        'audience' => [
            '@type' => 'EducationalAudience',
            'educationalRole' => 'student'
        ]
    ];

    return '<script type="application/ld+json">' . json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * Setup SEO with data already available
 */
function exam_item_seo_setup_with_data($response, $is_solution, $transient_key, $is_exam = false)
{
    // Asegurarse de que $is_exam se está pasando correctamente
    $content_type = $is_exam ? 'examen' : 'exercici';
    $content_type_singular = $is_exam ? 'examen' : 'exercici';

    // Generar meta título y descripción
    $meta_format = '';
    if ($is_exam) {
        $meta_format = $is_solution
            ? "Solució de l'examen %s de %s de %s"
            : "Examen %s de %s de %s";
    } else {
        $meta_format = $is_solution
            ? "Solució de l'exercici %s de la %s de %s de %s"
            : "Exercici %s de la %s de %s de %s";
    }

    // Modificar las meta tags para usar el tipo de contenido correcto
    $meta_title = sprintf(
        '%s %s de %s %s',
        $is_solution ? 'Solució de l\'' : '',
        $content_type_singular,
        $response['assignatura'],
        $response['any']
    );

    // Add tematica to description if available
    $meta_description = '';
    if (!empty($response['descripcio'])) {
        $meta_description = $response['descripcio'];
    } else {
        if ($is_exam) {
            $meta_description = sprintf(
                "%s de %s de %s. Consulta %s en línea.",
                $is_solution ? "Solució de l'examen" : "Examen",
                $response['assignatura'],
                $response['any'],
                $is_solution ? "la solució" : "l'examen"
            );
        } else {
            $meta_description = sprintf(
                "%s de %s de %s %s. Consulta %s en línea.",
                $is_solution ? "Solució de l'exercici" : "Exercici",
                $response['assignatura'],
                $response['tipus_prova'],
                $response['any'],
                $is_solution ? "la solució" : "l'exercici"
            );
        }

        if (!empty($response['tematica'])) {
            $meta_description .= sprintf(" Temàtica: %s.", $response['tematica']);
        }
    }

    // Limit description length for SEO best practices
    $meta_description = substr($meta_description, 0, 160);

    // Guardar en opciones con prefijo único para esta página específica
    update_option('_exam_item_seo_title_' . $transient_key, $meta_title, false);
    update_option('_exam_item_seo_description_' . $transient_key, $meta_description, false);
}

// Función para manejar SEO - mejorada para ejecutarse solo en las páginas específicas
function exam_item_seo_setup()
{
    // Verificar que estamos en una página de ejercicio o solución
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $is_exercise_page = (strpos($current_path, '/exercici/') !== false);
    $is_solution_page = (strpos($current_path, '/solucio/') !== false);
    $is_exam_page = (strpos($current_path, '/examenes/') !== false);

    // Solo continuar si estamos en una de estas páginas específicas
    if (!($is_exercise_page || $is_solution_page || $is_exam_page) || !is_page()) {
        return;
    }

    // Extract ID from URL path
    $path_parts = explode('/', trim($current_path, '/'));
    $item_id = end($path_parts);

    if (empty($item_id)) {
        return;
    }

    $is_solution = $is_solution_page;
    $is_exam = $is_exam_page;

    // Create a unique key for this page
    $transient_key = 'exam_item_' . md5($item_id . ($is_solution ? '_solution' : '_exercise'));

    // Check if we already have the data
    $stored_data = get_transient($transient_key);

    // If we have stored data, use it for SEO setup
    if ($stored_data) {
        // Si tenemos datos almacenados, usarlos para configurar el SEO
        if (isset($stored_data['tipus'])) {
            // Llamada a la API con el tipus almacenado
            $tipus = $stored_data['tipus'];
            $data = ["tipus" => $tipus, 'id' => $item_id];
            $base_url = 'https://formaciomiro-cercador-api-ne-prd-dbebg8bnemhte3f9.northeurope-01.azurewebsites.net';
            $endpoint = '/detalls';
            $response = send_data_to_api($data, $base_url, $endpoint);

            if (!isset($response['error'])) {
                // Setup SEO with the response data
                exam_item_seo_setup_with_data($response, $is_solution, $transient_key, ($tipus === 'examen'));
            }
        }
        return;
    }

    // Si no hay datos almacenados, proceder como antes
    $tipus = $is_exam ? "examens" : "pregunta";
    $data = ["tipus" => $tipus, 'id' => $item_id];
    $base_url = 'https://formaciomiro-cercador-api-ne-prd-dbebg8bnemhte3f9.northeurope-01.azurewebsites.net';
    $endpoint = '/detalls';
    $response = send_data_to_api($data, $base_url, $endpoint);

    if (isset($response['error'])) {
        return;
    }

    // Setup SEO with the response data
    exam_item_seo_setup_with_data($response, $is_solution, $transient_key, $is_exam);
}

// Modificar los filtros para verificar la página actual antes de cambiar títulos y descripciones
add_filter('rank_math/frontend/title', function ($title) {
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $is_exercise_page = (strpos($current_path, '/exercici/') !== false);
    $is_solution_page = (strpos($current_path, '/solucio/') !== false);
    $is_exam_page = (strpos($current_path, '/examenes/') !== false);

    if (!($is_exercise_page || $is_solution_page || $is_exam_page)) {
        return $title;
    }

    // Extract ID from URL path
    $path_parts = explode('/', trim($current_path, '/'));
    $item_id = end($path_parts);

    if (empty($item_id)) {
        return $title;
    }

    // Create a unique key for this page
    $transient_key = 'exam_item_' . md5($item_id . ($is_solution_page ? '_solution' : '_exercise'));

    // Get the custom title for this specific page
    $custom_title = get_option('_exam_item_seo_title_' . $transient_key, '');
    return !empty($custom_title) ? esc_html($custom_title) : $title;
}, 100);

add_filter('rank_math/frontend/description', function ($description) {
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $is_exercise_page = (strpos($current_path, '/exercici/') !== false);
    $is_solution_page = (strpos($current_path, '/solucio/') !== false);
    $is_exam_page = (strpos($current_path, '/examenes/') !== false);

    if (!($is_exercise_page || $is_solution_page || $is_exam_page)) {
        return $description;
    }

    // Extract ID from URL path
    $path_parts = explode('/', trim($current_path, '/'));
    $item_id = end($path_parts);

    if (empty($item_id)) {
        return $description;
    }

    // Create a unique key for this page
    $transient_key = 'exam_item_' . md5($item_id . ($is_solution_page ? '_solution' : '_exercise'));

    // Get the custom description for this specific page
    $custom_desc = get_option('_exam_item_seo_description_' . $transient_key, '');
    return !empty($custom_desc) ? esc_html($custom_desc) : $description;
}, 100);

// Inyectar metadatos manualmente en wp_head solo en páginas específicas
add_action('wp_head', function () {
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $is_exercise_page = (strpos($current_path, '/exercici/') !== false);
    $is_solution_page = (strpos($current_path, '/solucio/') !== false);
    $is_exam_page = (strpos($current_path, '/examenes/') !== false);

    if (!($is_exercise_page || $is_solution_page || $is_exam_page)) {
        return;
    }

    // Extract ID from URL path
    $path_parts = explode('/', trim($current_path, '/'));
    $item_id = end($path_parts);

    if (empty($item_id)) {
        return;
    }

    // Create a unique key for this page
    $transient_key = 'exam_item_' . md5($item_id . ($is_solution_page ? '_solution' : '_exercise'));

    $meta_title = get_option('_exam_item_seo_title_' . $transient_key, '');
    $meta_description = get_option('_exam_item_seo_description_' . $transient_key, '');

    // Add canonical URL to prevent duplicate content issues
    $canonical_url = home_url($current_path);
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";

    if (!empty($meta_title)) {
        echo '<meta name="title" content="' . esc_attr($meta_title) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($meta_title) . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($meta_title) . '">' . "\n";
    }
    if (!empty($meta_description)) {
        echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
    }

    // Add Open Graph type
    echo '<meta property="og:type" content="article">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical_url) . '">' . "\n";
}, 1);

// Registrar el shortcode y hooks
add_shortcode('item_view', 'item_view_shortcode');
add_action('wp', 'exam_item_seo_setup', 0);
