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

        // Get options from configuration JSON securely
        $json_data = load_examens_config_json();
        if (empty($json_data) || !is_array($json_data)) {
            throw new Exception('Configuration file not found or invalid');
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
