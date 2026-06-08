<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('institucionEstadoBadge')) {
    function institucionEstadoBadge(?string $estado): array
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

if (!function_exists('institucionEsActiva')) {
    function institucionEsActiva(?string $estado): bool
    {
        return strtolower(trim((string) $estado)) === 'activo';
    }
}

$instituciones = $instituciones ?? [];
$clientesInstitucionales = $clientesInstitucionales ?? [];
$planes = $planes ?? [];
$enlacesPorInstitucion = $enlacesPorInstitucion ?? [];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$totalInstituciones = count($instituciones);
$totalActivas = count(array_filter($instituciones, fn($i) => institucionEsActiva($i['estado'] ?? '')));
$totalClientesIns = count($clientesInstitucionales);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instituciones | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=10">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php
    $vistaActiva = 'instituciones';
    require __DIR__ . '/../partials/panel/sidebarAdmin.php';
    ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Convenios corporativos</span>
            <h1>Instituciones</h1>
            <p>Crea convenios con plan y enlace de registro. Al editar el plan, el enlace usa el nuevo convenio; puedes regenerarlo si necesitas invalidar el anterior.</p>
        </section>

        <?php if ($flash): ?>
            <div class="fp-alert fp-alert-<?= e($flash['tipo'] === 'success' ? 'ok' : ($flash['tipo'] === 'warning' ? 'pending' : 'error')) ?>" style="margin-bottom:20px;">
                <?= e($flash['mensaje'] ?? '') ?>
            </div>
        <?php endif; ?>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="6" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M9 10h6M9 14h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M12 6V4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalInstituciones) ?></p>
                <p class="fp-stat-premium-label">Instituciones registradas</p>
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
                <p class="fp-stat-premium-value"><?= e((string) $totalActivas) ?></p>
                <p class="fp-stat-premium-label">Convenios activos</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="8" cy="8" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="16" cy="8" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M4 18c0-2.2 1.8-3.5 4-3.5s4 1.3 4 3.5M12 18c0-2.2 1.8-3.5 4-3.5s4 1.3 4 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalClientesIns) ?></p>
                <p class="fp-stat-premium-label">Clientes institucionales</p>
            </article>
        </section>

        <section class="card fp-panel-unified">
            <div class="fp-panel-unified-head">
                <h3>Gestión de instituciones</h3>
            </div>

            <div class="fp-panel-form-block">
                <h4 style="margin-bottom:14px;color:var(--fp-text-soft);font-size:14px;">Nueva institución</h4>
                <?php if (empty($planes)): ?>
                    <p style="color:var(--fp-text-soft);margin-bottom:12px;">No hay planes institucionales activos. Crea uno en el módulo Planes (tipo INSTITUCIONAL o AMBOS).</p>
                <?php endif; ?>
                <form class="fp-form-premium" action="../../controllers/admin/institucionController.php?accion=guardar" method="POST" autocomplete="off">
                    <div class="fp-form-grid">
                        <div class="fp-field fp-field--full" style="grid-column: span 2;">
                            <label for="inst_nombre">Nombre</label>
                            <input type="text" id="inst_nombre" name="nombre" placeholder="Empresa o institución" required>
                        </div>

                        <div class="fp-field">
                            <label for="inst_nit">NIT o identificación</label>
                            <input type="text" id="inst_nit" name="nit" placeholder="900.123.456-7" required>
                        </div>

                        <div class="fp-field">
                            <label for="inst_telefono">Teléfono</label>
                            <input type="tel" id="inst_telefono" name="telefono" placeholder="601 234 5678" required autocomplete="tel">
                        </div>

                        <div class="fp-field">
                            <label for="inst_correo">Correo</label>
                            <input type="email" id="inst_correo" name="correo" placeholder="contacto@empresa.com" required autocomplete="off">
                        </div>

                        <div class="fp-field">
                            <label for="inst_direccion">Dirección</label>
                            <input type="text" id="inst_direccion" name="direccion" placeholder="Ciudad, sede principal" required>
                        </div>

                        <div class="fp-field">
                            <label for="inst_plan">Plan de convenio</label>
                            <select id="inst_plan" name="plan_id" required <?= empty($planes) ? 'disabled' : '' ?>>
                                <option value="">Seleccione plan</option>
                                <?php foreach ($planes as $plan): ?>
                                    <option value="<?= e((string) ($plan['id'] ?? '')) ?>">
                                        <?= e($plan['nombre'] ?? 'Plan') ?> · <?= e((string) ($plan['duracion'] ?? $plan['duracion_dias'] ?? 30)) ?> días
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <p style="font-size:12px;color:var(--fp-text-muted);margin:12px 0 16px;">Al registrar se generará automáticamente el enlace público de inscripción ligado a esta institución y al plan elegido.</p>

                    <button type="submit" class="fp-form-submit" style="max-width:280px;" <?= empty($planes) ? 'disabled' : '' ?>>Registrar institución y enlace</button>
                </form>
            </div>

            <div class="fp-panel-list-block">
                <h4>Listado de instituciones</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Institución</th>
                                <th>Plan / enlace</th>
                                <th class="col-contacto">Contacto</th>
                                <th class="col-estado">Estado</th>
                                <th class="col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($instituciones)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="5">No hay instituciones registradas todavía.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($instituciones as $item): ?>
                                <?php
                                $estadoBadge = institucionEstadoBadge($item['estado'] ?? '');
                                $instId = (int) ($item['id'] ?? $item['id_institucion'] ?? 0);
                                $activa = institucionEsActiva($item['estado'] ?? '');
                                $enlace = $enlacesPorInstitucion[$instId] ?? null;
                                $planActualId = (int) ($enlace['id_plan'] ?? 0);
                                $inputLinkId = 'enlace-inst-' . $instId;
                                ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e($item['nombre'] ?? '') ?></strong>
                                            <span>NIT <?= e($item['nit'] ?? '—') ?></span>
                                            <?php if (!empty($item['direccion'])): ?>
                                                <span class="fp-cell-highlight"><?= e($item['direccion']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fp-cell-stack">
                                            <span class="fp-tag-inline"><?= e($enlace['plan_nombre'] ?? 'Sin plan') ?></span>
                                            <?php if (!empty($enlace['url_registro'])): ?>
                                                <span style="font-size:11px;color:var(--fp-text-soft);">Registros: <?= e((string) (int) ($enlace['registros_realizados'] ?? 0)) ?></span>
                                                <div style="display:flex;gap:6px;align-items:center;margin-top:6px;flex-wrap:wrap;">
                                                    <input type="text" id="<?= e($inputLinkId) ?>" readonly value="<?= e($enlace['url_registro']) ?>" style="flex:1;min-width:140px;max-width:220px;padding:6px 8px;border-radius:8px;border:1px solid var(--fp-border);background:var(--fp-input-bg);color:var(--fp-text);font-size:11px;">
                                                    <button type="button" class="fp-btn-sm fp-btn-outline-mint" data-copy="<?= e($inputLinkId) ?>">Copiar</button>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size:12px;color:var(--fp-text-muted);">Sin enlace — edita y guarda con un plan</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fp-cell-stack">
                                            <span class="fp-cell-highlight"><?= e($item['correo'] ?? '—') ?></span>
                                            <span><?= e($item['telefono'] ?? $item['telefono_contacto'] ?? '—') ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                    </td>

                                    <td>
                                        <div class="fp-row-actions" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                            <details style="width:100%;">
                                                <summary class="fp-btn-sm fp-btn-outline-mint" style="cursor:pointer;list-style:none;">Editar</summary>
                                                <form class="fp-form-premium" style="margin-top:12px;padding:12px;border:1px solid var(--fp-border);border-radius:12px;" action="../../controllers/admin/institucionController.php?accion=actualizar" method="POST">
                                                    <input type="hidden" name="id" value="<?= e((string) $instId) ?>">
                                                    <input type="hidden" name="estado" value="<?= e($item['estado'] ?? 'activo') ?>">
                                                    <div class="fp-field" style="margin-bottom:10px;">
                                                        <label>Nombre</label>
                                                        <input type="text" name="nombre" value="<?= e($item['nombre'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="fp-field" style="margin-bottom:10px;">
                                                        <label>NIT</label>
                                                        <input type="text" name="nit" value="<?= e($item['nit'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="fp-field" style="margin-bottom:10px;">
                                                        <label>Teléfono</label>
                                                        <input type="text" name="telefono" value="<?= e($item['telefono'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="fp-field" style="margin-bottom:10px;">
                                                        <label>Correo</label>
                                                        <input type="email" name="correo" value="<?= e($item['correo'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="fp-field" style="margin-bottom:10px;">
                                                        <label>Dirección</label>
                                                        <input type="text" name="direccion" value="<?= e($item['direccion'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="fp-field" style="margin-bottom:10px;">
                                                        <label>Plan de convenio</label>
                                                        <select name="plan_id" required>
                                                            <?php foreach ($planes as $plan): ?>
                                                                <?php $pid = (int) ($plan['id'] ?? 0); ?>
                                                                <option value="<?= e((string) $pid) ?>" <?= $pid === $planActualId ? 'selected' : '' ?>>
                                                                    <?= e($plan['nombre'] ?? 'Plan') ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--fp-text-soft);margin-bottom:12px;">
                                                        <input type="checkbox" name="regenerar_enlace" value="1">
                                                        Regenerar enlace (invalida el URL anterior y aplica el plan seleccionado)
                                                    </label>
                                                    <button type="submit" class="fp-btn-sm btn-green">Guardar cambios</button>
                                                </form>
                                            </details>
                                            <?php if ($activa): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline"
                                                   href="../../controllers/admin/institucionController.php?accion=cambiarEstado&id=<?= e($instId) ?>&estado=inactivo"
                                                   style="border-color:rgba(255,47,160,0.35)!important;color:var(--fp-fuchsia)!important;">
                                                    Inactivar
                                                </a>
                                            <?php else: ?>
                                                <a class="btn fp-btn-sm btn-green"
                                                   href="../../controllers/admin/institucionController.php?accion=cambiarEstado&id=<?= e($instId) ?>&estado=activo">
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

            <div class="fp-panel-list-block" style="border-top:1px solid var(--fp-border);">
                <h4>Clientes institucionales vinculados</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Cliente</th>
                                <th style="width:28%;">Institución</th>
                                <th style="width:22%;">Cargo / relación</th>
                                <th class="col-estado">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientesInstitucionales)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="4">No hay clientes institucionales vinculados.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($clientesInstitucionales as $cliente): ?>
                                <?php $cliBadge = institucionEstadoBadge($cliente['estado'] ?? 'activo'); ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e($cliente['cliente'] ?? $cliente['nombre_completo'] ?? trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')) ?: 'Sin cliente') ?></strong>
                                            <?php if (!empty($cliente['correo'])): ?>
                                                <span style="color:var(--fp-text-soft);font-size:12px;"><?= e($cliente['correo']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fp-tag-inline"><?= e($cliente['institucion'] ?? 'Sin institución') ?></span>
                                    </td>
                                    <td>
                                        <span style="color:var(--fp-text-soft);font-size:13px;"><?= e($cliente['cargo'] ?? 'No definido') ?></span>
                                    </td>
                                    <td>
                                        <span class="<?= e($cliBadge['class']) ?>"><?= e($cliBadge['label']) ?></span>
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
<script>
document.querySelectorAll('[data-copy]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(btn.getAttribute('data-copy'));
        if (!input) return;
        input.select();
        navigator.clipboard.writeText(input.value).then(function() {
            btn.textContent = 'Copiado';
            setTimeout(function() { btn.textContent = 'Copiar'; }, 2000);
        });
    });
});
</script>
</body>
</html>
