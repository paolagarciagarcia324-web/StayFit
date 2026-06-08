<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$clientes = $clientes ?? [];
$avanceVirtual = $avanceVirtual ?? [];
$avancePorVideo = $avancePorVideo ?? [];
$progresos = $progresos ?? [];

$avancePorCliente = [];
foreach ($avanceVirtual as $item) {
    $avancePorCliente[(int) ($item['id_cliente'] ?? 0)] = (int) ($item['avance'] ?? 0);
}

$tituloPagina = 'Seguimiento virtual | FigueFit Coach';
$vistaActiva = 'seguimientoVirtual';

$totalVirtuales = count($clientes);
$totalAvances = count($avanceVirtual);

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <span class="fp-hero-tag">Contenido pregrabado</span>
            <h1>Seguimiento <span>virtual</span></h1>
            <p>Monitorea el avance de tus clientas con plan virtual o mixto y registra observaciones.</p>
        </section>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4-4v-2" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalVirtuales) ?></p>
                <p class="fp-stat-premium-label">Clientas con plan virtual</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--mint">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M4 8l8-4 8 4v8l-8 4-8-4V8z" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalAvances) ?></p>
                <p class="fp-stat-premium-label">Registros de avance en videos</p>
            </article>
        </section>

        <div class="fp-perfil-grid">
            <article class="fp-card card fp-perfil-card">
                <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                    <h3>Registrar observación</h3>
                    <p>Deja una nota sobre el seguimiento virtual de una clienta.</p>
                </div>
                <div class="fp-perfil-card-body">
                    <form class="fp-form-premium" action="../../controllers/coach/seguimientoVirtualController.php?accion=observacion" method="POST">
                        <div class="fp-field fp-field--full">
                            <label for="seg-cliente">Clienta</label>
                            <select id="seg-cliente" name="cliente_id" required>
                                <option value="">Seleccione clienta</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <?php
                                    $nombre = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
                                    if ($nombre === '' && !empty($cliente['nombre'])) {
                                        $nombre = trim((string) $cliente['nombre']);
                                    }
                                    ?>
                                    <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($nombre !== '' ? $nombre : 'Clienta') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fp-field fp-field--full">
                            <label for="seg-obs">Observación</label>
                            <textarea id="seg-obs" name="observacion" rows="4" placeholder="Comentario sobre avance, constancia o recomendaciones..." required></textarea>
                        </div>
                        <button type="submit" class="fp-form-submit">Guardar observación</button>
                    </form>
                </div>
            </article>

            <article class="fp-card card fp-perfil-card">
                <div class="fp-perfil-card-head fp-perfil-card-head--mint">
                    <h3>Clientas virtuales</h3>
                    <p>Planes VIRTUAL o MIXTO asignados a ti.</p>
                </div>
                <div class="fp-perfil-card-body">
                    <?php if (empty($clientes)): ?>
                        <div class="fp-pagos-empty">
                            <strong>Sin clientas virtuales</strong>
                            <p>No tienes clientas con plan virtual o mixto activo.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <?php
                            $nombre = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
                            if ($nombre === '' && !empty($cliente['nombre'])) {
                                $nombre = trim((string) $cliente['nombre']);
                            }
                            ?>
                            <div class="fp-timeline-item item">
                                <strong><?= e($nombre !== '' ? $nombre : 'Clienta') ?></strong>
                                <p><?= e($cliente['objetivos'] ?? 'Sin objetivos') ?></p>
                                <span class="fp-tag-inline"><?= e($cliente['modalidad'] ?? 'VIRTUAL') ?></span>
                                <a class="fp-btn-sm fp-btn-outline-mint" href="../../controllers/coach/seguimientoVirtualController.php?accion=detalle&cliente_id=<?= e($cliente['id'] ?? '') ?>">Ver detalle</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
        </div>

        <article class="fp-card card" style="margin-top: 22px;">
            <div class="fp-plan-card-head">
                <h3>Avance en videos</h3>
                <p>Progreso registrado por tus clientas en el contenido virtual.</p>
            </div>
            <div class="fp-plan-card-body">
                <?php if (empty($avancePorVideo)): ?>
                    <div class="fp-progreso-empty">
                        <strong>Sin avances registrados</strong>
                        <p>Cuando tus clientas vean o completen videos, aparecerán aquí.</p>
                    </div>
                <?php else: ?>
                    <div class="fp-table-wrap">
                        <table class="fp-table-premium fp-table-pagos">
                            <thead>
                                <tr>
                                    <th>Clienta</th>
                                    <th>Avance</th>
                                    <th>Estado</th>
                                    <th>Última actividad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avancePorVideo as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['cliente'] ?? 'Clienta') ?></strong></td>
                                        <td><?= e((string) (int) ($item['avance'] ?? 0)) ?>%</td>
                                        <td><span class="fp-badge fp-badge-ok"><?= e(strtolower((string) ($item['estado'] ?? $item['estado_progreso'] ?? 'en curso'))) ?></span></td>
                                        <td><?= e($item['ultimo_acceso'] ?? $item['completado_en'] ?? $item['actualizado_en'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </article>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>
