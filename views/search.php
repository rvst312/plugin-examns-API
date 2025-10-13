<?php

/**
 * Displays a search form for paraules_clau without autocomplete functionality.
 *
 * @param array $atts An associative array containing the selected values for the filters.
 * @return string The HTML search form.
 */
function mostrar_buscador_tematicas($atts)
{
    // Start output buffer
    ob_start();
?>
    <div class="search-tematicas-container">
        <!-- If resultat is equal to exam not visualizate search form-->
        <?php if (($atts['tipus_cerca'] ?? '') !== 'examen'): ?>
            <form method="GET" class="search-tematicas-form">
                <?php
                // Preserve other GET parameters that might exist
                foreach ($_GET as $key => $value) {
                    if (!in_array($key, ['paraules_clau'])) {
                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                    }
                }
                ?>
                <div class="search-input-container">
                    <input type="text"
                        name="paraules_clau"
                        id="search-tematicas"
                        placeholder="Buscar per paraules clau..."
                        value="<?php echo htmlspecialchars($_GET['paraules_clau'] ?? ''); ?>"
                        autocomplete="off">
                    <button type="submit" style="margin-left:20px" class="button-primary-exams search-button">
                        <span>Buscar</span>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
