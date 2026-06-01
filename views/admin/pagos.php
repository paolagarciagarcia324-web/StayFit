<?php

require_once __DIR__ . '/../../config/helpers.php';

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$pagos = $pagos ?? []; // Lista de pagos
$pendientes = $pendientes ?? []; // Pagos pendientes
$pago = $pago ?? null; // Detalle de pago
$comprobante = $comprobante ?? null; // Comprobante del pago
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Pagos | StayFit</title> <!-- Título -->
    <link rel="stylesheet" href="../../public/style.css"> <!-- Estilos generales -->

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
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card,
        .card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .stat-card h2 {
            color: #D63384;
            margin: 0;
            font-size: 32px;
        }

        .card h3 {
            color: #D63384;
            margin-top: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px;
            border-bottom: 2px solid #f0f0f0;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            color: #FFFFFF;
            background: #3EB489;
            font-size: 13px;
        }

        .badge.pending {
            background: #D63384;
        }

        .comprobante-preview {
            margin-top: 16px;
            padding: 16px;
            background: #f9f9f9;
            border-radius: 14px;
            border: 1px solid #eee;
        }

        .comprobante-img {
            max-width: 100%;
            max-height: 420px;
            border-radius: 12px;
            border: 1px solid #ddd;
            display: block;
        }

        .comprobante-pdf {
            width: 100%;
            min-height: 420px;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        .sin-comprobante {
            color: #777;
            font-style: italic;
        }

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 9px 13px;
            border-radius: 12px;
            font-weight: 700;
        }

        .btn-green {
            background: #3EB489;
        }

        textarea {
            width: 100%;
            min-height: 70px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin-bottom: 12px;
            font-family: inherit;
        }

        @media (max-width: 900px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/admin/dashboardController.php">Dashboard</a>
        <a href="../../controller/admin/solicitudController.php">Solicitudes</a>
        <a class="active" href="../../controller/admin/pagoController.php">Pagos</a>
        <a href="../../controller/admin/clienteController.php">Clientes</a>
        <a href="../../controller/admin/asignacionController.php">Asignaciones</a>
        <a href="../../controller/admin/planController.php">Planes</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Pagos y validación</h1>
            <p>Revisa comprobantes, aprueba pagos y activa clientes según su modalidad.</p>
        </section>

        <section class="stats">
            <div class="stat-card">
                <h2><?= count($pagos) ?></h2>
                <p>Total pagos registrados</p>
            </div>

            <div class="stat-card">
                <h2><?= count($pendientes) ?></h2>
                <p>Pagos pendientes</p>
            </div>

            <div class="stat-card">
                <h2><?= count(array_filter($pagos, fn($p) => ($p['estado'] ?? '') === 'aprobado')) ?></h2>
                <p>Pagos aprobados</p>
            </div>
        </section>

        <section class="card">
            <h3>Listado de pagos</h3>

            <table>
                <thead>
                    <tr>
                        <th>Cliente / Solicitud</th>
                        <th>Plan</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="6">No hay pagos registrados.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($pagos as $item): ?>
                        <tr>
                            <td><?= e($item['cliente'] ?? $item['solicitante'] ?? 'Sin nombre') ?></td>
                            <td><?= e($item['plan'] ?? 'Sin plan') ?></td>
                            <td>$<?= e($item['monto'] ?? '0') ?></td>
                            <td>
                                <span class="badge <?= (($item['estado'] ?? '') === 'pendiente') ? 'pending' : '' ?>">
                                    <?= e($item['estado'] ?? 'pendiente') ?>
                                </span>
                            </td>
                            <td><?= e($item['fecha'] ?? '') ?></td>
                            <td>
                                <a class="btn" href="../../controller/admin/pagoController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">
                                    Ver
                                </a>

                                <?php if (($item['estado'] ?? '') === 'pendiente'): ?>
                                    <a class="btn btn-green" href="../../controller/admin/pagoController.php?accion=aprobar&id=<?= e($item['id'] ?? '') ?>">
                                        Aprobar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <?php if ($pago): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle del pago</h3>
                <p><strong>Estado:</strong> <?= e($pago['estado'] ?? '') ?></p>
                <p><strong>Monto:</strong> $<?= e($pago['monto'] ?? '0') ?></p>
                <h4>Comprobante de pago</h4>
                <?php
                $urlComprobante = $comprobante['url_comprobante'] ?? $comprobante['ruta_archivo'] ?? $pago['url_comprobante'] ?? null;
                $pagoIdComprobante = (int) ($pago['id'] ?? $pago['id_pago'] ?? 0);
                require __DIR__ . '/partials/comprobanteVista.php';
                ?>

                <form action="../../controller/admin/pagoController.php?accion=rechazar" method="POST">
                    <input type="hidden" name="id" value="<?= e($pago['id'] ?? '') ?>">
                    <textarea name="observacion" placeholder="Motivo del rechazo"></textarea>
                    <button class="btn" type="submit">Rechazar pago</button>
                </form>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>