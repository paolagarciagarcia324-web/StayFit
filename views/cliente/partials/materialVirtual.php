<?php

require_once __DIR__ . '/../../../config/helpers.php';

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$video = $video ?? [];
$clienteController = $clienteController ?? '../../controller/cliente/contenidoVirtualController.php';
$videoId = (int) ($video['id'] ?? $video['id_video'] ?? 0);
$urlMedia = $video['url_video'] ?? '';
$urlPublica = urlPublicaMaterialVirtual($urlMedia, $videoId);
$embed = embedUrlVideo($urlMedia);
$tipo = strtoupper($video['tipo_media'] ?? 'VIDEO');
$estado = strtolower($video['estado_progreso'] ?? 'pendiente');
$estadoLabel = ['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completado' => 'Completado'][$estado] ?? $estado;

?>
<article class="leccion-card">
    <div class="leccion-header">
        <span class="leccion-orden">#<?= e($video['orden'] ?? '') ?></span>
        <span class="leccion-badge leccion-badge--<?= e($estado) ?>"><?= e($estadoLabel) ?></span>
    </div>
    <h4><?= e($video['titulo'] ?? 'Lección') ?></h4>
    <?php if (!empty($video['descripcion'])): ?>
        <p class="leccion-desc"><?= nl2br(e($video['descripcion'])) ?></p>
    <?php endif; ?>

    <div class="leccion-media">
        <?php if ($tipo === 'ENLACE' && $embed): ?>
            <div class="leccion-embed">
                <iframe src="<?= e($embed) ?>" allowfullscreen loading="lazy" title="<?= e($video['titulo'] ?? '') ?>"></iframe>
            </div>
        <?php elseif ($tipo === 'IMAGEN' && $urlPublica): ?>
            <a href="<?= e($urlPublica) ?>" target="_blank" rel="noopener">
                <img src="<?= e($urlPublica) ?>" alt="<?= e($video['titulo'] ?? '') ?>" class="leccion-img">
            </a>
        <?php elseif (($tipo === 'VIDEO' || $tipo === '') && $urlPublica): ?>
            <video controls class="leccion-video" preload="metadata">
                <source src="<?= e($urlPublica) ?>">
            </video>
        <?php elseif (esUrlExternaVideo($urlMedia)): ?>
            <div class="leccion-embed">
                <iframe src="<?= e($embed ?: $urlMedia) ?>" allowfullscreen loading="lazy"></iframe>
            </div>
        <?php elseif ($urlPublica): ?>
            <a class="btn" href="<?= e($urlPublica) ?>" target="_blank">Abrir material</a>
        <?php endif; ?>
    </div>

    <?php if ($estado !== 'completado'): ?>
        <a class="btn btn-green" href="<?= e($clienteController) ?>?accion=marcarVisto&video_id=<?= e($videoId) ?>">
            Marcar como completado
        </a>
    <?php else: ?>
        <span class="leccion-done">Completado</span>
    <?php endif; ?>
</article>
