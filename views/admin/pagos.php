<?php

require_once __DIR__ . '/../../config/helpers.php';

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pagoEstadoBadge')) {
    function pagoEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'validado', 'aprobado', 'pagado' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => $estado === 'validado' ? 'Validado' : ucfirst($estado),
            ],
            'rechazado', 'cancelado' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => ucfirst($estado),
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => 'Pendiente',
            ],
        };
    }
}

if (!function_exists('pagoEsAprobado')) {
    function pagoEsAprobado(?string $estado): bool
    {
        return in_array(strtolower(trim((string) $estado)), ['validado', 'aprobado', 'pagado'], true);
    }
}

if (!function_exists('formatearMonto')) {
    function formatearMonto($monto): string
    {
        return number_format((float) $monto, 0, ',', '.');
    }
}

if (!function_exists('formatearFechaPago')) {
    function formatearFechaPago(?string $fecha): string
    {
        if (!$fecha) {
            return '—';
        }

        $ts = strtotime($fecha);
        if ($ts === false) {
            return e($fecha);
        }

        return date('d M Y · H:i', $ts);
    }
}

$pagos = $pagos ?? [];
$pendientes = $pendientes ?? [];
$pago = $pago ?? null;
$comprobante = $comprobante ?? null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$totalPagos = count($pagos);
$totalPendientes = count($pendientes);
$totalAprobados = count(array_filter($pagos, fn($p) => pagoEsAprobado($p['estado'] ?? '')));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos y validación | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=4">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Panel financiero</span>
            <h1>Pagos y <span style="color:var(--fp-fuchsia)">validación</span></h1>
            <p>Revisa comprobantes, aprueba pagos y activa clientes según su modalidad.</p>
        </section>

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
                            <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M3 10h18M7 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalPagos) ?></p>
                <p class="fp-stat-premium-label">Total pagos registrados</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalPendientes) ?></p>
                <p class="fp-stat-premium-label">Pagos pendientes de revisión</p>
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
                <p class="fp-stat-premium-value"><?= e((string) $totalAprobados) ?></p>
                <p class="fp-stat-premium-label">Pagos validados y aprobados</p>
            </article>
        </section>

        <section class="card">
            <h3>Listado de pagos</h3>

            <div class="fp-table-wrap">
                <table class="fp-table-premium">
                    <thead>
                        <tr>
                            <th>Cliente / Solicitud</th>
                            <th>Plan</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                            <tr class="fp-empty-row">
                                <td colspan="6">No hay pagos registrados todavía.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($pagos as $item): ?>
                            <?php
                            $estadoBadge = pagoEstadoBadge($item['estado'] ?? 'pendiente');
                            $pagoId = (int) ($item['id'] ?? 0);
                            $esPendiente = ($item['estado'] ?? '') === 'pendiente';
                            ?>
                            <tr>
                                <td>
                                    <div class="fp-cell-stack">
                                        <strong><?= e($item['cliente'] ?? $item['solicitante'] ?? 'Sin nombre') ?></strong>
                                        <?php if (!empty($item['id_solicitud'])): ?>
                                            <span>Solicitud #<?= e($item['id_solicitud']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['metodo_pago'])): ?>
                                            <span class="fp-tag-inline"><?= e(strtolower($item['metodo_pago'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="fp-cell-stack">
                                        <strong><?= e($item['plan'] ?? 'Sin plan') ?></strong>
                                    </div>
                                </td>

                                <td>
                                    <span class="fp-money"><small>$</small><?= e(formatearMonto($item['monto'] ?? 0)) ?></span>
                                </td>

                                <td>
                                    <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                </td>

                                <td>
                                    <span style="color:var(--fp-text-soft);font-size:13px;font-weight:600;">
                                        <?= formatearFechaPago($item['fecha'] ?? '') ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="fp-row-actions">
                                        <a class="btn fp-btn-sm fp-btn-outline"
                                           href="../../controllers/admin/pagoController.php?accion=detalle&id=<?= e($pagoId) ?>">
                                            Ver detalle
                                        </a>

                                        <?php if ($esPendiente): ?>
                                            <a class="btn fp-btn-sm btn-green"
                                               href="../../controllers/admin/pagoController.php?accion=aprobar&id=<?= e($pagoId) ?>">
                                                Aprobar
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($pago): ?>
            <?php $detalleBadge = pagoEstadoBadge($pago['estado'] ?? 'pendiente'); ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle del pago #<?= e($pago['id'] ?? '') ?></h3>

                <div class="fp-pago-detail">
                    <div>
                        <dl class="fp-pago-detail-dl">
                            <div>
                                <dt>Cliente</dt>
                                <dd><?= e($pago['solicitante'] ?? $pago['cliente'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt>Estado</dt>
                                <dd><span class="<?= e($detalleBadge['class']) ?>"><?= e($detalleBadge['label']) ?></span></dd>
                            </div>
                            <div>
                                <dt>Plan</dt>
                                <dd><?= e($pago['plan'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt>Monto</dt>
                                <dd><span class="fp-money"><small>$</small><?= e(formatearMonto($pago['monto'] ?? 0)) ?></span></dd>
                            </div>
                            <div>
                                <dt>Fecha</dt>
                                <dd><?= formatearFechaPago($pago['fecha'] ?? '') ?></dd>
                            </div>
                            <?php if (!empty($pago['metodo_pago'])): ?>
                                <div>
                                    <dt>Método</dt>
                                    <dd><?= e($pago['metodo_pago']) ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($pago['referencia_pago'])): ?>
                                <div>
                                    <dt>Referencia</dt>
                                    <dd><?= e($pago['referencia_pago']) ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>

                        <?php if (($pago['estado'] ?? '') === 'pendiente'): ?>
                            <div class="fp-row-actions">
                                <a class="btn btn-green"
                                   href="../../controllers/admin/pagoController.php?accion=aprobar&id=<?= e($pago['id'] ?? '') ?>">
                                    Aprobar pago
                                </a>
                                <a class="btn fp-btn-outline" href="../../controllers/admin/pagoController.php">
                                    Volver al listado
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (($pago['estado'] ?? '') === 'pendiente'): ?>
                            <form class="fp-pago-reject" action="../../controllers/admin/pagoController.php?accion=rechazar" method="POST">
                                <input type="hidden" name="id" value="<?= e($pago['id'] ?? '') ?>">
                                <label for="observacion_rechazo">Motivo del rechazo</label>
                                <textarea id="observacion_rechazo" name="observacion" placeholder="Describe por qué se rechaza este pago…" required></textarea>
                                <button class="btn fp-badge-alert" type="submit" style="background:rgba(255,47,160,0.12);border:1px solid rgba(255,47,160,0.35);">
                                    Rechazar pago
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="fp-pago-comprobante-box">
                        <h4 style="margin:0 0 14px;font-size:14px;font-weight:800;color:var(--fp-white);">Comprobante de pago</h4>
                        <?php
                        $urlComprobante = $comprobante['url_comprobante'] ?? $comprobante['ruta_archivo'] ?? $pago['url_comprobante'] ?? $pago['comprobante_url'] ?? null;
                        $pagoIdComprobante = (int) ($pago['id'] ?? $pago['id_pago'] ?? 0);
                        require __DIR__ . '/partials/comprobanteVista.php';
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
