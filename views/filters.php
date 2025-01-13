<?php

/**
 * Displays a filter form with various selection options.
 *
 * @param array $atts An associative array containing the selected values for the filters.
 * @return string The HTML form for filtering.
 */
function mostrar_filtros($atts)
{
    // Get options category to config JSON in '{upload_basedir}/wp-content/uploads/examens/config.json'
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
        'juny' => 'June',
        'setembre' => 'September'
    ];

    // Community 
    $opciones_comunitat = ['catalunya' => 'Catalonia'];

    // Types of test
    $opciones_tipus_prova = ['selectivitat' => 'Selectivity'];
    ob_start();
?>
    <form method="GET" class="filters-wrapper" id="filters-form">
        <select name="assignatura">
            <option value="">-- Asignatura --</option>
            <?= crear_opciones($opciones_assignatura, $atts['assignatura']); ?>
        </select>
        <select name="tematica">
            <option value="">-- Temática --</option>
            <?= crear_opciones($opciones_tematica[$atts['assignatura']] ?? [], $atts['tematica']); ?>
        </select>
        <select name="any">
            <option value="">-- Año --</option>
            <?= crear_opciones($opciones_any, $atts['any']); ?>
        </select>
        <select name="convocatoria">
            <option value="">-- Convocatoria --</option>
            <?= crear_opciones($opciones_convocatoria, $atts['convocatoria']); ?>
        </select>
        <select name="comunitat">
            <option value="">-- Comunidad --</option>
            <?= crear_opciones($opciones_comunitat, $atts['comunitat']); ?>
        </select>
        <select name="tipus_prova">
            <option value="">-- Tipo de prueba --</option>
            <?= crear_opciones($opciones_tipus_prova, $atts['tipus_prova']); ?>
        </select>
        <button type="submit" class="submit-btn">Filtrar</button>
    </form>
    <script>
        // Use AJAX to load dynamic options.
        document.addEventListener('DOMContentLoaded', function() {
            const asignaturaSelect = document.querySelector('select[name="assignatura"]');
            const tematicaSelect = document.querySelector('select[name="tematica"]');

            asignaturaSelect.addEventListener('change', function() {
                const selectedAssignatura = this.value;
                const tematicas = <?= json_encode($opciones_tematica); ?>;

                tematicaSelect.innerHTML = '';

                if (tematicas[selectedAssignatura]) {
                    tematicas[selectedAssignatura].forEach(function(tematica) {
                        const option = document.createElement('option');
                        option.value = tematica;
                        option.textContent = tematica;
                        tematicaSelect.appendChild(option);
                    });
                }
            });
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
    $resultado = ''; // Initialize string
    foreach ($opciones as $key => $value) {
        $selected = ($key === $seleccionado) ? 'selected' : ''; // Check if the option is selected
        $resultado .= "<option value=\"" . htmlspecialchars($key) . "\" $selected>" . htmlspecialchars($value) . "</option>";
    }
    return $resultado;
}
