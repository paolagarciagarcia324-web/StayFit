<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$clientes = $clientes ?? [];
$cliente = $cliente ?? null;
$plan = $plan ?? null;
$progreso = $progreso ?? [];
$esDetalle = !empty($cliente);

$tituloPagina = $esDetalle ? 'Detalle cliente | FigueFit Coach' : 'Mis clientas | FigueFit Coach';
$vistaActiva = 'clientes';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

<?php if ($esDetalle): ?>
    <?php
    $nombreCliente = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
    if ($nombreCliente === '' && !empty($cliente['nombre'])) {
        $nombreCliente = trim((string) $cliente['nombre']);
    }
    $iniciales = '';
    foreach (preg_split('/\s+/', $nombreCliente) as $parte) {
        if ($parte !== '') {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
        }
        if (mb_strlen($iniciales) >= 2) {
            break;
        }
    }
    $iniciales = $iniciales !== '' ? $iniciales : 'CL';
    ?>

    <section class="fp-hero hero page-header">
        <span class="fp-hero-tag">Seguimiento</span>
        <h1><?= e($nombreCliente !== '' ? $nombreCliente : 'Cliente') ?></h1>
        <p>Detalle de la clienta asignada a tu acompañamiento.</p>
    </section>

    <div class="fp-plan-actions" style="margin-bottom: 22px;">
        <a class="fp-btn fp-btn-outline" href="../../controllers/coach/clientesController.php">← Volver al listado</a>
    </div>

    <div class="fp-perfil-grid">
        <article class="fp-card card fp-perfil-card">
            <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                <h3>Información</h3>
                <p>Datos de contacto y perfil de la clienta.</p>
            </div>
            <div class="fp-perfil-card-body">
                <div class="fp-perfil-resumen">
                    <div class="fp-perfil-resumen-item">
                        <div class="fp-perfil-resumen-icon fp-coach-avatar" aria-hidden="true"><?= e($iniciales) ?></div>
                        <div>
                            <strong><?= e($nombreCliente !== '' ? $nombreCliente : 'Cliente') ?></strong>
                            <span><?= e($cliente['correo'] ?? 'Sin correo') ?></span>
                        </div>
                    </div>
                    <div class="fp-perfil-resumen-item">
                        <div class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">T</div>
                        <div>
                            <strong>Tipo</strong>
                            <span><?= e($cliente['tipo_cliente'] ?? 'INDIVIDUAL') ?></span>
                        </div>
                    </div>
                </div>
                <p class="fp-plan-desc" style="margin-top: 16px;">
                    <strong style="color: var(--fp-white);">Objetivos:</strong>
                    <?= e($cliente['objetivos'] ?? 'Sin objetivos registrados') ?>
                </p>
            </div>
        </article>

        <article class="fp-card card fp-perfil-card">
            <div class="fp-perfil-card-head fp-perfil-card-head--mint">
                <h3>Plan activo</h3>
                <p>Membresía y vigencia actual.</p>
            </div>
            <div class="fp-perfil-card-body">
                <?php if (!$plan): ?>
                    <div class="fp-plan-empty">Sin plan activo registrado.</div>
                <?php else: ?>
                    <p class="fp-plan-hero-name"><?= e($plan['nombre'] ?? $plan['titulo'] ?? 'Plan') ?></p>
                    <div class="fp-plan-badges">
                        <span class="fp-badge fp-badge-ok"><?= e($plan['modalidad'] ?? 'N/D') ?></span>
                        <span class="fp-badge fp-badge-pending"><?= e($plan['estado'] ?? $plan['estado_plan_cliente'] ?? 'ACTIVO') ?></span>
                    </div>
                    <p class="fp-plan-desc">
                        Vigencia: <?= e($plan['fecha_inicio'] ?? '—') ?> — <?= e($plan['fecha_fin'] ?? '—') ?>
                    </p>
                <?php endif; ?>
            </div>
        </article>
    </div>

    <article class="fp-card card" style="margin-top: 22px;">
        <div class="fp-plan-card-head">
            <h3>Progreso reciente</h3>
            <p>Últimos registros físicos de la clienta.</p>
        </div>
        <div class="fp-plan-card-body">
            <?php if (empty($progreso)): ?>
                <div class="fp-progreso-empty">
                    <strong>Sin registros</strong>
                    <p>Aún no hay progreso registrado.</p>
                </div>
            <?php else: ?>
                <div class="fp-progreso-timeline">
                    <?php foreach ($progreso as $registro): ?>
                        <article class="fp-progreso-item">
                            <div class="fp-progreso-item-accent" aria-hidden="true"></div>
                            <div class="fp-progreso-item-main">
                                <header class="fp-progreso-item-head">
                                    <strong class="fp-progreso-item-peso"><?= e($registro['peso'] ?? 'N/D') ?> kg</strong>
                                    <time class="fp-progreso-item-fecha"><?= e($registro['fecha'] ?? $registro['fecha_registro'] ?? '') ?></time>
                                </header>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </article>

<?php else: ?>

    <section class="fp-hero hero page-header">
        <span class="fp-hero-tag">Tu cartera</span>
        <h1>Mis <span>clientas</span></h1>
        <p>Clientas que el administrador te ha asignado según su plan.</p>
    </section>

    <section class="fp-stats-premium">
        <article class="fp-stat-premium fp-stat-premium--fuchsia">
            <div class="fp-stat-premium-head">
                <div class="fp-stat-premium-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4-4v-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
            <p class="fp-stat-premium-value"><?= e((string) count($clientes)) ?></p>
            <p class="fp-stat-premium-label">Clientas asignadas</p>
        </article>
    </section>

    <article class="fp-card card">
        <div class="fp-plan-card-head">
            <h3>Listado de clientas</h3>
            <p>Consulta el detalle de cada clienta y su plan.</p>
        </div>
        <div class="fp-plan-card-body">
            <?php if (empty($clientes)): ?>
                <div class="fp-pagos-empty">
                    <strong>Sin clientas asignadas</strong>
                    <p>El administrador debe asignarte clientes desde el panel de Asignaciones.</p>
                </div>
            <?php else: ?>
                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-pagos">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Correo</th>
                                <th>Tipo</th>
                                <th>Estado plan</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $item): ?>
                                <?php
                                $nombre = trim(($item['nombre'] ?? '') . ' ' . ($item['apellido'] ?? ''));
                                if ($nombre === '' && !empty($item['nombre'])) {
                                    $nombre = trim((string) $item['nombre']);
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e($nombre !== '' ? $nombre : 'Cliente') ?></strong>
                                        </div>
                                    </td>
                                    <td><?= e($item['correo'] ?? '') ?></td>
                                    <td><span class="fp-tag-inline"><?= e($item['tipo_cliente'] ?? 'INDIVIDUAL') ?></span></td>
                                    <td><span class="fp-badge fp-badge-ok"><?= e($item['estado_plan'] ?? 'ACTIVO') ?></span></td>
                                    <td>
                                        <a class="fp-btn-sm fp-btn-outline-mint" href="../../controllers/coach/clientesController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">Ver</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </article>

<?php endif; ?>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>
