<?php
/**
 * Displays a filter form with various selection options.
 *
 * @param array $atts An associative array containing the selected values for the filters.
 * @return string The HTML form for filtering.
 */
function mostrar_filtros($atts)
{
    // Get options category to config JSON.
    // Example: {upload_basedir}/wp-content/uploads/examens/config/configuracio_assignatures.json
    $json_file_path = wp_upload_dir()['basedir'] . '/examens/config/configuracio_assignatures.json';
    if (!file_exists($json_file_path)) return 'Error: The configuration file is not found.' . $json_file_path;
    $json_data = json_decode(file_get_contents($json_file_path), true);

    // Initialize list save options: "subject", "theme", "community"
    $opciones_assignatura = [];
    $opciones_tematica = [];
    $opciones_comunitat = [];
    $opciones_tipus_prova = [];
    
    // Process JSON to generate options - now including all tipus_prova
    foreach ($json_data as $tipo_prova) {
        // Add each tipus_prova to the options
        $tipus_prova_name = $tipo_prova['tipus_prova'];
        $opciones_tipus_prova[strtolower($tipus_prova_name)] = $tipus_prova_name;
        
        // Only process the selected tipus_prova for other options
        $selected_tipus_prova = $atts['tipus_prova'] ?? 'selectivitat';
        if (strtolower($tipus_prova_name) === strtolower($selected_tipus_prova)) {
            foreach ($tipo_prova['comunitats'] as $comunitat) {
                $opciones_comunitat[$comunitat['comunitat']] = $comunitat['comunitat'];
                foreach ($comunitat['assignatures'] as $assignatura) {
                    $opciones_assignatura[$assignatura['assignatura']] = $assignatura['assignatura'];
                    $opciones_tematica[$assignatura['assignatura']] = $assignatura['tematiques'];
                }
            }
        }
    }

    // Year
    $opciones_any = [];
    $current_year = date('Y'); // Get current year
    for ($i = 2005; $i <= $current_year; $i++) {
        $opciones_any[(string)$i] = $i; // Generate years in range 2005 - Current year
    }

    // Call
    $opciones_convocatoria = [
        'juny' => 'Juny',
        'setembre' => 'Setembre'
    ];
    
    // If no tipus_prova options were found in the JSON, set default
    if (empty($opciones_tipus_prova)) {
        $opciones_tipus_prova = ['selectivitat' => 'Selectivitat'];
    }
    
    // Community - keep this hardcoded for now
    $opciones_comunitat = ['catalunya' => 'Catalunya'];
    
    ob_start();
?>

    <form method="GET" class="filters-wrapper" id="filters-form">
        <?php
        // Preserve other GET parameters that might exist
        foreach ($_GET as $key => $value) {
            if (!in_array($key, ['assignatura', 'tematica', 'any', 'convocatoria', 'comunitat', 'tipus_prova'])) {
                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
            }
        }
        ?>
        <select name="tipus_prova" class="filter-select" id="tipus_prova_select">
            <option value="">-- Tipus de prova --</option>
            <?= crear_opciones($opciones_tipus_prova, $atts['tipus_prova']); ?>
        </select>
        <select name="assignatura" class="filter-select">
            <option value="">-- Assignatura --</option>
            <?= crear_opciones($opciones_assignatura, $atts['assignatura']); ?>
        </select>
        <?php if (!isset($atts['tipus_cerca']) || $atts['tipus_cerca'] !== 'examen'): ?>
        <select name="tematica" class="filter-select">
            <option value="">-- Temàtica --</option>
            <?= crear_opciones($opciones_tematica[$atts['assignatura']] ?? [], $atts['tematica']); ?>
        </select>
        <?php endif; ?>
        <select name="any" class="filter-select">
            <option value="">-- Any --</option>
            <?= crear_opciones($opciones_any, $atts['any']); ?>
        </select>
        <select name="convocatoria" class="filter-select">
            <option value="">-- Convocatòria --</option>
            <?= crear_opciones($opciones_convocatoria, $atts['convocatoria']); ?>
        </select>
        <select name="comunitat" class="filter-select">
            <option value="">-- Comunitat --</option>
            <?= crear_opciones($opciones_comunitat, $atts['comunitat']); ?>
        </select>
        <div class="filter-buttons">
            <button type="submit" class="button-primary-exams">Filtrar</button>
        </div>
    </form>
    <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="button-secondary-exams" style="margin-bottom:1rem">Esborrar filtres</a>

    <script>
        // Use AJAX to load dynamic options.
        document.addEventListener('DOMContentLoaded', function() {
            const tipusProvaSelect = document.getElementById('tipus_prova_select');
            const asignaturaSelect = document.querySelector('select[name="assignatura"]');
            const tematicaSelect = document.querySelector('select[name="tematica"]');
            const currentTematica = '<?= $atts['tematica'] ?? ''; ?>';

            // Initial load of tematicas if assignatura is selected
            if (asignaturaSelect.value) {
                updateTematicas(asignaturaSelect.value, currentTematica);
            }

            // Add event listener for tipus_prova changes
            tipusProvaSelect.addEventListener('change', function() {
                // Submit the form when tipus_prova changes to reload the page with new options
                this.form.submit();
            });

            asignaturaSelect.addEventListener('change', function() {
                updateTematicas(this.value, '');
            });

            function updateTematicas(selectedAssignatura, selectedTematica) {
                const tematicas = <?= json_encode($opciones_tematica); ?>;
                
                tematicaSelect.innerHTML = '<option value="">-- Temàtica --</option>';

                if (tematicas[selectedAssignatura]) {
                    tematicas[selectedAssignatura].forEach(function(tematica) {
                        const option = document.createElement('option');
                        option.value = tematica;
                        option.textContent = tematica;
                        if (tematica === selectedTematica) {
                            option.selected = true;
                        }
                        tematicaSelect.appendChild(option);
                    });
                }
            }
        });
    </script>
<?php
    return ob_get_clean();
}

/**
 * Generates the options for a <select> element with the active selection.
 *
 * @param array $opciones An associative array of options for the select element.
 * @param string $seleccionado The currently selected option.
 * @return string The HTML options for the select element.
 */
function crear_opciones($opciones, $seleccionado)
{
    $resultado = '';
    foreach ($opciones as $key => $value) {
        // Convert both values to strings for comparison
        $selected = ((string)$key === (string)$seleccionado) ? 'selected' : '';
        $resultado .= "<option value=\"" . htmlspecialchars($key) . "\" $selected>" . htmlspecialchars($value) . "</option>";
    }
    return $resultado;
}