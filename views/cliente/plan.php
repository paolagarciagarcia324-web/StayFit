<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('planFormatearPrecio')) {
    function planFormatearPrecio($valor): string
    {
        if ($valor === null || $valor === '') {
            return '$0';
        }

        return '$' . number_format((float) $valor, 0, ',', '.');
    }
}

if (!function_exists('planDiasRestantes')) {
    function planDiasRestantes(?string $fechaFin): ?int
    {
        if (!$fechaFin) {
            return null;
        }

        $fin = strtotime($fechaFin);
        if ($fin === false) {
            return null;
        }

        return (int) ceil(($fin - strtotime('today')) / 86400);
    }
}

if (!function_exists('planModalidadBadge')) {
    function planModalidadBadge(?string $modalidad): string
    {
        $m = strtoupper(trim((string) $modalidad));

        return match ($m) {
            'VIRTUAL' => 'fp-badge fp-badge-ok',
            'PRESENCIAL' => 'fp-badge fp-badge-pending',
            'MIXTO', 'MIXTA' => 'fp-badge',
            default => 'fp-badge fp-badge-pending',
        };
    }
}

$plan = $plan ?? null;
$coach = $coach ?? null;
$accesos = $accesos ?? [];

$nombreTopbar = $_SESSION['nombre'] ?? 'Cliente';
$accesosActivos = array_filter($accesos, fn($a) => ($a['estado'] ?? '') === 'activo' || !empty($a['habilitado']));
$totalAccesos = count($accesosActivos);
$diasRestantes = $plan ? planDiasRestantes($plan['fecha_fin'] ?? null) : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi plan | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=14">
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
                <span class="fp-hero-tag">Tu membresía</span>
                <h1>Mi <span>plan</span></h1>
                <p>Consulta tu plan activo, modalidad, beneficios y módulos habilitados en FigueFit.</p>
            </section>

            <?php if (!$plan): ?>
                <section class="fp-stats-premium">
                    <article class="fp-stat-premium fp-stat-premium--warn">
                        <div class="fp-stat-premium-head">
                            <div class="fp-stat-premium-icon" aria-hidden="true">!</div>
                        </div>
                        <p class="fp-stat-premium-value" style="font-size:16px;">Sin plan activo</p>
                        <p class="fp-stat-premium-label">Aún no tienes una membresía vigente</p>
                    </article>
                </section>

                <article class="fp-card card fp-plan-card">
                    <div class="fp-plan-card-body" style="padding-top:24px;">
                        <div class="fp-plan-empty">
                            No tienes un plan activo actualmente. Explora las opciones disponibles y solicita tu inscripción.
                        </div>
                        <div class="fp-plan-actions">
                            <a class="btn fp-btn" href="../../public/planPublico.php">Ver planes disponibles</a>
                            <a class="btn fp-btn fp-btn-outline" href="../../controllers/cliente/pagoController.php">Enviar comprobante de pago</a>
                        </div>
                    </div>
                </article>
            <?php else: ?>

                <section class="fp-stats-premium">
                    <article class="fp-stat-premium fp-stat-premium--fuchsia">
                        <div class="fp-stat-premium-head">
                            <div class="fp-stat-premium-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2v20M7 7h10M6 11h12M5 15h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                        <p class="fp-stat-premium-value"><?= e(planFormatearPrecio($plan['precio'] ?? 0)) ?></p>
                        <p class="fp-stat-premium-label">Inversión del plan</p>
                    </article>

                    <article class="fp-stat-premium fp-stat-premium--mint">
                        <div class="fp-stat-premium-head">
                            <div class="fp-stat-premium-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                        <p class="fp-stat-premium-value">
                            <?= $diasRestantes !== null ? e((string) max(0, $diasRestantes)) . ' días' : '—' ?>
                        </p>
                        <p class="fp-stat-premium-label">Tiempo restante</p>
                    </article>

                    <article class="fp-stat-premium">
                        <div class="fp-stat-premium-head">
                            <div class="fp-stat-premium-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 7h16v10H4z" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M8 11h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>
                        <p class="fp-stat-premium-value"><?= e((string) $totalAccesos) ?></p>
                        <p class="fp-stat-premium-label">Módulos habilitados</p>
                    </article>
                </section>

                <div class="fp-plan-grid">
                    <article class="fp-card card fp-plan-card">
                        <div class="fp-plan-card-head">
                            <h3>Plan activo</h3>
                            <p>Detalle de tu membresía vigente y acompañamiento asignado.</p>
                        </div>
                        <div class="fp-plan-card-body">
                            <h2 class="fp-plan-hero-name"><?= e($plan['nombre'] ?? 'Plan FigueFit') ?></h2>
                            <p class="fp-plan-desc"><?= e($plan['descripcion'] ?? 'Plan diseñado para acompañar tu proceso fitness.') ?></p>

                            <div class="fp-plan-badges">
                                <span class="<?= e(planModalidadBadge($plan['modalidad'] ?? '')) ?>">
                                    <?= e(strtoupper($plan['modalidad'] ?? 'MODALIDAD')) ?>
                                </span>
                                <span class="fp-badge fp-badge-ok"><?= e(ucfirst(strtolower($plan['estado'] ?? 'activo'))) ?></span>
                            </div>

                            <div class="fp-plan-meta">
                                <div class="fp-plan-meta-item">
                                    <strong>Precio</strong>
                                    <span><?= e(planFormatearPrecio($plan['precio'] ?? 0)) ?></span>
                                </div>
                                <div class="fp-plan-meta-item">
                                    <strong>Duración</strong>
                                    <span><?= e($plan['duracion'] ?? $plan['duracion_dias'] ?? '0') ?> días</span>
                                </div>
                                <div class="fp-plan-meta-item">
                                    <strong>Inicio</strong>
                                    <span><?= e($plan['fecha_inicio'] ?? 'No registrada') ?></span>
                                </div>
                                <div class="fp-plan-meta-item">
                                    <strong>Vencimiento</strong>
                                    <span><?= e($plan['fecha_fin'] ?? 'No registrada') ?></span>
                                </div>
                            </div>

                            <div class="fp-plan-coach">
                                <h4>Coach asignado</h4>
                                <?php if ($coach || !empty($plan['coach_nombre'])): ?>
                                    <p class="fp-plan-coach-name"><?= e($coach['nombre_completo'] ?? $plan['coach_nombre'] ?? '') ?></p>
                                    <?php if (!empty($coach['especialidad'] ?? $plan['coach_especialidad'] ?? '')): ?>
                                        <p class="fp-plan-coach-meta"><?= e($coach['especialidad'] ?? $plan['coach_especialidad'] ?? '') ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($coach['correo'] ?? $plan['coach_correo'] ?? '')): ?>
                                        <p class="fp-plan-coach-meta"><?= e($coach['correo'] ?? $plan['coach_correo'] ?? '') ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php
                                    $modalidadPlan = strtoupper($plan['modalidad'] ?? '');
                                    $requiereCoach = !empty($plan['requiere_coach']) || in_array($modalidadPlan, ['PRESENCIAL', 'MIXTA', 'MIXTO'], true);
                                    ?>
                                    <p class="fp-plan-coach-meta">
                                        <?= $requiereCoach
                                            ? 'Pendiente de asignación por el administrador.'
                                            : 'No aplica para tu modalidad virtual.' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>

                    <article class="fp-card card fp-plan-card">
                        <div class="fp-plan-card-head fp-plan-card-head--mint">
                            <h3>Accesos habilitados</h3>
                            <p>Módulos del servicio disponibles según tu plan contratado.</p>
                        </div>
                        <div class="fp-plan-card-body">
                            <?php if (empty($accesosActivos)): ?>
                                <div class="fp-plan-empty">
                                    No tienes accesos habilitados todavía. Cuando el administrador active tu plan, aparecerán aquí.
                                </div>
                            <?php else: ?>
                                <div class="fp-plan-acceso-list">
                                    <?php foreach ($accesosActivos as $acceso): ?>
                                        <div class="fp-plan-acceso-item">
                                            <strong><?= e($acceso['modulo'] ?? 'Módulo') ?></strong>
                                            <span class="fp-badge fp-badge-ok"><?= e(ucfirst($acceso['estado'] ?? 'activo')) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="fp-plan-actions">
                                <a class="btn fp-btn" href="../../controllers/cliente/pagoController.php">Renovar o enviar pago</a>
                                <a class="btn fp-btn fp-btn-outline" href="../../public/planPublico.php">Ver otros planes</a>
                            </div>
                        </div>
                    </article>
                </div>

            <?php endif; ?>

        </main>
    </div>
</div>
</body>
</html>
