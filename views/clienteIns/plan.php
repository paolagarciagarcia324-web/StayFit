<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('planInsFormatearPrecio')) {
    function planInsFormatearPrecio($valor): string
    {
        if ($valor === null || $valor === '') {
            return '$0';
        }

        return '$' . number_format((float) $valor, 0, ',', '.');
    }
}

if (!function_exists('planInsFormatearFecha')) {
    function planInsFormatearFecha(?string $fecha): string
    {
        if ($fecha === null || trim($fecha) === '') {
            return 'No registrada';
        }

        try {
            return (new DateTime($fecha))->format('d/m/Y');
        } catch (Exception $e) {
            return $fecha;
        }
    }
}

if (!function_exists('planInsDiasRestantes')) {
    function planInsDiasRestantes(?string $fechaFin): ?int
    {
        if (!$fechaFin) {
            return null;
        }

        $fin = strtotime($fechaFin);

        return $fin === false ? null : (int) ceil(($fin - strtotime('today')) / 86400);
    }
}

if (!function_exists('planInsModalidadBadge')) {
    function planInsModalidadBadge(?string $modalidad): string
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

if (!function_exists('planInsEstadoBadge')) {
    function planInsEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'activa', 'activo' => ['class' => 'fp-badge fp-badge-ok', 'label' => 'Activo'],
            'vencido', 'vencida' => ['class' => 'fp-badge fp-badge-alert', 'label' => 'Vencido'],
            default => ['class' => 'fp-badge fp-badge-pending', 'label' => $estado !== '' ? ucfirst($estado) : 'Sin estado'],
        };
    }
}

$plan = $plan ?? null;
$accesos = $accesos ?? [];
$institucion = $institucion ?? [];
$pagos = $pagos ?? [];

$nombreTopbar = $_SESSION['nombre'] ?? 'Cliente institucional';
$institucionNombre = trim((string) ($institucion['nombre'] ?? ''));
$accesosActivos = array_filter($accesos, static function ($a) {
    $estado = strtolower((string) ($a['estado'] ?? ''));

    return in_array($estado, ['activo', 'activa'], true) || !empty($a['habilitado']);
});
$totalAccesos = count($accesosActivos);
$diasRestantes = $plan ? planInsDiasRestantes($plan['fecha_fin'] ?? null) : null;
$estadoPlan = planInsEstadoBadge($plan['estado'] ?? 'activo');
$estadoInstitucion = planInsEstadoBadge($institucion['estado'] ?? 'activo');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi plan institucional | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=14">
</head>
<body class="fp-panel">

<div class="fp-layout cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente institucional</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreTopbar) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Convenio corporativo</span>
                <h1>Mi plan <span>institucional</span></h1>
                <p>Consulta tu plan activo, beneficios, modalidad, accesos y estado del convenio con tu institución.</p>
            </section>

            <?php if (!$plan): ?>
                <section class="fp-stats-premium">
                    <article class="fp-stat-premium fp-stat-premium--warn">
                        <div class="fp-stat-premium-head">
                            <div class="fp-stat-premium-icon" aria-hidden="true">!</div>
                        </div>
                        <p class="fp-stat-premium-value" style="font-size:16px;">Sin plan activo</p>
                        <p class="fp-stat-premium-label">Aún no tienes un convenio vigente</p>
                    </article>
                </section>

                <article class="fp-card card fp-plan-card">
                    <div class="fp-plan-card-body" style="padding-top:24px;">
                        <div class="fp-plan-empty">
                            No tienes un plan institucional activo. Contacta al administrador de tu institución o revisa tu vinculación.
                        </div>
                        <div class="fp-plan-actions">
                            <a class="btn fp-btn" href="../../controllers/clienteIns/institucionController.php">Ver mi institución</a>
                            <a class="btn fp-btn fp-btn-outline" href="../../controllers/clienteIns/pagoController.php">Consultar pagos</a>
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
                        <p class="fp-stat-premium-value"><?= e(planInsFormatearPrecio($plan['precio'] ?? 0)) ?></p>
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
                            <p>Detalle de tu membresía institucional y vigencia del convenio.</p>
                        </div>
                        <div class="fp-plan-card-body">
                            <h2 class="fp-plan-hero-name"><?= e($plan['nombre'] ?? 'Plan institucional FigueFit') ?></h2>
                            <p class="fp-plan-desc"><?= e($plan['descripcion'] ?? 'Plan asociado al convenio institucional.') ?></p>

                            <div class="fp-plan-badges">
                                <span class="<?= e(planInsModalidadBadge($plan['modalidad'] ?? '')) ?>">
                                    <?= e(strtoupper($plan['modalidad'] ?? 'MODALIDAD')) ?>
                                </span>
                                <span class="<?= e($estadoPlan['class']) ?>"><?= e($estadoPlan['label']) ?></span>
                            </div>

                            <div class="fp-plan-meta">
                                <div class="fp-plan-meta-item">
                                    <strong>Precio</strong>
                                    <span><?= e(planInsFormatearPrecio($plan['precio'] ?? 0)) ?></span>
                                </div>
                                <div class="fp-plan-meta-item">
                                    <strong>Duración</strong>
                                    <span><?= e($plan['duracion'] ?? $plan['duracion_dias'] ?? '0') ?> días</span>
                                </div>
                                <div class="fp-plan-meta-item">
                                    <strong>Inicio</strong>
                                    <span><?= e(planInsFormatearFecha($plan['fecha_inicio'] ?? null)) ?></span>
                                </div>
                                <div class="fp-plan-meta-item">
                                    <strong>Vencimiento</strong>
                                    <span><?= e(planInsFormatearFecha($plan['fecha_fin'] ?? null)) ?></span>
                                </div>
                            </div>

                            <div class="fp-plan-coach">
                                <h4>Institución vinculada</h4>
                                <?php if ($institucionNombre !== ''): ?>
                                    <p class="fp-plan-coach-name"><?= e($institucionNombre) ?></p>
                                    <?php if (!empty($institucion['correo'])): ?>
                                        <p class="fp-plan-coach-meta"><?= e($institucion['correo']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($institucion['telefono'])): ?>
                                        <p class="fp-plan-coach-meta"><?= e($institucion['telefono']) ?></p>
                                    <?php endif; ?>
                                    <p style="margin-top:10px;">
                                        <span class="<?= e($estadoInstitucion['class']) ?>"><?= e($estadoInstitucion['label']) ?></span>
                                    </p>
                                <?php else: ?>
                                    <p class="fp-plan-coach-meta">Sin institución vinculada actualmente.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>

                    <article class="fp-card card fp-plan-card">
                        <div class="fp-plan-card-head fp-plan-card-head--mint">
                            <h3>Accesos habilitados</h3>
                            <p>Módulos del servicio disponibles según tu plan de convenio.</p>
                        </div>
                        <div class="fp-plan-card-body">
                            <?php if (empty($accesosActivos)): ?>
                                <div class="fp-plan-empty">
                                    No tienes accesos habilitados todavía. Cuando se active tu plan institucional, aparecerán aquí.
                                </div>
                            <?php else: ?>
                                <div class="fp-plan-acceso-list">
                                    <?php foreach ($accesosActivos as $acceso): ?>
                                        <div class="fp-plan-acceso-item">
                                            <strong><?= e($acceso['modulo'] ?? 'Módulo') ?></strong>
                                            <span class="fp-badge fp-badge-ok"><?= e(ucfirst(strtolower($acceso['estado'] ?? 'activo'))) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="fp-plan-actions">
                                <a class="btn fp-btn" href="../../controllers/clienteIns/pagoController.php">Ver pagos o renovar</a>
                                <a class="btn fp-btn fp-btn-outline" href="../../controllers/clienteIns/institucionController.php">Ver institución</a>
                            </div>
                        </div>
                    </article>
                </div>

            <?php endif; ?>

            <?php if (!empty($pagos)): ?>
                <article class="fp-card card fp-plan-card" style="margin-top:24px;">
                    <div class="fp-plan-card-head">
                        <h3>Historial de pagos</h3>
                        <p>Registros de pago asociados a tu convenio institucional.</p>
                    </div>
                    <div class="fp-plan-card-body">
                        <div class="fp-table-wrap">
                            <table class="fp-table-premium fp-table-fluid">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th style="width:18%;">Monto</th>
                                        <th class="col-estado">Estado</th>
                                        <th style="width:18%;">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos as $pago): ?>
                                        <tr>
                                            <td><?= e($pago['plan'] ?? $pago['plan_id'] ?? 'Plan') ?></td>
                                            <td><span class="fp-money"><small>$</small><?= e(number_format((float) ($pago['monto'] ?? 0), 0, ',', '.')) ?></span></td>
                                            <td><span class="fp-badge fp-badge-pending"><?= e(ucfirst($pago['estado'] ?? 'pendiente')) ?></span></td>
                                            <td><?= e(planInsFormatearFecha($pago['fecha'] ?? null)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

        </main>
    </div>
</div>
</body>
</html>
