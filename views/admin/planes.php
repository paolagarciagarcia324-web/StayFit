<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('planEstadoBadge')) {
    function planEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'activo' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => 'Activo',
            ],
            'inactivo' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => 'Inactivo',
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => $estado !== '' ? ucfirst($estado) : 'Sin estado',
            ],
        };
    }
}

if (!function_exists('planEsActivo')) {
    function planEsActivo(?string $estado): bool
    {
        return strtolower(trim((string) $estado)) === 'activo';
    }
}

if (!function_exists('planModalidadLabel')) {
    function planModalidadLabel(?string $modalidad): string
    {
        $modalidad = strtolower(trim((string) $modalidad));

        return match ($modalidad) {
            'virtual' => 'Virtual',
            'presencial' => 'Presencial',
            'mixta', 'mixto' => 'Mixto',
            default => $modalidad !== '' ? ucfirst($modalidad) : '—',
        };
    }
}

if (!function_exists('planTieneMaterialVirtual')) {
    function planTieneMaterialVirtual(array $item): bool
    {
        $mod = strtoupper((string) ($item['modalidad'] ?? ''));

        return in_array($mod, ['VIRTUAL', 'MIXTA', 'MIXTO'], true);
    }
}

if (!function_exists('formatearPrecioPlan')) {
    function formatearPrecioPlan($precio): string
    {
        return number_format((float) $precio, 0, ',', '.');
    }
}

$planes = $planes ?? [];
$programas = $programas ?? [];
$programasVirtuales = $programasVirtuales ?? [];

$totalPlanes = count($planes);
$totalActivos = count(array_filter($planes, fn($p) => planEsActivo($p['estado'] ?? '')));
$totalInactivos = $totalPlanes - $totalActivos;
$totalVirtuales = count(array_filter($planes, fn($p) => planTieneMaterialVirtual($p)));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes y programas | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=9">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Catálogo comercial</span>
            <h1>Planes y programas</h1>
            <p>Configura los planes visibles en el inicio público, sus precios, modalidad y accesos.</p>
        </section>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalPlanes) ?></p>
                <p class="fp-stat-premium-label">Planes en catálogo</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--mint">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalActivos) ?></p>
                <p class="fp-stat-premium-label">Disponibles para venta</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M10 9l3 2-3 2V9z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalVirtuales) ?></p>
                <p class="fp-stat-premium-label">Con material virtual</p>
            </article>
        </section>

        <section class="card fp-panel-unified">
            <div class="fp-panel-unified-head">
                <h3>Gestión de planes</h3>
            </div>

            <div class="fp-panel-form-block">
                <form class="fp-form-premium" action="../../controllers/admin/planController.php?accion=guardarPlan" method="POST">
                    <div class="fp-form-grid">
                        <div class="fp-field fp-field--full" style="grid-column: span 2;">
                            <label for="plan_nombre">Nombre</label>
                            <input type="text" id="plan_nombre" name="nombre" placeholder="Plan Presencial Integral" required>
                        </div>

                        <div class="fp-field">
                            <label for="plan_precio">Precio</label>
                            <input type="number" id="plan_precio" name="precio" min="0" placeholder="180000" required>
                        </div>

                        <div class="fp-field">
                            <label for="plan_duracion">Duración (días)</label>
                            <input type="number" id="plan_duracion" name="duracion_dias" min="1" placeholder="30" required>
                        </div>

                        <div class="fp-field fp-field--full">
                            <label for="plan_descripcion">Descripción</label>
                            <textarea id="plan_descripcion" name="descripcion" placeholder="Beneficios, alcance y enfoque del plan" required></textarea>
                        </div>

                        <div class="fp-field">
                            <label for="plan_modalidad">Modalidad</label>
                            <select id="plan_modalidad" name="modalidad" required>
                                <option value="presencial">Presencial</option>
                                <option value="virtual">Virtual</option>
                                <option value="mixta">Mixta</option>
                            </select>
                        </div>

                        <div class="fp-field">
                            <label for="plan_programa">Programa virtual</label>
                            <select id="plan_programa" name="programa_virtual_id">
                                <option value="">No aplica</option>
                                <?php foreach ($programasVirtuales as $programa): ?>
                                    <option value="<?= e($programa['id'] ?? '') ?>">
                                        <?= e($programa['nombre'] ?? 'Programa virtual') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fp-field fp-field--full">
                            <span class="fp-field-hint" style="margin:0 0 8px;">Incluye en el plan</span>
                            <div class="fp-check-grid">
                                <label class="fp-check-item">
                                    <input type="checkbox" name="incluye_entrenamiento" checked>
                                    Entrenamiento
                                </label>
                                <label class="fp-check-item">
                                    <input type="checkbox" name="incluye_nutricion">
                                    Nutrición
                                </label>
                                <label class="fp-check-item">
                                    <input type="checkbox" name="requiere_coach">
                                    Requiere coach
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="fp-form-submit" style="max-width:220px;">Guardar plan</button>
                </form>
            </div>

            <div class="fp-panel-list-block">
                <h4>Planes registrados · <?= e((string) $totalActivos) ?> activos</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Plan</th>
                                <th style="width:14%;">Modalidad</th>
                                <th style="width:14%;">Precio</th>
                                <th class="col-estado">Estado</th>
                                <th class="col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($planes)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="5">No hay planes registrados todavía.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($planes as $item): ?>
                                <?php
                                $estadoBadge = planEstadoBadge($item['estado'] ?? '');
                                $planId = (int) ($item['id'] ?? $item['id_plan'] ?? 0);
                                $activo = planEsActivo($item['estado'] ?? '');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e($item['nombre'] ?? '') ?></strong>
                                            <?php if (!empty($item['descripcion'])): ?>
                                                <span><?= e($item['descripcion']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['duracion'] ?? $item['duracion_dias'] ?? null)): ?>
                                                <span class="fp-cell-highlight"><?= e($item['duracion'] ?? $item['duracion_dias']) ?> días</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="fp-tag-inline"><?= e(planModalidadLabel($item['modalidad'] ?? '')) ?></span>
                                    </td>

                                    <td>
                                        <span class="fp-money"><small>$</small><?= e(formatearPrecioPlan($item['precio'] ?? 0)) ?></span>
                                    </td>

                                    <td>
                                        <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                    </td>

                                    <td>
                                        <div class="fp-row-actions">
                                            <?php if (planTieneMaterialVirtual($item)): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline-mint"
                                                   href="../../controllers/admin/contenidoVirtualController.php?plan_id=<?= e($planId) ?>">
                                                    Videos
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($activo): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline"
                                                   href="../../controllers/admin/planController.php?accion=cambiarEstado&id=<?= e($planId) ?>&estado=inactivo"
                                                   style="border-color:rgba(255,47,160,0.35)!important;color:var(--fp-fuchsia)!important;">
                                                    Inactivar
                                                </a>
                                            <?php else: ?>
                                                <a class="btn fp-btn-sm btn-green"
                                                   href="../../controllers/admin/planController.php?accion=cambiarEstado&id=<?= e($planId) ?>&estado=activo">
                                                    Activar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>
</div>
</body>
</html>
