<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('notificacionEsLeida')) {
    function notificacionEsLeida(array $item): bool
    {
        if (isset($item['leida'])) {
            return (int) $item['leida'] === 1;
        }

        return strtolower(trim((string) ($item['estado'] ?? ''))) === 'leida';
    }
}

if (!function_exists('notificacionMensaje')) {
    function notificacionMensaje(array $item): string
    {
        return trim((string) ($item['mensaje'] ?? $item['contenido'] ?? ''));
    }
}

if (!function_exists('notificacionFecha')) {
    function notificacionFecha(array $item): string
    {
        $fecha = trim((string) ($item['fecha'] ?? $item['fecha_envio'] ?? $item['creado_en'] ?? ''));

        if ($fecha === '') {
            return '—';
        }

        $ts = strtotime($fecha);

        return $ts ? date('d M Y · H:i', $ts) : e($fecha);
    }
}

if (!function_exists('notificacionTipoBadge')) {
    function notificacionTipoBadge(?string $tipo): array
    {
        $tipo = strtoupper(trim((string) $tipo));

        return match ($tipo) {
            'PAGO', 'PAGOS' => ['class' => 'fp-badge fp-badge-warn', 'label' => 'Pago'],
            'SOLICITUD', 'INGRESO' => ['class' => 'fp-badge fp-badge-pending', 'label' => 'Solicitud'],
            'ACCESO', 'PLAN' => ['class' => 'fp-badge fp-badge-ok', 'label' => 'Acceso'],
            default => ['class' => 'fp-badge', 'label' => $tipo !== '' ? ucfirst(strtolower($tipo)) : 'Sistema'],
        };
    }
}

$notificaciones = $notificaciones ?? [];

$totalNotif = count($notificaciones);
$totalNoLeidas = count(array_filter($notificaciones, fn($n) => !notificacionEsLeida($n)));
$totalLeidas = $totalNotif - $totalNoLeidas;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=11">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Centro de alertas</span>
            <h1>Notificaciones</h1>
            <p>Alertas importantes sobre solicitudes, pagos, accesos, vencimientos y actividad del sistema.</p>
        </section>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M12 4a5 5 0 00-5 5v2.5l-1.5 2.5h13L17 11.5V9a5 5 0 00-5-5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M10 18a2 2 0 004 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalNotif) ?></p>
                <p class="fp-stat-premium-label">Alertas totales</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalNoLeidas) ?></p>
                <p class="fp-stat-premium-label">Sin leer</p>
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
                <p class="fp-stat-premium-value"><?= e((string) $totalLeidas) ?></p>
                <p class="fp-stat-premium-label">Leídas</p>
            </article>
        </section>

        <section class="card fp-panel-unified">
            <div class="fp-panel-unified-head">
                <h3>Bandeja de alertas</h3>
            </div>

            <div class="fp-panel-list-block">
                <?php if (empty($notificaciones)): ?>
                    <div class="fp-notif-empty">
                        <h4>Sin notificaciones</h4>
                        <p>No tienes alertas pendientes en este momento.</p>
                    </div>
                <?php else: ?>
                    <div class="fp-notif-list">
                        <?php foreach ($notificaciones as $item): ?>
                            <?php
                            $leida = notificacionEsLeida($item);
                            $notifId = (int) ($item['id'] ?? $item['id_notificacion'] ?? 0);
                            $tipoBadge = notificacionTipoBadge($item['tipo'] ?? $item['tipo_notificacion'] ?? 'SISTEMA');
                            ?>
                            <article class="fp-notif-card <?= $leida ? 'fp-notif-card--read' : '' ?>">
                                <div class="fp-notif-card-head">
                                    <h4><?= e($item['titulo'] ?? 'Notificación') ?></h4>
                                    <span class="<?= e($tipoBadge['class']) ?>"><?= e($tipoBadge['label']) ?></span>
                                </div>

                                <p><?= e(notificacionMensaje($item)) ?></p>

                                <div class="fp-notif-meta">
                                    <span class="fp-notif-date"><?= notificacionFecha($item) ?></span>

                                    <div class="fp-notif-actions">
                                        <?php if (!$leida): ?>
                                            <a class="btn fp-btn-sm fp-btn-outline-mint"
                                               href="../../controllers/admin/notificacionController.php?accion=marcarLeida&id=<?= e($notifId) ?>">
                                                Marcar leída
                                            </a>
                                        <?php else: ?>
                                            <span class="fp-tag-inline" style="opacity:0.85;">Leída</span>
                                        <?php endif; ?>

                                        <a class="btn fp-btn-sm fp-btn-outline"
                                           href="../../controllers/admin/notificacionController.php?accion=eliminar&id=<?= e($notifId) ?>"
                                           style="border-color:rgba(255,47,160,0.35)!important;color:var(--fp-fuchsia)!important;">
                                            Eliminar
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>
</body>
</html>
