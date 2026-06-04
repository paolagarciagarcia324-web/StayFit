<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('planPrecioPublico')) {
    function planPrecioPublico($precio): string
    {
        return '$' . number_format((float) $precio, 0, ',', '.');
    }
}

if (!function_exists('planModalidadEtiqueta')) {
    function planModalidadEtiqueta(?string $modalidad): string
    {
        $mapa = [
            'VIRTUAL'     => 'Virtual',
            'PRESENCIAL'  => 'Presencial',
            'MIXTO'       => 'Mixto',
            'MIXTA'       => 'Mixto',
        ];

        return $mapa[strtoupper((string) $modalidad)] ?? ucfirst(strtolower((string) $modalidad));
    }
}

if (!function_exists('planCardClase')) {
    function planCardClase(?string $modalidad): string
    {
        switch (strtoupper((string) $modalidad)) {
            case 'VIRTUAL':
                return 'plan-card-premium--activate';
            case 'PRESENCIAL':
                return 'plan-card-premium--premium';
            case 'MIXTO':
            case 'MIXTA':
                return 'plan-card-premium--evolution';
            default:
                return 'plan-card-premium--activate';
        }
    }
}

if (!function_exists('planTierClase')) {
    function planTierClase(?string $modalidad): string
    {
        switch (strtoupper((string) $modalidad)) {
            case 'VIRTUAL':
                return 'plan-tier--activate';
            case 'PRESENCIAL':
                return 'plan-tier--premium';
            case 'MIXTO':
            case 'MIXTA':
                return 'plan-tier--evolution';
            default:
                return 'plan-tier--activate';
        }
    }
}

if (!function_exists('planEsDestacado')) {
    function planEsDestacado(array $plan): bool
    {
        if (!empty($plan['destacado'])) {
            return true;
        }

        return in_array(strtoupper((string) ($plan['modalidad'] ?? '')), ['MIXTO', 'MIXTA'], true);
    }
}

if (!function_exists('planIncluyeLista')) {
    function planIncluyeLista(array $plan): array
    {
        $items = [];

        if (!empty($plan['incluye_entrenamiento'])) {
            $items[] = 'Entrenamiento incluido';
        }
        if (!empty($plan['incluye_nutricion'])) {
            $items[] = 'Plan de nutrición';
        }
        if (!empty($plan['incluye_videos'])) {
            $items[] = 'Contenido virtual';
        }
        if (!empty($plan['incluye_sesiones'])) {
            $items[] = 'Sesiones presenciales';
        }
        if (!empty($plan['incluye_eventos'])) {
            $items[] = 'Eventos especiales';
        }
        if (!empty($plan['requiere_coach'])) {
            $items[] = 'Coach asignado';
        }

        if ($items === [] && !empty($plan['descripcion'])) {
            $items[] = $plan['descripcion'];
        }

        return $items;
    }
}

if (!function_exists('planIconoSvg')) {
    function planIconoSvg(?string $modalidad): string
    {
        switch (strtoupper((string) $modalidad)) {
            case 'PRESENCIAL':
                return '<svg class="plan-icon" viewBox="0 0 48 48" fill="none"><path d="M24 6l4.2 8.5 9.4 1.4-6.8 6.6 1.6 9.3L24 27.8l-8.4 4.4 1.6-9.3-6.8-6.6 9.4-1.4L24 6z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>';
            case 'MIXTO':
            case 'MIXTA':
                return '<svg class="plan-icon" viewBox="0 0 48 48" fill="none"><path d="M24 8v28M16 20l8-8 8 8M16 36h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            default:
                return '<svg class="plan-icon" viewBox="0 0 48 48" fill="none"><circle cx="18" cy="20" r="5" stroke="currentColor" stroke-width="1.4"/><circle cx="30" cy="20" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M12 32c2-4 6-6 12-6s10 2 12 6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>';
        }
    }
}

$planesPublicos = $planesPublicos ?? [];

?>
        <div class="plans-grid plans-grid--catalog">
            <?php if ($planesPublicos === []): ?>
                <p class="plans-lead" style="grid-column: 1 / -1;">No hay planes activos disponibles en este momento.</p>
            <?php endif; ?>

            <?php foreach ($planesPublicos as $i => $plan): ?>
                <?php
                $destacado = planEsDestacado($plan);
                $clases = 'plan-card-premium ' . planCardClase($plan['modalidad'] ?? '');
                if ($destacado) {
                    $clases .= ' is-featured';
                }
                $incluye = planIncluyeLista($plan);
                $planId = (int) ($plan['id_plan'] ?? $plan['id'] ?? 0);
                ?>
                <article class="<?= e($clases) ?>" data-animate style="--i: <?= (int) $i ?>">
                    <?php if ($destacado): ?>
                        <span class="plan-badge">Recomendado</span>
                    <?php endif; ?>

                    <span class="plan-tier <?= e(planTierClase($plan['modalidad'] ?? '')) ?>">
                        <?= e(planModalidadEtiqueta($plan['modalidad'] ?? '')) ?>
                    </span>

                    <div class="plan-icon-wrap plan-icon-wrap--sm" aria-hidden="true">
                        <?= planIconoSvg($plan['modalidad'] ?? '') ?>
                    </div>

                    <h3><?= e($plan['nombre'] ?? 'Plan') ?></h3>

                    <?php if (!empty($plan['descripcion'])): ?>
                        <p class="plan-tagline"><?= e($plan['descripcion']) ?></p>
                    <?php endif; ?>

                    <?php if ($incluye !== []): ?>
                        <ul class="plan-features plan-features--compact">
                            <?php foreach ($incluye as $item): ?>
                                <li><span><?= e($item) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="plan-price plan-price--dual">
                        <span class="plan-price-label">Inversión</span>
                        <div class="plan-price-rows">
                            <div class="plan-price-row">
                                <span><?= e(planModalidadEtiqueta($plan['modalidad'] ?? '')) ?></span>
                                <strong><?= e(planPrecioPublico($plan['precio'] ?? 0)) ?></strong>
                            </div>
                            <?php if (!empty($plan['duracion_dias']) || !empty($plan['duracion'])): ?>
                                <div class="plan-price-row">
                                    <span>Duración</span>
                                    <strong><?= e((int) ($plan['duracion_dias'] ?? $plan['duracion'])) ?> días</strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="solicitud.php?plan_id=<?= $planId ?>" class="plan-cta<?= $destacado ? ' plan-cta--featured' : '' ?>">
                        Elegir plan
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
