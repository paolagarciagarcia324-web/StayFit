<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('totalFilasReporte')) {
    function totalFilasReporte(array $filas): int
    {
        $total = 0;
        foreach ($filas as $fila) {
            $total += (int) ($fila['total'] ?? 0);
        }

        return $total;
    }
}

$reporteClientes = $reporteClientes ?? [];
$reporteProgreso = $reporteProgreso ?? [];
$reporteRutinas = $reporteRutinas ?? [];
$reporteSesiones = $reporteSesiones ?? [];

$tituloPagina = 'Reportes | FigueFit Coach';
$vistaActiva = 'reportes';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <span class="fp-hero-tag">Indicadores</span>
            <h1><span>Reportes</span></h1>
            <p>Resumen de clientas, progreso físico, rutinas y sesiones bajo tu acompañamiento.</p>
        </section>

        <section class="fp-stats-premium">
            <article class="fp-stat-premium fp-stat-premium--fuchsia">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">C</div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) totalFilasReporte($reporteClientes)) ?></p>
                <p class="fp-stat-premium-label">Clientas registradas</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--mint">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">P</div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) totalFilasReporte($reporteProgreso)) ?></p>
                <p class="fp-stat-premium-label">Registros de progreso</p>
            </article>

            <article class="fp-stat-premium">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">R</div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) totalFilasReporte($reporteRutinas)) ?></p>
                <p class="fp-stat-premium-label">Rutinas gestionadas</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">S</div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) totalFilasReporte($reporteSesiones)) ?></p>
                <p class="fp-stat-premium-label">Sesiones programadas</p>
            </article>
        </section>

        <div class="fp-perfil-grid">
            <?php
            $bloques = [
                ['Clientas por estado', $reporteClientes],
                ['Progreso por estado', $reporteProgreso],
                ['Rutinas por estado', $reporteRutinas],
                ['Sesiones por estado', $reporteSesiones],
            ];
            foreach ($bloques as [$titulo, $filas]):
            ?>
            <article class="fp-card card fp-perfil-card">
                <div class="fp-plan-card-head">
                    <h3><?= e($titulo) ?></h3>
                </div>
                <div class="fp-plan-card-body">
                    <?php if (empty($filas)): ?>
                        <div class="fp-pagos-empty">
                            <strong>Sin datos</strong>
                            <p>No hay registros para mostrar en este indicador.</p>
                        </div>
                    <?php else: ?>
                        <div class="fp-table-wrap">
                            <table class="fp-table-premium">
                                <thead>
                                    <tr>
                                        <th>Estado</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filas as $fila): ?>
                                        <tr>
                                            <td><span class="fp-tag-inline"><?= e(strtolower((string) ($fila['estado'] ?? '—'))) ?></span></td>
                                            <td><strong><?= e((string) ($fila['total'] ?? 0)) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>
