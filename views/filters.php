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

    // Process JSON to generate options
    foreach ($json_data as $tipo_prova) {
        foreach ($tipo_prova['comunitats'] as $comunitat) {
            $opciones_comunitat[$comunitat['comunitat']] = $comunitat['comunitat'];
            foreach ($comunitat['assignatures'] as $assignatura) {
                $opciones_assignatura[$assignatura['assignatura']] = $assignatura['assignatura'];
                $opciones_tematica[$assignatura['assignatura']] = $assignatura['tematiques'];
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

    // Community 
    $opciones_comunitat = ['catalunya' => 'Catalunya'];

    // Types of test
    $opciones_tipus_prova = ['selectivitat' => 'Selectivitat'];
    ob_start();
?>
    <?php
    // Display active filters
    $active_filters = array_filter($atts);
    if (!empty($active_filters)) {
        echo '<div class="active-filters">';
        echo '<strong>Active Filters:</strong> ';
        foreach ($active_filters as $key => $value) {
            echo '<span class="filter-tag">' . htmlspecialchars($key) . ': ' . htmlspecialchars($value) . '</span> ';
        }
        echo '</div>';
    }
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
        <select name="assignatura">
            <option value="">-- Assignatura --</option>
            <?= crear_opciones($opciones_assignatura, $atts['assignatura']); ?>
        </select>
        <select name="tematica">
            <option value="">-- Temàtic --</option>
            <?= crear_opciones($opciones_tematica[$atts['assignatura']] ?? [], $atts['tematica']); ?>
        </select>
        <select name="any">
            <option value="">-- Any --</option>
            <?= crear_opciones($opciones_any, $atts['any']); ?>
        </select>
        <select name="convocatoria">
            <option value="">-- Convocatòria --</option>
            <?= crear_opciones($opciones_convocatoria, $atts['convocatoria']); ?>
        </select>
        <select name="comunitat">
            <option value="">-- Comunitat --</option>
            <?= crear_opciones($opciones_comunitat, $atts['comunitat']); ?>
        </select>
        <select name="tipus_prova">
            <option value="">-- Tipus de prova --</option>
            <?= crear_opciones($opciones_tipus_prova, $atts['tipus_prova']); ?>
        </select>
        <div class="filter-buttons">
            <button type="submit" class="button-primary-exams">Filtrar</button>
            <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="button-secondary-exams">Esborrar filtres</a>
        </div>
    </form>

    <style>
        .active-filters {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .filter-tag {
            display: inline-block;
            padding: 3px 8px;
            margin: 2px;
            background: #e0e0e0;
            border-radius: 3px;
        }
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        .button-secondary-exams {
            display: inline-block;
            padding: 6px 12px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .button-secondary-exams:hover {
            background: #e0e0e0;
        }
    </style>
    <script>
        // Use AJAX to load dynamic options.
        document.addEventListener('DOMContentLoaded', function() {
            const asignaturaSelect = document.querySelector('select[name="assignatura"]');
            const tematicaSelect = document.querySelector('select[name="tematica"]');
            const currentTematica = '<?= $atts['tematica'] ?? ''; ?>';

            // Initial load of tematicas if assignatura is selected
            if (asignaturaSelect.value) {
                updateTematicas(asignaturaSelect.value, currentTematica);
            }

            asignaturaSelect.addEventListener('change', function() {
                updateTematicas(this.value, '');
            });

            function updateTematicas(selectedAssignatura, selectedTematica) {
                const tematicas = <?= json_encode($opciones_tematica); ?>;
                
                tematicaSelect.innerHTML = '<option value="">-- Temàtic --</option>';

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
