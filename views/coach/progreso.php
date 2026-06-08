<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Clientes asignados
$progresos = $progresos ?? []; // Progresos registrados
$avanceVirtual = $avanceVirtual ?? []; // Seguimiento virtual
$reporteClientes = $reporteClientes ?? []; // Reporte clientes
$reporteProgreso = $reporteProgreso ?? []; // Reporte progreso
$tituloPagina = 'Progreso Coach | FigueFit';
$vistaActiva = 'progreso';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <h1>Progreso y <span>seguimiento</span></h1>
            <p>Revisa avances físicos, seguimiento virtual y registra observaciones profesionales.</p>
        </section>

        <section class="grid">

            <div>
                <div class="card">
                    <h3>Registrar observación</h3>

                    <form action="../../controllers/coach/progresoController.php?accion=observacion" method="POST">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($cliente['nombre'] ?? 'Cliente') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Observación profesional</label>
                        <textarea name="observacion" placeholder="Escribe una recomendación o análisis del progreso..." required></textarea>

                        <button type="submit">Guardar observación</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Seguimiento virtual</h3>

                    <?php if (empty($avanceVirtual)): ?>
                        <div class="empty">No hay seguimiento virtual registrado.</div>
                    <?php endif; ?>

                    <?php foreach ($avanceVirtual as $item): ?>
                        <div class="virtual-item">
                            <strong><?= e($item['cliente'] ?? 'Cliente') ?></strong>
                            <p>Avance: <?= e((string) (int) ($item['avance'] ?? 0)) ?>%</p>
                            <span class="badge"><?= e(strtolower((string) ($item['estado'] ?? 'sin iniciar'))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Progresos registrados</h3>

                    <?php if (empty($progresos)): ?>
                        <div class="empty">No hay registros de progreso todavía.</div>
                    <?php endif; ?>

                    <?php foreach ($progresos as $item): ?>
                        <div class="progress-item">
                            <strong><?= e($item['cliente'] ?? 'Cliente') ?></strong>
                            <p><strong>Peso:</strong> <?= e($item['peso'] ?? '0') ?> kg</p>
                            <p><strong>Medidas:</strong> <?= e($item['medidas'] ?? 'No registradas') ?></p>
                            <p><?= e($item['observacion'] ?? 'Sin observación') ?></p>
                            <span class="badge"><?= e($item['fecha'] ?? 'Fecha no registrada') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h3>Resumen rápido</h3>
                    <p style="color: var(--fp-text-soft); font-size: 13px;">
                        Consulta reportes detallados en
                        <a href="../../controllers/coach/reporteController.php">Reportes</a>
                        o seguimiento de videos en
                        <a href="../../controllers/coach/seguimientoVirtualController.php">Seguimiento virtual</a>.
                    </p>
                </div>
            </div>

        </section>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>