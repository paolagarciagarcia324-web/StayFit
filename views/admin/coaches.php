<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('coachEstadoBadge')) {
    function coachEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'activo' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => 'Activo',
            ],
            'inactivo', 'suspendido' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => ucfirst($estado),
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => $estado !== '' ? ucfirst($estado) : 'Sin estado',
            ],
        };
    }
}

if (!function_exists('coachEsActivo')) {
    function coachEsActivo(?string $estado): bool
    {
        return strtolower(trim((string) $estado)) === 'activo';
    }
}

if (!function_exists('coachNombreMostrar')) {
    function coachNombreMostrar(array $item): string
    {
        return trim($item['nombre_completo'] ?? $item['nombre'] ?? 'Coach sin nombre');
    }
}

if (!function_exists('coachInicial')) {
    function coachInicial(array $item): string
    {
        $nombre = coachNombreMostrar($item);

        return strtoupper(substr($nombre, 0, 1));
    }
}

$coaches = $coaches ?? [];
$coach = $coach ?? null;
$clientes = $clientes ?? [];

$totalCoaches = count($coaches);
$totalActivos = count(array_filter($coaches, fn($c) => coachEsActivo($c['estado'] ?? '')));
$totalInactivos = $totalCoaches - $totalActivos;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coaches | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=7">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Equipo profesional</span>
            <h1>Coaches</h1>
            <p>Administra el equipo que acompaña el entrenamiento, nutrición y progreso de las clientas.</p>
        </section>

        <?php if ($alert): ?>
            <div class="<?= ($alert['icon'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <strong><?= e($alert['title'] ?? 'Aviso') ?></strong>
                <?= e($alert['text'] ?? '') ?>
            </div>
        <?php endif; ?>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M5 20c0-3.3 2.7-5.5 7-5.5s7 2.2 7 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalCoaches) ?></p>
                <p class="fp-stat-premium-label">Coaches registrados</p>
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
                <p class="fp-stat-premium-label">Coaches activos</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalInactivos) ?></p>
                <p class="fp-stat-premium-label">Coaches inactivos</p>
            </article>
        </section>

        <section class="card fp-panel-unified">
            <div class="fp-panel-unified-head">
                <h3>Gestión de coaches</h3>
            </div>

            <div class="fp-panel-form-block">
                <form class="fp-form-premium" action="../../controllers/admin/coachController.php?accion=guardar" method="POST" autocomplete="off">
                    <div class="fp-form-grid">
                        <div class="fp-field fp-field--full" style="grid-column: span 2;">
                            <label for="coach_nombre">Nombre completo</label>
                            <input type="text" id="coach_nombre" name="nombre" placeholder="Ana López" required autocomplete="name">
                        </div>

                        <div class="fp-field">
                            <label for="coach_correo">Correo</label>
                            <input type="email" id="coach_correo" name="correo" placeholder="coach@correo.com" required autocomplete="off">
                        </div>

                        <div class="fp-field">
                            <label for="coach_celular">Celular</label>
                            <input type="tel" id="coach_celular" name="celular" placeholder="300 123 4567" required autocomplete="tel">
                        </div>

                        <div class="fp-field">
                            <label for="coach_identificacion">Identificación</label>
                            <input type="text" id="coach_identificacion" name="identificacion" placeholder="Documento" required autocomplete="off">
                        </div>

                        <div class="fp-field">
                            <label for="coach_contrasena">Contraseña</label>
                            <input type="password" id="coach_contrasena" name="contrasena" minlength="6" placeholder="Opcional" autocomplete="new-password">
                        </div>

                        <div class="fp-field">
                            <label for="coach_especialidad">Especialidad</label>
                            <input type="text" id="coach_especialidad" name="especialidad" placeholder="Fuerza, movilidad…" required>
                        </div>

                        <div class="fp-field fp-field--full">
                            <label for="coach_biografia">Biografía profesional</label>
                            <textarea id="coach_biografia" name="biografia" placeholder="Experiencia, enfoque y tipo de acompañamiento"></textarea>
                        </div>
                    </div>

                    <button type="submit" class="fp-form-submit" style="max-width:220px;margin-top:6px;">Registrar coach</button>
                    <span class="fp-field-hint" style="display:block;margin-top:8px;">Si no defines contraseña, se usará el número de identificación.</span>
                </form>
            </div>

            <div class="fp-panel-list-block">
                <h4>Equipo de coaches</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Coach</th>
                                <th class="col-contacto">Contacto</th>
                                <th style="width:22%;">Especialidad</th>
                                <th class="col-estado">Estado</th>
                                <th class="col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coaches)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="5">No hay coaches registrados todavía.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($coaches as $item): ?>
                                <?php
                                $estadoBadge = coachEstadoBadge($item['estado'] ?? '');
                                $coachId = (int) ($item['id'] ?? 0);
                                $activo = coachEsActivo($item['estado'] ?? '');
                                $nombre = coachNombreMostrar($item);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fp-coach-cell">
                                            <span class="fp-coach-avatar" aria-hidden="true"><?= e(coachInicial($item)) ?></span>
                                            <div class="fp-cell-stack">
                                                <strong><?= e($nombre) ?></strong>
                                                <?php if (!empty($item['identificacion'] ?? $item['credencial'] ?? null)): ?>
                                                    <span>ID <?= e($item['identificacion'] ?? $item['credencial']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fp-cell-stack">
                                            <span class="fp-cell-highlight"><?= e($item['correo'] ?? 'Sin correo') ?></span>
                                            <span><?= e($item['telefono'] ?? $item['celular'] ?? '—') ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="fp-tag-inline"><?= e($item['especialidad'] ?? 'No definida') ?></span>
                                    </td>

                                    <td>
                                        <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                    </td>

                                    <td>
                                        <div class="fp-row-actions">
                                            <a class="btn fp-btn-sm fp-btn-outline"
                                               href="../../controllers/admin/coachController.php?accion=detalle&id=<?= e($coachId) ?>">
                                                Ver
                                            </a>

                                            <?php if ($activo): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline"
                                                   href="../../controllers/admin/coachController.php?accion=cambiarEstado&id=<?= e($coachId) ?>&estado=inactivo"
                                                   style="border-color:rgba(255,47,160,0.35)!important;color:var(--fp-fuchsia)!important;">
                                                    Inactivar
                                                </a>
                                            <?php else: ?>
                                                <a class="btn fp-btn-sm btn-green"
                                                   href="../../controllers/admin/coachController.php?accion=cambiarEstado&id=<?= e($coachId) ?>&estado=activo">
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

        <?php if ($coach): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle del coach</h3>

                <dl class="fp-cliente-detail">
                    <div class="fp-cliente-detail-item">
                        <dt>Nombre</dt>
                        <dd><?= e(coachNombreMostrar($coach)) ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Correo</dt>
                        <dd><?= e($coach['correo'] ?? '—') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Celular</dt>
                        <dd><?= e($coach['telefono'] ?? '—') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Especialidad</dt>
                        <dd><?= e($coach['especialidad'] ?? '—') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Estado</dt>
                        <dd>
                            <?php $detBadge = coachEstadoBadge($coach['estado'] ?? ''); ?>
                            <span class="<?= e($detBadge['class']) ?>"><?= e($detBadge['label']) ?></span>
                        </dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Clientes asignados</dt>
                        <dd><?= e((string) count($clientes)) ?></dd>
                    </div>
                </dl>

                <?php if (!empty($coach['biografia'])): ?>
                    <p style="margin:16px 0 0;color:var(--fp-text-soft);font-size:14px;line-height:1.6;">
                        <?= e($coach['biografia']) ?>
                    </p>
                <?php endif; ?>

                <div class="fp-row-actions" style="margin-top:16px;max-width:none;">
                    <a class="btn fp-btn-outline" href="../../controllers/admin/coachController.php">Volver al listado</a>
                </div>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
