<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pagoEstadoBadgeCliente')) {
    function pagoEstadoBadgeCliente(?string $estado): array
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

if (!function_exists('pagoEsAprobadoCliente')) {
    function pagoEsAprobadoCliente(?string $estado): bool
    {
        return in_array(strtolower(trim((string) $estado)), ['validado', 'aprobado', 'pagado'], true);
    }
}

if (!function_exists('formatearMontoCliente')) {
    function formatearMontoCliente($monto): string
    {
        return '$' . number_format((float) $monto, 0, ',', '.');
    }
}

if (!function_exists('formatearFechaPagoCliente')) {
    function formatearFechaPagoCliente(?string $fecha): array
    {
        if (!$fecha) {
            return ['fecha' => '—', 'hora' => ''];
        }

        $ts = strtotime($fecha);
        if ($ts === false) {
            return ['fecha' => (string) $fecha, 'hora' => ''];
        }

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        return [
            'fecha' => date('d', $ts) . ' ' . $meses[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts),
            'hora' => date('H:i', $ts),
        ];
    }
}

$pagos = $pagos ?? [];
$planes = $planes ?? [];
$planActivo = $planActivo ?? null;
$nombreTopbar = $_SESSION['nombre'] ?? 'Cliente';

$planSeleccionado = $planActivo['id_plan'] ?? $planActivo['id'] ?? '';

$totalPagos = count($pagos);
$totalPendientes = 0;
$totalAprobados = 0;
$totalMontoValidado = 0.0;

foreach ($pagos as $p) {
    $estado = strtolower((string) ($p['estado'] ?? 'pendiente'));
    if ($estado === 'pendiente') {
        $totalPendientes++;
    }
    if (pagoEsAprobadoCliente($estado)) {
        $totalAprobados++;
        $totalMontoValidado += (float) ($p['monto'] ?? 0);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=17">
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
                <h1><span>Pagos</span></h1>
                <p>Consulta tu historial, envía comprobantes y mantén activo tu acceso a FigueFit.</p>
            </section>

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
                    <p class="fp-stat-premium-label">Pagos registrados</p>
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
                    <p class="fp-stat-premium-label">En revisión</p>
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
                    <p class="fp-stat-premium-value"><?= e(formatearMontoCliente($totalMontoValidado)) ?></p>
                    <p class="fp-stat-premium-label"><?= e((string) $totalAprobados) ?> pago(s) validado(s)</p>
                </article>
            </section>

            <div class="fp-pagos-grid">
                <article class="fp-card card fp-pagos-card">
                    <div class="fp-pagos-card-head fp-pagos-card-head--fuchsia">
                        <h3>Enviar nuevo pago</h3>
                        <p>Sube tu comprobante para que el equipo valide tu acceso al plan.</p>
                    </div>
                    <div class="fp-pagos-card-body">
                        <div class="fp-pagos-tip">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M12 10v6M12 7h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                            <p>Aceptamos transferencia, Nequi y Daviplata. El estado quedará <strong>pendiente</strong> hasta que un administrador lo valide.</p>
                        </div>

                        <form class="fp-form-premium fp-pagos-form" action="../../controllers/cliente/pagoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                            <div class="fp-form-grid">
                                <div class="fp-field fp-field--full">
                                    <label for="pago-plan">Plan</label>
                                    <?php if (!empty($planes)): ?>
                                        <select id="pago-plan" name="plan_id" required>
                                            <option value="">Seleccione un plan</option>
                                            <?php foreach ($planes as $plan): ?>
                                                <?php $planId = $plan['id'] ?? $plan['id_plan'] ?? ''; ?>
                                                <option value="<?= e($planId) ?>" <?= (string) $planSeleccionado === (string) $planId ? 'selected' : '' ?>>
                                                    <?= e($plan['nombre'] ?? 'Plan') ?> · <?= e(formatearMontoCliente($plan['precio'] ?? 0)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="number" id="pago-plan" name="plan_id" placeholder="ID del plan" value="<?= e((string) $planSeleccionado) ?>" required>
                                        <span class="fp-field-hint">Ingresa el ID del plan asignado por tu coach.</span>
                                    <?php endif; ?>
                                </div>

                                <div class="fp-field">
                                    <label for="pago-monto">Monto pagado</label>
                                    <input type="number" id="pago-monto" name="monto" min="0" step="100" placeholder="Ej: 90000" required>
                                </div>

                                <div class="fp-field">
                                    <label for="pago-tipo">Tipo de cuenta</label>
                                    <select id="pago-tipo" name="tipo_cuenta" required>
                                        <option value="">Seleccione una opción</option>
                                        <option value="ahorros">Ahorros</option>
                                        <option value="corriente">Corriente</option>
                                        <option value="nequi">Nequi</option>
                                        <option value="daviplata">Daviplata</option>
                                    </select>
                                </div>

                                <div class="fp-field fp-field--full">
                                    <label for="pago-numero">Número de cuenta / celular</label>
                                    <input type="text" id="pago-numero" name="numero_cuenta" placeholder="Ej: 3001234567" required>
                                </div>

                                <div class="fp-field fp-field--full">
                                    <label for="pago-comprobante">Comprobante</label>
                                    <div class="fp-progreso-file">
                                        <input type="file" id="pago-comprobante" name="comprobante" accept="image/*,.pdf" required>
                                        <label for="pago-comprobante" class="fp-progreso-file-label">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="1.8"/>
                                                <path d="M14 2v6h6M8 13h8M8 17h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                            <span>Adjuntar comprobante</span>
                                            <small>Imagen o PDF · obligatorio</small>
                                        </label>
                                        <span class="fp-progreso-file-name" data-file-name></span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="fp-form-submit fp-pagos-submit">Enviar comprobante</button>
                        </form>
                    </div>
                </article>

                <article class="fp-card card fp-pagos-card">
                    <div class="fp-pagos-card-head fp-pagos-card-head--mint">
                        <h3>Historial de pagos</h3>
                        <p>Todos tus comprobantes enviados y su estado de validación.</p>
                    </div>
                    <div class="fp-pagos-card-body">
                        <?php if (empty($pagos)): ?>
                            <div class="fp-pagos-empty">
                                <div class="fp-pagos-empty-icon" aria-hidden="true">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                        <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M3 10h18" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </div>
                                <strong>Sin pagos registrados</strong>
                                <p>Cuando envíes tu primer comprobante, aparecerá aquí con su estado.</p>
                            </div>
                        <?php else: ?>
                            <div class="fp-table-wrap">
                                <table class="fp-table-premium fp-table-pagos">
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pagos as $pago): ?>
                                            <?php
                                            $badge = pagoEstadoBadgeCliente($pago['estado'] ?? 'pendiente');
                                            $fechaParts = formatearFechaPagoCliente($pago['fecha'] ?? '');
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fp-cell-stack">
                                                        <strong><?= e($pago['plan'] ?? $pago['plan_id'] ?? 'Plan') ?></strong>
                                                        <?php if (!empty($pago['metodo_pago'])): ?>
                                                            <span class="fp-tag-inline"><?= e(strtolower((string) $pago['metodo_pago'])) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fp-pagos-monto"><?= e(formatearMontoCliente($pago['monto'] ?? 0)) ?></span>
                                                </td>
                                                <td>
                                                    <span class="<?= e($badge['class']) ?>"><?= e($badge['label']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="fp-cell-stack">
                                                        <strong><?= e($fechaParts['fecha']) ?></strong>
                                                        <?php if ($fechaParts['hora'] !== ''): ?>
                                                            <span><?= e($fechaParts['hora']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

        </main>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('pago-comprobante');
    var nameEl = document.querySelector('[data-file-name]');
    if (!input || !nameEl) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        nameEl.textContent = file ? file.name : '';
    });
})();
</script>
</body>
</html>
