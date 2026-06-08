<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$planEntrenamiento = $planEntrenamiento ?? null;
$rutinas = $rutinas ?? [];
$videos = $videos ?? [];
$avanceVirtual = min(100, max(0, (int) ($avanceVirtual ?? 0)));
$programaVirtual = $programaVirtual ?? null;

$nombreTopbar = $_SESSION['nombre'] ?? 'Cliente';
$totalRutinas = count($rutinas);
$totalVideos = count($videos);
$videosCompletados = count(array_filter($videos, function ($v) {
    $e = strtolower(str_replace(' ', '_', (string) ($v['estado_progreso'] ?? '')));

    return in_array($e, ['completado', 'completada'], true);
}));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamiento | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=15">
</head>
<body class="fp-panel">

<div class="fp-layout cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente individual</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreTopbar) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Tu progreso</span>
                <h1><span>Entrenamiento</span></h1>
                <p>Consulta tu plan, rutinas asignadas y contenido virtual pregrabado de tu coach.</p>
            </section>

            <section class="fp-stats-premium">
                <article class="fp-stat-premium fp-stat-premium--fuchsia">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M6 4h12v16H6z" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M9 8h6M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e((string) $totalRutinas) ?></p>
                    <p class="fp-stat-premium-label">Rutinas asignadas</p>
                </article>

                <article class="fp-stat-premium fp-stat-premium--mint">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M4 8l8-4 8 4v8l-8 4-8-4V8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <path d="M12 4v16M4 8l8 4 8-4" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e((string) $totalVideos) ?></p>
                    <p class="fp-stat-premium-label">Lecciones virtuales</p>
                </article>

                <article class="fp-stat-premium">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e((string) $avanceVirtual) ?>%</p>
                    <p class="fp-stat-premium-label">Avance del programa</p>
                </article>
            </section>

            <div class="fp-entreno-grid">
                <article class="fp-card card fp-entreno-card">
                    <div class="fp-entreno-card-head">
                        <h3>Plan y rutinas</h3>
                        <p>Tu plan personalizado y las sesiones que debes completar.</p>
                    </div>
                    <div class="fp-entreno-card-body">
                        <div class="fp-entreno-section">
                            <h4 class="fp-entreno-section-title">Plan de entrenamiento</h4>
                            <?php if (!$planEntrenamiento): ?>
                                <div class="fp-entreno-empty">
                                    Aún no tienes un plan de entrenamiento asignado. Tu coach o el administrador lo configurará pronto.
                                </div>
                            <?php else: ?>
                                <div class="fp-entreno-plan-box">
                                    <p class="fp-entreno-plan-name"><?= e($planEntrenamiento['nombre'] ?? 'Plan FigueFit') ?></p>
                                    <p class="fp-entreno-plan-obj"><?= e($planEntrenamiento['objetivo'] ?? 'Objetivo no definido') ?></p>
                                    <span class="fp-badge fp-badge-ok"><?= e(ucfirst($planEntrenamiento['estado'] ?? 'activo')) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="fp-entreno-section">
                            <h4 class="fp-entreno-section-title">Mis rutinas</h4>
                            <?php if (empty($rutinas)): ?>
                                <div class="fp-entreno-empty">No tienes rutinas asignadas por ahora.</div>
                            <?php else: ?>
                                <?php foreach ($rutinas as $rutina): ?>
                                    <div class="fp-entreno-rutina">
                                        <p class="fp-entreno-rutina-name"><?= e($rutina['nombre'] ?? $rutina['titulo'] ?? 'Rutina') ?></p>
                                        <p class="fp-entreno-rutina-desc"><?= e($rutina['descripcion'] ?? 'Sin descripción') ?></p>
                                        <span class="fp-badge fp-badge-pending"><?= e(ucfirst($rutina['estado'] ?? 'asignada')) ?></span>

                                        <form class="fp-entreno-rutina-form fp-form-premium" style="margin-top:14px;" action="../../controllers/cliente/entrenamientoController.php?accion=marcarRutina" method="POST">
                                            <input type="hidden" name="rutina_id" value="<?= e($rutina['id'] ?? $rutina['id_rutina'] ?? '') ?>">
                                            <select name="estado" required>
                                                <option value="en_progreso">En progreso</option>
                                                <option value="completada">Completada</option>
                                                <option value="omitida">Omitida</option>
                                            </select>
                                            <textarea name="observacion" placeholder="Observación sobre la rutina"></textarea>
                                            <button type="submit" class="fp-form-submit">Actualizar rutina</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

                <article class="fp-card card fp-entreno-card">
                    <div class="fp-entreno-card-head fp-entreno-card-head--mint">
                        <h3>Programa virtual</h3>
                        <p>Videos y material pregrabado incluido en tu plan.</p>
                    </div>
                    <div class="fp-entreno-card-body">
                        <?php if ($programaVirtual): ?>
                            <div class="fp-entreno-programa">
                                <strong><?= e($programaVirtual['nombre'] ?? 'Tu programa') ?></strong>
                                <?php if (!empty($programaVirtual['descripcion'])): ?>
                                    <p><?= nl2br(e($programaVirtual['descripcion'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="fp-entreno-progress-head">
                            <span>Progreso del contenido</span>
                            <strong><?= e((string) $avanceVirtual) ?>%</strong>
                        </div>
                        <div class="fp-entreno-progress-track">
                            <div class="fp-entreno-progress-fill" style="width: <?= e((string) $avanceVirtual) ?>%;"></div>
                        </div>

                        <?php if ($totalVideos > 0): ?>
                            <p style="margin:0 0 14px;font-size:13px;color:var(--fp-text-muted);">
                                <?= e((string) $videosCompletados) ?> de <?= e((string) $totalVideos) ?> lecciones completadas
                            </p>
                        <?php endif; ?>

                        <?php if (empty($videos)): ?>
                            <div class="fp-entreno-empty">
                                Tu coach aún no ha publicado material en este plan. Vuelve pronto.
                            </div>
                        <?php else: ?>
                            <?php foreach ($videos as $video): ?>
                                <?php require __DIR__ . '/partials/materialVirtual.php'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

        </main>
    </div>
</div>
</body>
</html>
