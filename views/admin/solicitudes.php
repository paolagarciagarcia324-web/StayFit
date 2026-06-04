<?php

require_once __DIR__ . '/../../config/helpers.php';

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$solicitudes = $solicitudes ?? [];
$solicitud = $solicitud ?? null;
$flash = $flash ?? null;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Solicitudes | StayFit</title> <!-- Título -->
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

        .alert-success {
            background: #e8f8f1;
            color: #1d6b4f;
            border: 1px solid #3EB489;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
        }

        .alert-error {
            background: #fde8f0;
            color: #8b2252;
            border: 1px solid #D63384;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 14px;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
        }

        .badge {
            background: #D63384;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .badge.ok {
            background: #3EB489;
        }

        .btn {
            background: #D63384;
            color: #FFFFFF;
            padding: 9px 13px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-green {
            background: #3EB489;
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

        .btn-mini {
            display: inline-block;
            padding: 6px 12px;
            font-size: 12px;
        }

        textarea {
            width: 100%;
            min-height: 80px;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 12px;
            font-family: inherit;
            margin-bottom: 12px;
        }

        @media (max-width: 900px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }
        }
    </style>
</head>

<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controllers/admin/dashboardController.php">Dashboard</a>
        <a class="active" href="../../controllers/admin/solicitudController.php">Solicitudes</a>
        <a href="../../controllers/admin/pagoController.php">Pagos</a>
        <a href="../../controllers/admin/clienteController.php">Clientes</a>
        <a href="../../controllers/admin/asignacionController.php">Asignaciones</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Solicitudes de ingreso</h1>
            <p>Personas interesadas que enviaron sus datos y comprobante. Aún no son clientes activos.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h3>Listado de solicitudes</h3>

            <table>
                <thead>
                    <tr>
                        <th>Solicitante</th>
                        <th>Contacto</th>
                        <th>Plan</th>
                        <th>Modalidad</th>
                        <th>Comprobante</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($solicitudes)): ?>
                        <tr>
                            <td colspan="7">No hay solicitudes registradas.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($solicitudes as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['nombre'] ?? '') ?></strong><br>
                                <small><?= e($item['identificacion'] ?? '') ?></small>
                            </td>

                            <td><?= e($item['celular'] ?? '') ?></td>
                            <td><?= e($item['plan_interes'] ?? '—') ?></td>
                            <td><?= e($item['modalidad'] ?? 'No definida') ?></td>

                            <td>
                                <?php if (!empty($item['url_comprobante'])): ?>
                                    <a class="btn btn-mini" target="_blank" rel="noopener"
                                       href="<?= e(urlPublicaComprobante($item['url_comprobante'], (int) ($item['id'] ?? 0))) ?>">
                                        Ver archivo
                                    </a>
                                    <a class="btn btn-mini" href="../../controllers/admin/solicitudController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>#detalle-comprobante">
                                        En detalle
                                    </a>
                                <?php else: ?>
                                    <span class="sin-comprobante">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge <?= (($item['estado'] ?? '') === 'aprobada') ? 'ok' : '' ?>">
                                    <?= e($item['estado'] ?? 'pendiente') ?>
                                </span>
                            </td>

                            <td>
                                <a class="btn" href="../../controllers/admin/solicitudController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">Ver</a>

                                <?php if (($item['estado'] ?? '') === 'pendiente'): ?>
                                    <a class="btn-green btn" href="../../controllers/admin/solicitudController.php?accion=marcarRevision&id=<?= e($item['id'] ?? '') ?>">Revisar</a>
                                <?php endif; ?>

                                <a class="btn-green btn" href="../../controllers/admin/validacionPagoController.php?accion=aprobar&solicitud_id=<?= e($item['id'] ?? '') ?>">Aprobar pago</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <?php if ($solicitud): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle de solicitud</h3>

                <p><strong>Nombre:</strong> <?= e($solicitud['nombre'] ?? '') ?></p>
                <p><strong>Edad:</strong> <?= e($solicitud['edad'] ?? '') ?></p>
                <p><strong>Identificación:</strong> <?= e($solicitud['identificacion'] ?? '') ?></p>
                <p><strong>Celular:</strong> <?= e($solicitud['celular'] ?? '') ?></p>
                <p><strong>Plan:</strong> <?= e($solicitud['plan_interes'] ?? '') ?></p>
                <p><strong>Modalidad:</strong> <?= e($solicitud['modalidad'] ?? '') ?></p>
                <p><strong>Tipo cuenta:</strong> <?= e($solicitud['tipo_cuenta'] ?? '') ?></p>
                <p><strong>Número cuenta:</strong> <?= e($solicitud['numero_cuenta'] ?? '') ?></p>
                <p><strong>Monto:</strong> $<?= e($solicitud['monto_pago'] ?? '0') ?></p>
                <p><strong>Estado:</strong> <?= e($solicitud['estado'] ?? '') ?></p>

                <h4>Comprobante de pago</h4>
                <?php
                $urlComprobante = $solicitud['url_comprobante'] ?? null;
                $solicitudIdComprobante = (int) ($solicitud['id'] ?? 0);
                require __DIR__ . '/partials/comprobanteVista.php';
                ?>

                <form action="../../controllers/admin/solicitudController.php?accion=rechazar" method="POST">
                    <input type="hidden" name="id" value="<?= e($solicitud['id'] ?? '') ?>">
                    <textarea name="observacion" placeholder="Motivo del rechazo"></textarea>
                    <button class="btn" type="submit">Rechazar solicitud</button>
                </form>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>