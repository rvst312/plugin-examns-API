<?php

function mostrar_resultados($response)
{
    ob_start();
?>
    <!-- Skeleton loader -->
    <div class="grid-container" id="skeleton-container">
        <?php for ($i = 0; $i < 12; $i++): ?>
            <div class="grid-item" style="background-color:#fff;">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-button"></div>
                <div class="skeleton skeleton-button"></div>
            </div>
        <?php endfor; ?>
    </div>
    <!-- End skeleton loader -->

    <!-- Results -->
    <div class="grid-container hidden" id="results-container">
        <?php if (!empty($response['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($response['error']); ?>
            </div>
        <?php elseif (!empty($response['resultats']) && is_array($response['resultats'])): ?>
            <?php foreach ($response['resultats'] as $item): ?>
                <div class="grid-item">
                    <img src="<?= isset($item['url_miniatura']) ? htmlspecialchars($item['url_miniatura']) : 'placeholder.jpg'; ?>"
                        alt="Miniatura"
                        loading="lazy"
                        onload="this.style.opacity='1'">
                    <p style="text-align: center;">
                        <span class="category"><?= isset($item['tipus_prova']) ? htmlspecialchars($item['tipus_prova']) : 'Desconegut'; ?></span>
                        <!--<span class="theme"><?= isset($item['comunitat']) ? htmlspecialchars($item['comunitat']) : 'Desconegut'; ?></span><br />-->
                        <span class="subject"><?= isset($item['assignatura']) ? htmlspecialchars($item['assignatura']) : 'Desconegut'; ?></span>
                        <span class="convocatoria"><?= isset($item['convocatoria']) ? htmlspecialchars($item['convocatoria']) : 'Desconegut'; ?></span>
                        <span class="year"><?= isset($item['any']) ? htmlspecialchars($item['any']) : 'Desconegut'; ?></span>
                    </p>
                    <div class="buttons-item">
                        <?php if (isset($item['tipus_resultat']) && $item['tipus_resultat'] === 'examen'): ?>
                            <a class="button-secondary-exams"
                               href="<?= esc_url(site_url('/examenes/' . ($item['id'] ?? '#'))); ?>">
                                Examen
                            </a>
                            <a class="button-secondary-exams"
                               href="<?= esc_url(site_url('/examenes/solucio/' . ($item['id'] ?? '#'))); ?>">
                                Solució
                            </a>
                        <?php else: ?>
                            <a class="button-secondary-exams"
                               href="<?= esc_url(site_url('/exercicis-selectivitat/exercici/' . ($item['id'] ?? '#'))); ?>">
                                Exercici
                            </a>
                            <a class="button-secondary-exams"
                               href="<?= esc_url(site_url('/exercicis-selectivitat/solucio/' . ($item['id'] ?? '#'))); ?>">
                                Solució
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <style>
                .grid-container{  grid-template-columns: 1fr;  }</style>
            <div class="no-results">
                No s’han trobat resultats :(
            </div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}