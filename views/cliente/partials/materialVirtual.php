<?php

require_once __DIR__ . '/../../../config/helpers.php';

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('leccionEstadoInfo')) {
    function leccionEstadoInfo(?string $estado): array
    {
        $e = strtolower(str_replace(' ', '_', trim((string) $estado)));

        return match ($e) {
            'completado' => ['label' => 'Completado', 'class' => 'fp-badge fp-badge-ok'],
            'en_progreso' => ['label' => 'En progreso', 'class' => 'fp-badge'],
            'no_iniciado' => ['label' => 'No iniciado', 'class' => 'fp-badge fp-badge-pending'],
            default => ['label' => $e !== '' ? ucfirst(str_replace('_', ' ', $e)) : 'Pendiente', 'class' => 'fp-badge fp-badge-pending'],
        };
    }
}

$video = $video ?? [];
$clienteController = $clienteController ?? '../../controllers/cliente/contenidoVirtualController.php';
$videoId = (int) ($video['id'] ?? $video['id_video'] ?? 0);
$urlMedia = $video['url_video'] ?? '';
$urlPublica = urlPublicaMaterialVirtual($urlMedia, $videoId);
$embed = embedUrlVideo($urlMedia);
$tipo = strtoupper($video['tipo_media'] ?? 'VIDEO');
$estadoInfo = leccionEstadoInfo($video['estado_progreso'] ?? 'pendiente');
$estado = strtolower(str_replace(' ', '_', (string) ($video['estado_progreso'] ?? 'pendiente')));

?>
<article class="fp-leccion-card leccion-card">
    <div class="fp-leccion-header leccion-header">
        <span class="fp-leccion-orden leccion-orden">#<?= e($video['orden'] ?? '—') ?></span>
        <span class="<?= e($estadoInfo['class']) ?>"><?= e($estadoInfo['label']) ?></span>
    </div>
    <h4><?= e($video['titulo'] ?? 'Lección') ?></h4>
    <?php if (!empty($video['descripcion'])): ?>
        <p class="fp-leccion-desc leccion-desc"><?= nl2br(e($video['descripcion'])) ?></p>
    <?php endif; ?>

    <div class="leccion-media">
        <?php if ($tipo === 'ENLACE' && $embed): ?>
            <div class="fp-leccion-embed leccion-embed">
                <iframe src="<?= e($embed) ?>" allowfullscreen loading="lazy" title="<?= e($video['titulo'] ?? '') ?>"></iframe>
            </div>
        <?php elseif ($tipo === 'IMAGEN' && $urlPublica): ?>
            <a href="<?= e($urlPublica) ?>" target="_blank" rel="noopener">
                <img src="<?= e($urlPublica) ?>" alt="<?= e($video['titulo'] ?? '') ?>" class="fp-leccion-img leccion-img">
            </a>
        <?php elseif (($tipo === 'VIDEO' || $tipo === '') && $urlPublica): ?>
            <video controls class="fp-leccion-video leccion-video" preload="metadata">
                <source src="<?= e($urlPublica) ?>">
            </video>
        <?php elseif (esUrlExternaVideo($urlMedia)): ?>
            <div class="fp-leccion-embed leccion-embed">
                <iframe src="<?= e($embed ?: $urlMedia) ?>" allowfullscreen loading="lazy"></iframe>
            </div>
        <?php elseif ($urlPublica): ?>
            <div class="fp-leccion-actions">
                <a class="btn fp-btn" href="<?= e($urlPublica) ?>" target="_blank" rel="noopener">Abrir material</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="fp-leccion-actions leccion-actions">
        <?php if ($estado !== 'completado'): ?>
            <a class="btn fp-btn fp-perfil-submit-mint" href="<?= e($clienteController) ?>?accion=marcarVisto&video_id=<?= e($videoId) ?>">
                Marcar como completado
            </a>
        <?php else: ?>
            <span class="fp-leccion-done leccion-done">✓ Completado</span>
        <?php endif; ?>
    </div>
</article>
