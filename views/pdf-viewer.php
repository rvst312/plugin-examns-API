<?php

function pdf_viewer_shortcode($atts)
{
    $pdf_ID = $atts['id'];
    ob_start();

    // HAacer la peticon al nuevo endpoint desde functions.php
    
?>
    <div class="pdf-viewer-container">
        <iframe src="<?= esc_url($pdf_url); ?>"
            class="pdf-frame"
            type="application/pdf"
            width="100%"
            height="800px"
            frameborder="0">
            <p>Tu navegador no puede mostrar PDFs directamente.
                <a href="<?= esc_url($pdf_url); ?>" target="_blank">Descargar PDF</a>
            </p>
        </iframe>
    </div>
<?php
    return ob_get_clean();
}

add_shortcode('pdf_viewer', 'pdf_viewer_shortcode');
