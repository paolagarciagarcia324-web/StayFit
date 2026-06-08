<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$instituciones = $instituciones ?? [];
$planes = $planes ?? [];
$enlacesPorInstitucion = $enlacesPorInstitucion ?? [];
$clientesInstitucionales = $clientesInstitucionales ?? [];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$totalInstituciones = count($instituciones);
$totalEnlaces = count($enlacesPorInstitucion);
$totalRegistrados = count($clientesInstitucionales);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente institucional | FigueFit Admin</title>
    <link rel="stylesheet" href="../../public/panel.css?v=18">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php
    $vistaActiva = 'clienteInstitucional';
    require __DIR__ . '/../partials/panel/sidebarAdmin.php';
    ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Registro externo</span>
            <h1>Cliente <span>institucional</span></h1>
            <p>Personas registradas vía enlace. Para crear instituciones, asignar plan y gestionar enlaces, usa el módulo <a href="../../controllers/admin/institucionController.php" style="color:var(--fp-mint);">Instituciones</a>.</p>
        </section>

        <?php if ($flash): ?>
            <div class="fp-alert fp-alert-<?= e($flash['tipo'] === 'success' ? 'ok' : 'error') ?>" style="margin-bottom:20px;">
                <?= e($flash['mensaje'] ?? '') ?>
            </div>
        <?php endif; ?>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <p class="fp-stat-premium-value"><?= e((string) $totalInstituciones) ?></p>
                <p class="fp-stat-premium-label">Instituciones</p>
            </article>
            <article class="fp-stat-premium fp-stat-premium--mint">
                <p class="fp-stat-premium-value"><?= e((string) $totalEnlaces) ?></p>
                <p class="fp-stat-premium-label">Enlaces configurados</p>
            </article>
            <article class="fp-stat-premium">
                <p class="fp-stat-premium-value"><?= e((string) $totalRegistrados) ?></p>
                <p class="fp-stat-premium-label">Personas registradas</p>
            </article>
        </section>

        <section class="fp-card card" style="margin-bottom:22px;">
            <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                <h3>Generar o actualizar enlace</h3>
                <p>Elige la institución y el plan de convenio. Se creará una URL única para compartir.</p>
            </div>
            <div class="fp-perfil-card-body">
                <?php if (empty($instituciones)): ?>
                    <p style="color:var(--fp-text-soft);">Primero crea una institución en el módulo Instituciones.</p>
                <?php elseif (empty($planes)): ?>
                    <p style="color:var(--fp-text-soft);">No hay planes institucionales activos. Crea un plan con tipo INSTITUCIONAL o AMBOS.</p>
                <?php else: ?>
                    <form class="fp-form-premium" action="../../controllers/admin/clienteInstitucionalController.php?accion=generarEnlace" method="POST">
                        <div class="fp-field">
                            <label for="ci-institucion">Institución</label>
                            <select id="ci-institucion" name="institucion_id" required>
                                <option value="">Seleccione institución</option>
                                <?php foreach ($instituciones as $inst): ?>
                                    <?php $instId = (int) ($inst['id'] ?? $inst['id_institucion'] ?? 0); ?>
                                    <option value="<?= e((string) $instId) ?>"><?= e($inst['nombre'] ?? 'Institución') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fp-field">
                            <label for="ci-plan">Plan de convenio</label>
                            <select id="ci-plan" name="plan_id" required>
                                <option value="">Seleccione plan</option>
                                <?php foreach ($planes as $plan): ?>
                                    <option value="<?= e((string) ($plan['id'] ?? '')) ?>">
                                        <?= e($plan['nombre'] ?? 'Plan') ?> — <?= e((string) ($plan['duracion'] ?? $plan['duracion_dias'] ?? 30)) ?> días
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="fp-form-submit">Generar / actualizar enlace</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <section class="fp-card card" style="margin-bottom:22px;">
            <div class="fp-plan-card-head">
                <h3>Enlaces por institución</h3>
                <p>Copia y comparte la URL. Cada enlace solo registra personas en su institución.</p>
            </div>
            <div class="fp-plan-card-body">
                <?php if (empty($enlacesPorInstitucion)): ?>
                    <div class="fp-pagos-empty">
                        <strong>Sin enlaces generados</strong>
                        <p>Usa el formulario superior para crear el primer enlace.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($enlacesPorInstitucion as $enlace): ?>
                        <?php
                        $enlaceId = (int) ($enlace['id_enlace'] ?? $enlace['id'] ?? 0);
                        $activo = !empty($enlace['activo']);
                        $inputId = 'enlace-' . $enlaceId;
                        ?>
                        <article class="fp-timeline-item item" style="margin-bottom:16px;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                                <div>
                                    <strong><?= e($enlace['institucion_nombre'] ?? 'Institución') ?></strong>
                                    <p style="margin:6px 0;color:var(--fp-text-soft);font-size:13px;">
                                        Plan: <?= e($enlace['plan_nombre'] ?? '—') ?>
                                        · Registros: <?= e((string) (int) ($enlace['registros_realizados'] ?? 0)) ?>
                                    </p>
                                    <span class="fp-badge <?= $activo ? 'fp-badge-ok' : 'fp-badge-alert' ?>">
                                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </div>
                                <div class="fp-row-actions">
                                    <?php if ($activo): ?>
                                        <a class="fp-btn-sm fp-btn-outline" href="../../controllers/admin/clienteInstitucionalController.php?accion=toggleEnlace&id=<?= e((string) $enlaceId) ?>&activo=0">Desactivar</a>
                                    <?php else: ?>
                                        <a class="fp-btn-sm btn-green" href="../../controllers/admin/clienteInstitucionalController.php?accion=toggleEnlace&id=<?= e((string) $enlaceId) ?>&activo=1">Activar</a>
                                    <?php endif; ?>
                                    <form action="../../controllers/admin/clienteInstitucionalController.php?accion=regenerarToken" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_enlace" value="<?= e((string) $enlaceId) ?>">
                                        <button type="submit" class="fp-btn-sm fp-btn-outline-mint" onclick="return confirm('¿Regenerar token? El enlace anterior dejará de funcionar.');">Regenerar token</button>
                                    </form>
                                </div>
                            </div>
                            <div style="margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <input type="text" id="<?= e($inputId) ?>" readonly value="<?= e($enlace['url_registro'] ?? '') ?>" style="flex:1;min-width:220px;padding:10px 12px;border-radius:10px;border:1px solid var(--fp-border);background:var(--fp-input-bg);color:var(--fp-text);font-size:13px;">
                                <button type="button" class="fp-btn-sm fp-btn-outline-mint" data-copy="<?= e($inputId) ?>">Copiar enlace</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="fp-card card">
            <div class="fp-plan-card-head">
                <h3>Personas registradas por enlace</h3>
                <p>Clientas institucionales vinculadas a una organización.</p>
            </div>
            <div class="fp-table-wrap">
                <table class="fp-table-premium fp-table-fluid">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Institución</th>
                            <th>Fecha alta</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientesInstitucionales)): ?>
                            <tr class="fp-empty-row"><td colspan="5">Aún no hay registros institucionales.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($clientesInstitucionales as $cliente): ?>
                            <tr>
                                <td><strong><?= e($cliente['cliente'] ?: 'Sin nombre') ?></strong></td>
                                <td><?= e($cliente['correo'] ?? '—') ?></td>
                                <td><span class="fp-tag-inline"><?= e($cliente['institucion'] ?? '—') ?></span></td>
                                <td><?= e($cliente['fecha_vinculacion'] ?? '—') ?></td>
                                <td><span class="fp-badge fp-badge-ok"><?= e(strtolower((string) ($cliente['estado'] ?? 'activo'))) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            btn.textContent = 'Copiado';
            setTimeout(function() { btn.textContent = 'Copiar enlace'; }, 2000);
        });
    });
});
</script>
</body>
</html>
