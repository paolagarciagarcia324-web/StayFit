<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('clienteNombreLista')) {
    function clienteNombreLista(array $cliente): string
    {
        $nombre = trim((string) ($cliente['nombre'] ?? ''));

        if ($nombre !== '') {
            return $nombre;
        }

        $nombre = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));

        return $nombre !== '' ? $nombre : 'Cliente sin nombre';
    }
}

if (!function_exists('coachNombreLista')) {
    function coachNombreLista(array $coach): string
    {
        return trim($coach['nombre_completo'] ?? $coach['nombre'] ?? '') ?: 'Coach sin nombre';
    }
}

if (!function_exists('asignacionEstadoBadge')) {
    function asignacionEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'activo' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => 'Activo',
            ],
            'inactivo', 'vencido', 'cancelado' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => ucfirst($estado),
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => $estado !== '' ? strtoupper($estado) : 'ACTIVO',
            ],
        };
    }
}

if (!function_exists('asignacionTieneCoach')) {
    function asignacionTieneCoach(array $item): bool
    {
        $coach = trim((string) ($item['coach'] ?? ''));

        return $coach !== '' && strtolower($coach) !== 'sin coach';
    }
}

if (!function_exists('asignacionTieneVirtual')) {
    function asignacionTieneVirtual(array $item): bool
    {
        $programa = trim((string) ($item['programa_virtual'] ?? ''));

        return $programa !== '' && strtolower($programa) !== 'no asignado';
    }
}

$clientes = $clientes ?? [];
$coaches = $coaches ?? [];
$programas = $programas ?? [];
$asignaciones = $asignaciones ?? [];
$totalPlanes = $totalPlanes ?? 0;
$flash = $flash ?? null;

$totalAsignaciones = count($asignaciones);
$conCoach = count(array_filter($asignaciones, fn($a) => asignacionTieneCoach($a)));
$conVirtual = count(array_filter($asignaciones, fn($a) => asignacionTieneVirtual($a)));
$activas = count(array_filter($asignaciones, fn($a) => strtolower(trim((string) ($a['estado'] ?? 'activo'))) === 'activo'));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaciones | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=8">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Coordinación del servicio</span>
            <h1>Asignaciones</h1>
            <p>Asigna coaches o contenido virtual según la modalidad del plan de cada cliente.</p>
        </section>

        <?php if ($totalPlanes === 0): ?>
            <div class="fp-alert-panel fp-alert-panel--warn">
                No hay planes en el catálogo. Ve a
                <a href="../../controllers/admin/planController.php">Planes</a>
                y crea al menos uno antes de asignar coaches.
            </div>
        <?php endif; ?>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <rect x="4" y="4" width="16" height="16" rx="3" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalAsignaciones) ?></p>
                <p class="fp-stat-premium-label">Asignaciones registradas</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--mint">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M4 20c0-2.8 2.2-4.5 5-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <circle cx="17" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M14 17c0-2 1.5-3.5 3-3.5s3 1.5 3 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $conCoach) ?></p>
                <p class="fp-stat-premium-label">Con coach asignado</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="5" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M10 9l3 2-3 2V9z" fill="currentColor"/>
                            <path d="M17 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $conVirtual) ?></p>
                <p class="fp-stat-premium-label">Con contenido virtual</p>
            </article>
        </section>

        <section class="card fp-panel-unified">
            <div class="fp-panel-unified-head">
                <h3>Gestión de asignaciones</h3>
            </div>

            <div class="fp-asignaciones-forms">
                <div class="fp-asignacion-form-col fp-asignacion-form-col--coach">
                    <h4>Asignar coach</h4>
                    <form action="../../controllers/admin/asignacionController.php?accion=asignarCoach" method="POST">
                        <label for="coach_cliente_id">Cliente</label>
                        <select id="coach_cliente_id" name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>">
                                    <?= e(clienteNombreLista($cliente)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="coach_id">Coach</label>
                        <select id="coach_id" name="coach_id" required>
                            <option value="">Seleccione coach</option>
                            <?php foreach ($coaches as $coach): ?>
                                <option value="<?= e($coach['id'] ?? '') ?>">
                                    <?= e(coachNombreLista($coach)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="fp-form-submit btn">Asignar coach</button>
                    </form>
                </div>

                <div class="fp-asignacion-form-col fp-asignacion-form-col--virtual">
                    <h4>Asignar contenido virtual</h4>
                    <form action="../../controllers/admin/asignacionController.php?accion=asignarContenidoVirtual" method="POST">
                        <label for="virtual_cliente_id">Cliente</label>
                        <select id="virtual_cliente_id" name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>">
                                    <?= e(clienteNombreLista($cliente)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="programa_virtual_id">Programa virtual</label>
                        <select id="programa_virtual_id" name="programa_virtual_id" required>
                            <option value="">Seleccione programa</option>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?= e($programa['id'] ?? '') ?>">
                                    <?= e($programa['nombre'] ?? 'Programa sin nombre') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="fp-form-submit fp-form-submit--mint btn">Asignar videos</button>
                    </form>
                </div>
            </div>

            <div class="fp-panel-list-block">
                <h4>Historial de asignaciones</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Cliente</th>
                                <th style="width:14%;">Modalidad</th>
                                <th style="width:22%;">Coach</th>
                                <th style="width:22%;">Contenido virtual</th>
                                <th class="col-estado">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($asignaciones)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="5">No hay asignaciones registradas todavía.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($asignaciones as $item): ?>
                                <?php $estadoBadge = asignacionEstadoBadge($item['estado'] ?? 'activo'); ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e($item['cliente'] ?? 'Sin cliente') ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fp-tag-inline"><?= e(strtolower($item['modalidad'] ?? 'no definida')) ?></span>
                                    </td>
                                    <td>
                                        <?php if (asignacionTieneCoach($item)): ?>
                                            <span class="fp-cell-highlight"><?= e($item['coach']) ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--fp-text-muted);font-size:13px;">Sin coach</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (asignacionTieneVirtual($item)): ?>
                                            <span class="fp-cell-highlight"><?= e($item['programa_virtual']) ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--fp-text-muted);font-size:13px;">No asignado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
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
