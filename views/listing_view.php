<?php

function listing_view_shortcode($atts = [])
{
    // Default attributes with type validation
    $default_atts = [
        'tipus_cerca' => isset($atts['tipus_cerca']) && in_array($atts['tipus_cerca'], ['pregunta', 'examen']) 
            ? $atts['tipus_cerca'] 
            : 'pregunta',
        'comunitat' => 'catalunya',
        'tipus_prova' => 'selectivitat',
        'assignatura' => null,
        'any' => null,
        'paraules_clau' => null,
        'pagina' => 1
    ];

    // Merge with provided attributes
    $atts = shortcode_atts($default_atts, $atts);

    // Get parameters from URL
    $subject = get_query_var('subject', '');
    $year = get_query_var('year', '');

    // Set the parameters from URL if they exist
    if (!empty($subject)) {
        $decoded_subject = urldecode($subject);
        $atts['assignatura'] = str_replace('-', ' ', $decoded_subject);
    }
    if (!empty($year)) {
        $year = sanitize_text_field($year);
        $atts['any'] = $year;
    }

    if (empty($atts['assignatura']) && empty($atts['any'])) {
        return '<div class="error-message">No subject or year specified</div>';
    }

    // Create a unique key for this listing page
    $listing_key = 'listing_view_' . md5(
        ($atts['assignatura'] ? $atts['assignatura'] : '') . 
        ($atts['any'] ? $atts['any'] : '') . 
        $atts['tipus_prova']
    );
    
    // Setup SEO for this listing page
    listing_seo_setup($atts, $listing_key);

    // API configuration
    $base_url = 'https://formaciomiro-cercador-api-ne-prd-dbebg8bnemhte3f9.northeurope-01.azurewebsites.net';
    $endpoint = '/cerca';

    // Call the API with the filtered attributes
    $response = get_exams_data(
        $base_url,
        $endpoint,
        $atts['tipus_cerca'], // Ensure this is passed to API
        $atts['comunitat'],
        $atts['tipus_prova'],
        $atts['assignatura'],
        null, // convocatoria
        $atts['any'],
        null, // paraules_clau
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

    // Get SEO buttons with proper context
    $seo_context = [
        "tipus_resultat" => $atts['tipus_cerca'],
        'tipus_prova' => $atts['tipus_prova'],
        'assignatura' => $atts['assignatura'],
        'any' => $atts['any']
    ];
    $seo_buttons = render_seo_buttons($seo_context);

    // Add structured data for SEO
    $structured_data = generate_listing_structured_data($atts, $response['num_resultats']);

    // Determine content type based on tipus_cerca
    $is_exam = ($atts['tipus_cerca'] === 'examen');
    $content_type = $is_exam ? 'exàmens' : 'exercicis';
    $content_type_singular = $is_exam ? 'examen' : 'exercici';
    
    // Build the output
    ob_start();
    ?>
    <div class="listing-container">
        <h1 style="text-transform: capitalize;">
            <?php
            if (!empty($atts['assignatura'])) {
                echo $content_type . " de " . esc_html($atts['assignatura']) . " de " . esc_html($atts['tipus_prova']);
            } else {
                echo $content_type . " de " . esc_html($atts['tipus_prova']) . " de " . esc_html($atts['any']);
            }
            ?>
        </h1>
        <p style="margin-bottom:2rem">
            En aquesta pàgina pots consultar els <?php echo $content_type; ?> i les solucions de
            <?php
            if (!empty($atts['tipus_prova'])) {
                echo esc_html($atts['tipus_prova']) . " ";
            }
            if (!empty($atts['assignatura'])) {
                echo esc_html($atts['assignatura']) . " ";
            }
            if (!empty($atts['any'])) {
                echo esc_html($atts['any']) . " ";
            }
            else{
                echo "de totes les proves";
            }
            ?>.
            Tingues en compte que cada assignatura conté <?php echo $content_type; ?> i solucions de diferents temàtiques.
            Si vols consultar els <?php echo $content_type; ?> de Selectivitat per temàtiques t'aconsellem que utilitzis els filtres del
            <a href="/<?php echo $is_exam ? 'examens-de-selectivitat' : 'exercicis-selectivitat'; ?>">cercador d'<?php echo $content_type; ?></a>.
        </p>
        <?php
        echo $items;
        echo $pagination;
        echo $seo_buttons;
        echo $structured_data;
        ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Generates structured data for listing pages
 */
function generate_listing_structured_data($atts, $num_results) {
    $is_exam = ($atts['tipus_cerca'] === 'examen');
    $content_type = $is_exam ? 'examen' : 'exercicis';
    
    $name = '';
    $description = '';
    
    if (!empty($atts['assignatura'])) {
        $name = sprintf('%s de %s de %s', 
            $content_type,
            $atts['assignatura'],
            $atts['tipus_prova']
        );
        
        $description = sprintf('Col·lecció de %d %s de %s de %s. Consulta %s i solucions.', 
            $num_results,
            $content_type,
            $atts['assignatura'],
            $atts['tipus_prova'],
            $content_type
        );
    } else if (!empty($atts['any'])) {
        $name = sprintf('%s de %s de %s', 
            $content_type,
            $atts['tipus_prova'],
            $atts['any']
        );
        
        $description = sprintf('Col·lecció de %d %s de %s de l\'any %s. Consulta %s i solucions.', 
            $num_results,
            $content_type,
            $atts['tipus_prova'],
            $atts['any'],
            $content_type
        );
    }
    
    $structured_data = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $name,
        'description' => $description,
        'about' => [
            '@type' => 'Thing',
            'name' => !empty($atts['assignatura']) ? $atts['assignatura'] : $atts['tipus_prova']
        ],
        'audience' => [
            '@type' => 'EducationalAudience',
            'educationalRole' => 'student'
        ]
    ];
    
    return '<script type="application/ld+json">' . json_encode($structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * Setup SEO for listing pages
 */
function listing_seo_setup($atts, $listing_key) {
    $is_exam = ($atts['tipus_cerca'] === 'examen');
    $content_type = $is_exam ? 'exàmens' : 'exercicis';
    
    // Generate meta title
    $meta_title = '';
    if (!empty($atts['assignatura'])) {
        $meta_title = sprintf('%s de %s de %s', 
            $content_type,
            $atts['assignatura'],
            $atts['tipus_prova']
        );
    } else if (!empty($atts['any'])) {
        $meta_title = sprintf('%s de %s de %s', 
            $content_type,
            $atts['tipus_prova'],
            $atts['any']
        );
    }
    
    // Generate meta description
    $meta_description = '';
    if (!empty($atts['assignatura'])) {
        $meta_description = sprintf('Consulta els %s i solucions de %s de %s. Recursos educatius per preparar els exàmens.', 
            $content_type,
            $atts['assignatura'],
            $atts['tipus_prova']
        );
    } else if (!empty($atts['any'])) {
        $meta_description = sprintf('Consulta els %s i solucions de %s de l\'any %s. Recursos educatius per preparar els exàmens.', 
            $content_type,
            $atts['tipus_prova'],
            $atts['any']
        );
    }
    
    // Limit description length for SEO best practices
    $meta_description = substr($meta_description, 0, 160);
    
    // Save options with unique prefix for this specific page
    update_option('_listing_seo_title_' . $listing_key, $meta_title, false);
    update_option('_listing_seo_description_' . $listing_key, $meta_description, false);
}

// Add filter for title - specific to listing pages
add_filter('rank_math/frontend/title', function($title) {
    // Only run on listing pages
    if (!is_page() || !has_shortcode(get_post()->post_content, 'listing_view')) {
        return $title;
    }
    
    // Get parameters from URL
    $subject = get_query_var('subject', '');
    $year = get_query_var('year', '');
    
    if (empty($subject) && empty($year)) {
        return $title;
    }
    
    // Recreate the attributes
    $assignatura = '';
    if (!empty($subject)) {
        $decoded_subject = urldecode($subject);
        $assignatura = str_replace('-', ' ', $decoded_subject);
    }
    
    $any = '';
    if (!empty($year)) {
        $any = sanitize_text_field($year);
    }
    
    // Create the same key used in the shortcode
    $listing_key = 'listing_view_' . md5(
        ($assignatura ? $assignatura : '') . 
        ($any ? $any : '') . 
        'selectivitat'
    );
    
    // Get the custom title for this specific page
    $custom_title = get_option('_listing_seo_title_' . $listing_key, '');
    return !empty($custom_title) ? esc_html($custom_title) : $title;
}, 99); // Use priority 99 to avoid conflicts with exercise_view filter (100)

// Add filter for description - specific to listing pages
add_filter('rank_math/frontend/description', function($description) {
    // Only run on listing pages with the right URL pattern
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Check if we're on a listing page
    if (strpos($current_path, '/exercicis-selectivitat/assignatura/') === false && 
        strpos($current_path, '/exercicis-selectivitat/any/') === false) {
        return $description;
    }
    
    $path_parts = explode('/', trim($current_path, '/'));
    
    // Find the correct parameters in the URL path
    $assignatura = '';
    $any = '';
    
    foreach ($path_parts as $index => $part) {
        if ($part === 'assignatura' && isset($path_parts[$index + 1])) {
            $assignatura = urldecode($path_parts[$index + 1]);
            $assignatura = str_replace('-', ' ', $assignatura);
        }
        if ($part === 'any' && isset($path_parts[$index + 1])) {
            $any = sanitize_text_field($path_parts[$index + 1]);
        }
    }
    
    if (empty($assignatura) && empty($any)) {
        return $description;
    }
    
    // Create the same key used in the shortcode
    $listing_key = 'listing_view_' . md5(
        ($assignatura ? $assignatura : '') . 
        ($any ? $any : '') . 
        'selectivitat'
    );
    
    // Get the custom description for this specific page
    $custom_desc = get_option('_listing_seo_description_' . $listing_key, '');
    return !empty($custom_desc) ? esc_html($custom_desc) : $description;
}, 99);

// Add meta tags to head for listing pages
add_action('wp_head', function() {
    // Only run on listing pages with the right URL pattern
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Check if we're on a listing page
    if (strpos($current_path, '/exercicis-selectivitat/assignatura/') === false && 
        strpos($current_path, '/exercicis-selectivitat/any/') === false) {
        return;
    }
    
    $path_parts = explode('/', trim($current_path, '/'));
    
    // Find the correct parameters in the URL path
    $assignatura = '';
    $any = '';
    
    foreach ($path_parts as $index => $part) {
        if ($part === 'assignatura' && isset($path_parts[$index + 1])) {
            $assignatura = urldecode($path_parts[$index + 1]);
            $assignatura = str_replace('-', ' ', $assignatura);
        }
        if ($part === 'any' && isset($path_parts[$index + 1])) {
            $any = sanitize_text_field($path_parts[$index + 1]);
        }
    }
    
    if (empty($assignatura) && empty($any)) {
        return;
    }
    
    // Create the same key used in the shortcode
    $listing_key = 'listing_view_' . md5(
        ($assignatura ? $assignatura : '') . 
        ($any ? $any : '') . 
        'selectivitat'
    );
    
    $meta_title = get_option('_listing_seo_title_' . $listing_key, '');
    $meta_description = get_option('_listing_seo_description_' . $listing_key, '');
    
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
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical_url) . '">' . "\n";
}, 2); // Use priority 2 to ensure it runs after exercise_view (1) but before other plugins

add_shortcode('listing_view', 'listing_view_shortcode');
