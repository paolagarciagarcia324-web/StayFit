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
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Entrenamiento | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.rutina {
            border-left: 5px solid #D63384;
            background: #fff7fb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .video, .leccion-card {
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
            background: #FFFFFF;
        }

        .programa-intro {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 20px;
        }

        .progress-track {
            background: #eee;
            border-radius: 20px;
            height: 12px;
            overflow: hidden;
            margin: 10px 0 20px;
        }

        .progress-fill {
            background: linear-gradient(90deg, #3EB489, #D63384);
            height: 100%;
            border-radius: 20px;
            transition: width .3s;
        }

        .leccion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .leccion-orden {
            font-weight: 700;
            color: #D63384;
        }

        .leccion-badge {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 12px;
            background: #eee;
            color: #555;
        }

        .leccion-badge--completado { background: #3EB489; color: #fff; }
        .leccion-badge--en_progreso { background: #D63384; color: #fff; }

        .leccion-desc { color: #555; line-height: 1.5; }

        .leccion-embed {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            margin: 12px 0;
        }

        .leccion-embed iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .leccion-video, .leccion-img {
            width: 100%;
            max-height: 360px;
            border-radius: 12px;
            margin: 12px 0;
        }

        .leccion-done {
            color: #3EB489;
            font-weight: 700;
        }

        

        

        

        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 14px;
            margin: 8px 0 12px;
            font-family: inherit;
        }

        button {
            background: #3EB489;
            color: #FFFFFF;
            border: none;
            padding: 11px 16px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .progress-box {
            background: #eee;
            height: 14px;
            border-radius: 20px;
            overflow: hidden;
            margin: 12px 0;
        }

        .progress-bar {
            width: <?= e($avanceVirtual) ?>%;
            height: 100%;
            background: #3EB489;
        }

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }
    </style>
</head>
<body class="fp-panel">

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Entrenamiento</h1>
            <p>Consulta tu plan, rutinas asignadas y contenido virtual pregrabado.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Plan de entrenamiento</h3>

                <?php if (!$planEntrenamiento): ?>
                    <div class="empty">Aún no tienes un plan de entrenamiento asignado.</div>
                <?php else: ?>
                    <p><strong><?= e($planEntrenamiento['nombre'] ?? 'Plan StayFit') ?></strong></p>
                    <p><?= e($planEntrenamiento['objetivo'] ?? 'Objetivo no definido') ?></p>
                    <span class="badge"><?= e($planEntrenamiento['estado'] ?? 'activo') ?></span>
                <?php endif; ?>

                <hr>

                <h3>Mis rutinas</h3>

                <?php if (empty($rutinas)): ?>
                    <div class="empty">No tienes rutinas asignadas por ahora.</div>
                <?php endif; ?>

                <?php foreach ($rutinas as $rutina): ?>
                    <div class="rutina">
                        <strong><?= e($rutina['nombre'] ?? 'Rutina') ?></strong>
                        <p><?= e($rutina['descripcion'] ?? 'Sin descripción') ?></p>
                        <span class="badge"><?= e($rutina['estado'] ?? 'asignada') ?></span>

                        <form action="../../controllers/cliente/entrenamientoController.php?accion=marcarRutina" method="POST">
                            <input type="hidden" name="rutina_id" value="<?= e($rutina['id'] ?? '') ?>">

                            <select name="estado" required>
                                <option value="en_progreso">En progreso</option>
                                <option value="completada">Completada</option>
                                <option value="omitida">Omitida</option>
                            </select>

                            <textarea name="observacion" placeholder="Observación sobre la rutina"></textarea>

                            <button type="submit">Actualizar rutina</button>
                        </form>
                    </div>
                <?php endforeach; ?>
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
