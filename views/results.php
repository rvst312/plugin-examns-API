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
        <?php if (isset($response['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($response['error']); ?></div>
        <?php elseif (isset($response['resultats']) && is_array($response['resultats'])): ?>
            <?php foreach ($response['resultats'] as $item): ?>
                <div class="grid-item">
                    <img src="<?= htmlspecialchars($item['url_miniatura']); ?>"
                        alt="Miniatura"
                        loading="lazy"
                        onload="this.style.opacity='1'">
                    <p style="text-align: center;">
                        <span class="category"><?= htmlspecialchars($item['tipus_prova']); ?></span>
                        <span class="theme"><?= htmlspecialchars($item['comunitat']); ?></span><br />
                        <span class="subject"><?= htmlspecialchars($item['assignatura']); ?></span>
                        <span class="convocatoria"><?= htmlspecialchars($item['convocatoria']); ?></span>
                        <span class="year"><?= htmlspecialchars($item['any']); ?></span>
                    </p>
                    <div class="buttons-item">
                        <a class="button-secondary-exams"
                            href="<?= esc_url(home_url('/ejercici/' . $item['id'])); ?>"
                            target="_blank">
                            Veure Exercici
                        </a>
                        <a class="button-secondary-exams"
                            href="<?= esc_url(home_url('/solucio/' . $item['id'])); ?>"
                            target="_blank">
                            Veure Solució
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">No se encontraron resultados :(</div>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}
