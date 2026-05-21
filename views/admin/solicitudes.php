<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$solicitudes = $solicitudes ?? []; // Lista de solicitudes
$solicitud = $solicitud ?? null; // Detalle de solicitud

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
        <a href="../../controller/admin/dashboardController.php">Dashboard</a>
        <a class="active" href="../../controller/admin/solicitudController.php">Solicitudes</a>
        <a href="../../controller/admin/pagoController.php">Pagos</a>
        <a href="../../controller/admin/clienteController.php">Clientes</a>
        <a href="../../controller/admin/asignacionController.php">Asignaciones</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Solicitudes de ingreso</h1>
            <p>Personas interesadas que enviaron sus datos y comprobante. Aún no son clientes activos.</p>
        </section>

        <section class="card">
            <h3>Listado de solicitudes</h3>

            <table>
                <thead>
                    <tr>
                        <th>Solicitante</th>
                        <th>Contacto</th>
                        <th>Modalidad</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($solicitudes)): ?>
                        <tr>
                            <td colspan="5">No hay solicitudes registradas.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($solicitudes as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['nombre'] ?? '') ?></strong><br>
                                <small><?= e($item['identificacion'] ?? '') ?></small>
                            </td>

                            <td><?= e($item['celular'] ?? '') ?></td>
                            <td><?= e($item['modalidad'] ?? 'No definida') ?></td>

                            <td>
                                <span class="badge <?= (($item['estado'] ?? '') === 'aprobada') ? 'ok' : '' ?>">
                                    <?= e($item['estado'] ?? 'pendiente') ?>
                                </span>
                            </td>

                            <td>
                                <a class="btn" href="../../controller/admin/solicitudController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">Ver</a>

                                <?php if (($item['estado'] ?? '') === 'pendiente'): ?>
                                    <a class="btn-green btn" href="../../controller/admin/solicitudController.php?accion=marcarRevision&id=<?= e($item['id'] ?? '') ?>">Revisar</a>
                                <?php endif; ?>

                                <a class="btn-green btn" href="../../controller/admin/validacionPagoController.php?accion=aprobar&solicitud_id=<?= e($item['id'] ?? '') ?>">Aprobar pago</a>
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
                <p><strong>Modalidad:</strong> <?= e($solicitud['modalidad'] ?? '') ?></p>
                <p><strong>Estado:</strong> <?= e($solicitud['estado'] ?? '') ?></p>

                <form action="../../controller/admin/solicitudController.php?accion=rechazar" method="POST">
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