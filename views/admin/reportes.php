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
    <title>Reportes | StayFit</title> <!-- Título -->
    <link rel="stylesheet" href="../../public/style.css"> <!-- Estilos -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            background: #2D2D2D;
            color: #FFFFFF;
            padding: 28px 20px;
        }

        .sidebar h2 {
            color: #D63384;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #D63384;
        }

        .content {
            flex: 1;
            padding: 34px;
        }

        .page-header {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            border-radius: 22px;
            padding: 30px;
            margin-bottom: 28px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            color: #D63384;
            margin-top: 0;
        }

        .number {
            font-size: 32px;
            font-weight: 800;
            color: #D63384;
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 22px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .badge {
            background: #3EB489;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        @media (max-width: 1000px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controllers/admin/dashboardController.php">Dashboard</a>
        <a href="../../controllers/admin/clienteController.php">Clientes</a>
        <a href="../../controllers/admin/pagoController.php">Pagos</a>
        <a href="../../controllers/admin/planController.php">Planes</a>
        <a class="active" href="../../controllers/admin/reporteController.php">Reportes</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

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