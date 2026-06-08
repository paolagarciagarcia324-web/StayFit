<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$reporteClientes = $reporteClientes ?? []; // Reporte clientes
$reportePagos = $reportePagos ?? []; // Reporte pagos
$reportePlanes = $reportePlanes ?? []; // Reporte planes
$reporteProgreso = $reporteProgreso ?? []; // Reporte progreso
$estadisticas = $estadisticas ?? []; // Estadísticas generales

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Reportes | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->
<!-- Estilos -->

    <style>
.number {
            font-size: 32px;
            font-weight: 800;
            color: #D63384;
            margin: 0;
        }
    </style>
</head>

<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Reportes</h1>
            <p>Consulta información clave para la toma de decisiones y seguimiento del sistema.</p>
        </section>

        <section class="stats">
            <div class="card">
                <p class="number"><?= e($estadisticas['clientesActivos'] ?? count($reporteClientes)) ?></p>
                <p>Clientes activos</p>
            </div>

            <div class="card">
                <p class="number"><?= e($estadisticas['pagosAprobados'] ?? count($reportePagos)) ?></p>
                <p>Pagos aprobados</p>
            </div>

            <div class="card">
                <p class="number"><?= e($estadisticas['planesVendidos'] ?? count($reportePlanes)) ?></p>
                <p>Planes vendidos</p>
            </div>

            <div class="card">
                <p class="number"><?= e($estadisticas['clientesVirtuales'] ?? 0) ?></p>
                <p>Clientes virtuales</p>
            </div>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Reporte de clientes</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Estado / Tipo</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($reporteClientes)): ?>
                            <tr><td colspan="2">No hay datos disponibles.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($reporteClientes as $item): ?>
                            <tr>
                                <td><?= e($item['estado'] ?? $item['tipo_cliente'] ?? 'General') ?></td>
                                <td><span class="badge"><?= e($item['total'] ?? 0) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Reporte de pagos</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($reportePagos)): ?>
                            <tr><td colspan="2">No hay datos disponibles.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($reportePagos as $item): ?>
                            <tr>
                                <td><?= e($item['estado'] ?? 'General') ?></td>
                                <td><span class="badge"><?= e($item['total'] ?? 0) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Planes y programas</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Plan / modalidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($reportePlanes)): ?>
                            <tr><td colspan="2">No hay datos disponibles.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($reportePlanes as $item): ?>
                            <tr>
                                <td><?= e($item['modalidad'] ?? $item['nombre'] ?? 'General') ?></td>
                                <td><span class="badge"><?= e($item['total'] ?? 0) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Progreso de clientes</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Indicador</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($reporteProgreso)): ?>
                            <tr><td colspan="2">No hay datos disponibles.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($reporteProgreso as $item): ?>
                            <tr>
                                <td><?= e($item['indicador'] ?? 'Registros') ?></td>
                                <td><span class="badge"><?= e($item['total'] ?? 0) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>

    </main>
</div>
</body>
</html>