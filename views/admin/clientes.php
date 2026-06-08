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

if (!function_exists('clienteEstadoBadge')) {
    function clienteEstadoBadge(?string $estado): array
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

if (!function_exists('clienteEsActivo')) {
    function clienteEsActivo(?string $estado): bool
    {
        return strtolower(trim((string) $estado)) === 'activo';
    }
}

if (!function_exists('clienteTipoLabel')) {
    function clienteTipoLabel(?string $tipo): string
    {
        $tipo = strtolower(trim((string) $tipo));

        return match ($tipo) {
            'institucional' => 'Institucional',
            'individual' => 'Individual',
            default => $tipo !== '' ? ucfirst($tipo) : 'Individual',
        };
    }
}

$clientes = $clientes ?? [];
$cliente = $cliente ?? null;
$pagos = $pagos ?? [];
$plan = $plan ?? null;
$coach = $coach ?? null;

$totalClientes = count($clientes);
$totalActivos = count(array_filter($clientes, fn($c) => clienteEsActivo($c['estado'] ?? '')));
$totalInstitucionales = count(array_filter(
    $clientes,
    fn($c) => strtolower(trim((string) ($c['tipo_cliente'] ?? ''))) === 'institucional'
));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=6">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Gestión de membresías</span>
            <h1>Clientes</h1>
            <p>Gestiona clientas fijas, clientes aprobados, modalidad, estado y trazabilidad del servicio.</p>
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
                            <circle cx="9" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M4 20c0-3.3 2.2-5.5 5-5.5s5 2.2 5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M16 7h5M18.5 4.5V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalClientes) ?></p>
                <p class="fp-stat-premium-label">Total clientes registrados</p>
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
                <p class="fp-stat-premium-label">Clientes activos en la plataforma</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalInstitucionales) ?></p>
                <p class="fp-stat-premium-label">Clientes institucionales</p>
            </article>
        </section>

        <section class="card fp-clientes-unified">
            <div class="fp-clientes-unified-head">
                <h3>Gestión de clientes</h3>
            </div>

            <div class="fp-clientes-form-block">
                <form class="fp-form-premium" action="../../controllers/admin/clienteController.php?accion=guardarClienteFijo" method="POST">
                    <div class="fp-form-grid">
                        <div class="fp-field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" placeholder="María" required>
                        </div>

                        <div class="fp-field">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" placeholder="García" required>
                        </div>

                        <div class="fp-field">
                            <label for="identificacion">Identificación</label>
                            <input type="text" id="identificacion" name="identificacion" placeholder="Documento" required>
                        </div>

                        <div class="fp-field">
                            <label for="edad">Edad</label>
                            <input type="number" id="edad" name="edad" min="12" placeholder="25" required>
                        </div>

                        <div class="fp-field">
                            <label for="correo">Correo</label>
                            <input type="email" id="correo" name="correo" placeholder="cliente@correo.com" required>
                        </div>

                        <div class="fp-field">
                            <label for="celular">Celular</label>
                            <input type="text" id="celular" name="celular" placeholder="300 123 4567" required>
                        </div>

                        <div class="fp-field">
                            <label for="tipo_cliente">Tipo</label>
                            <select id="tipo_cliente" name="tipo_cliente" required>
                                <option value="individual">Individual</option>
                                <option value="institucional">Institucional</option>
                            </select>
                        </div>

                        <div class="fp-field">
                            <label for="contrasena">Contraseña</label>
                            <input type="password" id="contrasena" name="contrasena" minlength="6" placeholder="Opcional" autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="fp-form-submit" style="max-width:220px;margin-top:6px;">Registrar clienta</button>
                    <span class="fp-field-hint" style="display:block;margin-top:8px;">Si no defines contraseña, se usará el número de identificación.</span>
                </form>
            </div>

            <div class="fp-clientes-list-block">
                <h4>Listado de clientes</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Cliente</th>
                                <th class="col-contacto">Contacto</th>
                                <th class="col-tipo">Tipo</th>
                                <th class="col-estado">Estado</th>
                                <th class="col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="5">No hay clientes registrados todavía.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($clientes as $item): ?>
                                <?php
                                $estadoBadge = clienteEstadoBadge($item['estado'] ?? '');
                                $clienteId = (int) ($item['id'] ?? 0);
                                $activo = clienteEsActivo($item['estado'] ?? '');
                                ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e($item['nombre'] ?? 'Sin nombre') ?></strong>
                                            <span>ID <?= e($item['identificacion'] ?? '—') ?></span>
                                            <?php if (!empty($item['edad'])): ?>
                                                <span class="fp-cell-highlight"><?= e($item['edad']) ?> años</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fp-cell-stack">
                                            <span class="fp-cell-highlight"><?= e($item['correo'] ?? 'Sin correo') ?></span>
                                            <span><?= e($item['celular'] ?? $item['telefono'] ?? '—') ?></span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="fp-tag-inline"><?= e(clienteTipoLabel($item['tipo_cliente'] ?? 'individual')) ?></span>
                                    </td>

                                    <td>
                                        <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                    </td>

                                    <td>
                                        <div class="fp-row-actions">
                                            <a class="btn fp-btn-sm fp-btn-outline"
                                               href="../../controllers/admin/clienteController.php?accion=detalle&id=<?= e($clienteId) ?>">
                                                Ver
                                            </a>

                                            <?php if ($activo): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline"
                                                   href="../../controllers/admin/clienteController.php?accion=cambiarEstado&id=<?= e($clienteId) ?>&estado=inactivo"
                                                   style="border-color:rgba(255,47,160,0.35)!important;color:var(--fp-fuchsia)!important;">
                                                    Inactivar
                                                </a>
                                            <?php else: ?>
                                                <a class="btn fp-btn-sm btn-green"
                                                   href="../../controllers/admin/clienteController.php?accion=cambiarEstado&id=<?= e($clienteId) ?>&estado=activo">
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

        <?php if ($cliente): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle del cliente</h3>

                <dl class="fp-cliente-detail">
                    <div class="fp-cliente-detail-item">
                        <dt>Nombre completo</dt>
                        <dd><?= e($cliente['nombre'] ?? '—') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Correo</dt>
                        <dd><?= e($cliente['correo'] ?? '—') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Identificación</dt>
                        <dd><?= e($cliente['identificacion'] ?? '—') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Estado</dt>
                        <dd>
                            <?php $detBadge = clienteEstadoBadge($cliente['estado'] ?? ''); ?>
                            <span class="<?= e($detBadge['class']) ?>"><?= e($detBadge['label']) ?></span>
                        </dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Plan activo</dt>
                        <dd><?= e($plan['nombre'] ?? 'Sin plan activo') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Coach asignado</dt>
                        <dd><?= e($coach['nombre'] ?? 'Sin coach asignado') ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Pagos registrados</dt>
                        <dd><?= e((string) count($pagos)) ?></dd>
                    </div>
                    <div class="fp-cliente-detail-item">
                        <dt>Tipo</dt>
                        <dd><?= e(clienteTipoLabel($cliente['tipo_cliente'] ?? 'individual')) ?></dd>
                    </div>
                </dl>

                <div class="fp-row-actions" style="margin-top:8px;max-width:none;">
                    <a class="btn fp-btn-outline" href="../../controllers/admin/clienteController.php">Volver al listado</a>
                </div>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
