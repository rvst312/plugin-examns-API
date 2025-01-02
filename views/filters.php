<?php
/**
 * Displays a filter form with various selection options.
 *
 * @param array $atts An associative array containing the selected values for the filters.
 * @return string The HTML form for filtering.
 */
function mostrar_filtros($atts) {
    $opciones_comunitat = ['catalunya' => 'Catalunya']; 
    $opciones_tipus_prova = ['selectivitat' => 'Selectivitat']; 
    $opciones_assignatura = [
        'química' => 'Química', 
        'tecnologia' => 'Tecnologia',
        'angles' => 'Anglès',
        'biologia' => 'Biologia',
        'castella' => 'Castellà',
        'català' => 'Català',
        'ctma' => 'CTMA',
        'dibuix_artistic' => 'Dibuix Artístic',
        'dibuix_tecnic' => 'Dibuix Tècnic',
        'economia_d_empresa' => 'Economia d\'Empresa',
        'filosofia' => 'Filosofia',
        'fisica' => 'Física',
        'geografia' => 'Geografia',
        'grec' => 'Grec',
        'historia' => 'Història',
        'literatura_castellana' => 'Literatura Castellana',
        'literatura_catalana' => 'Literatura Catalana',
        'lati' => 'Llatí',
        'matematiques_aplicades' => 'Matemàtiques Aplicades',
        'matematiques_cientific' => 'Matemàtiques Científic'
    ]; 
    $opciones_convocatoria = ['juny' => 'Juny', 'setembre' => 'Setembre']; 

    // Captura la salida en un buffer
    ob_start();
    ?>
    <form method="GET" class="filters-wrapper">
        <select name="comunitat">
            <?= crear_opciones($opciones_comunitat, $atts['comunitat']); ?>
        </select>
        <select name="tipus_prova">
            <?= crear_opciones($opciones_tipus_prova, $atts['tipus_prova']); ?>
        </select>
        <select name="assignatura">
            <?= crear_opciones($opciones_assignatura, $atts['assignatura']); ?>
        </select>
        <select name="convocatoria">
            <?= crear_opciones($opciones_convocatoria, $atts['convocatoria']); ?>
        </select>
        <input type="number" name="any" value="<?= htmlspecialchars($atts['any']); ?>">
        <input type="submit" class="submit-btn" value="Filtrar">
    </form>
    <?php
    return ob_get_clean(); // Retorna el contenido capturado en el buffer
}

/**
 * Generates the options for a <select> element with the active selection.
 *
 * @param array $opciones An associative array of options for the select element.
 * @param string $seleccionado The currently selected option.
 * @return string The HTML options for the select element.
 */
function crear_opciones($opciones, $seleccionado) {
    $resultado = ''; // Initialize string
    foreach ($opciones as $key => $value) {
        $selected = ($key === $seleccionado) ? 'selected' : ''; // Check if the option is selected
        $resultado .= "<option value=\"" . htmlspecialchars($key) . "\" $selected>" . htmlspecialchars($value) . "</option>";
    }
    return $resultado;
}