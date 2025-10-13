<?php
function render_seo_buttons($response = [])
{
    try {
        // Check if we have any results
        $is_exam = (
            isset($response['resultats'][0]['tipus_resultat']) &&
            $response['resultats'][0]['tipus_resultat'] === 'examen'
        );
    
        $base_path = $is_exam ? 'examens-de-selectivitat' : 'exercicis-selectivitat';
        $url_subject = $is_exam ? 'asignatura' : 'assignatura';
        $url_year = $is_exam? 'year' : 'any';

        // Get options category to config JSON
        $upload_dir = wp_upload_dir();
        if (is_wp_error($upload_dir)) {
            throw new Exception('Error getting WordPress upload directory');
        }

        $json_file_path = $upload_dir['basedir'] . '/examens/config/configuracio_assignatures.json';
        if (!file_exists($json_file_path)) {
            throw new Exception('Configuration file not found: ' . $json_file_path);
        }

        $json_content = @file_get_contents($json_file_path);
        if ($json_content === false) {
            throw new Exception('Unable to read configuration file');
        }

        $json_data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON configuration: ' . json_last_error_msg());
        }

        // Initialize list save options
        $opciones_assignatura = [];

        // Process JSON to generate options
        if (!empty($json_data) && is_array($json_data)) {
            foreach ($json_data as $tipo_prova) {
                if (!empty($tipo_prova['comunitats']) && is_array($tipo_prova['comunitats'])) {
                    foreach ($tipo_prova['comunitats'] as $comunitat) {
                        if (!empty($comunitat['assignatures']) && is_array($comunitat['assignatures'])) {
                            foreach ($comunitat['assignatures'] as $assignatura) {
                                if (!empty($assignatura['assignatura'])) {
                                    $opciones_assignatura[$assignatura['assignatura']] = $assignatura['assignatura'];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Check if we have any subjects
        if (empty($opciones_assignatura)) {
            throw new Exception('No subjects found in configuration');
        }

        ob_start();
?>
        <div class="exam-filters">
            <h3>
                Buscar per any
            </h3>
            <!-- Year buttons -->
            <div class="year-buttons">
                <?php
                $currentYear = intval(date("Y"));
                // Year buttons section
                for ($year = 2005; $year <= $currentYear; $year++) {
                    printf(
                        '<a href="%s" class="button-secondary-seo">%s</a>',
                        esc_url(site_url("/{$base_path}/{$url_year}/{$year}")),
                        esc_html($year)
                    );
                }
                ?>
            </div>

            <h3>
                Buscar per assignatura
            </h3>
            <!-- Subject buttons -->
            <div class="subject-buttons">
                <?php
                // Subject buttons section
                foreach ($opciones_assignatura as $subject => $name) {
                    $encoded_subject = str_replace(' ', '-', sanitize_title($subject));
                    printf(
                        '<a href="%s" class="button-secondary-seo">%s</a>',
                        esc_url(site_url("/{$base_path}/{$url_subject}/{$encoded_subject}")),
                        esc_html($name)
                    );
                }
                ?>
            </div>
        </div>

<?php
        $output = ob_get_clean();
        if ($output === false) {
            throw new Exception('Error capturing output buffer');
        }
        return $output;
    } catch (Exception $e) {
        error_log('Error in render_seo_buttons: ' . $e->getMessage());
        return '<div class="error-message">Error loading content. Please try again later.</div>';
    }
}
