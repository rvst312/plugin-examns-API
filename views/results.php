<?php

function mostrar_resultados($response) {
    // Error handler
    if (isset($response['error'])) {
        return htmlspecialchars($response['error']); // Escapado por seguridad
    }

    // Capturar el contenido del HTML dinámico
    ob_start();
    ?>
    <ul>
        <?php foreach ($response as $item): ?>
            <li>
                <img src="<?= htmlspecialchars($item['url_miniatura']); ?>" alt="Miniatura">
                <h3><?= htmlspecialchars($item['descripcio']); ?></h3>
                <p>Assignatura: <?= htmlspecialchars($item['assignatura']); ?></p>
                <p>Any: <?= htmlspecialchars($item['any']); ?></p>
                <p>Convocatoria: <?= htmlspecialchars($item['convocatoria']); ?></p>
                <a href="<?= htmlspecialchars($item['url_pregunta']); ?>" class="button">Examen</a>
                <a href="<?= htmlspecialchars($item['url_solucio']); ?>" class="button">Solución</a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean(); // Retorna el HTML generado
}