<?php

require_once __DIR__ . '/../../../config/helpers.php';

$urlComprobante = $urlComprobante ?? ($item['url_comprobante'] ?? null);
$solicitudIdComprobante = $solicitudIdComprobante ?? ($item['id'] ?? $solicitud['id'] ?? null);
$pagoIdComprobante = $pagoIdComprobante ?? ($pago['id'] ?? $pago['id_pago'] ?? null);
$urlPublica = urlPublicaComprobante($urlComprobante, $solicitudIdComprobante ? (int) $solicitudIdComprobante : null, $pagoIdComprobante ? (int) $pagoIdComprobante : null);

if (!$urlPublica): ?>
    <p class="sin-comprobante">Sin comprobante adjunto.</p>
<?php return; endif; ?>

<div class="comprobante-preview" id="detalle-comprobante">
    <?php if (esComprobanteImagen($urlComprobante)): ?>
        <a href="<?= e($urlPublica) ?>" target="_blank" rel="noopener">
            <img src="<?= e($urlPublica) ?>" alt="Comprobante de pago" class="comprobante-img">
        </a>
        <p><a href="<?= e($urlPublica) ?>" target="_blank" rel="noopener" class="btn">Abrir imagen en tamaño completo</a></p>
    <?php elseif (esComprobantePdf($urlComprobante)): ?>
        <iframe src="<?= e($urlPublica) ?>" class="comprobante-pdf" title="Comprobante PDF"></iframe>
        <p><a href="<?= e($urlPublica) ?>" target="_blank" rel="noopener" class="btn">Descargar / ver PDF</a></p>
    <?php else: ?>
        <p><a href="<?= e($urlPublica) ?>" target="_blank" rel="noopener" class="btn">Ver archivo del comprobante</a></p>
    <?php endif; ?>
</div>
