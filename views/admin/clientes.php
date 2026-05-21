<?php

if (!function_exists('e')) { // Evita repetir la función
    function e($valor) { // Limpia texto para imprimir
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Lista de clientes
$cliente = $cliente ?? null; // Detalle de cliente
$pagos = $pagos ?? []; // Pagos del cliente
$plan = $plan ?? null; // Plan activo
$coach = $coach ?? null; // Coach asignado
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Clientes | StayFit</title> <!-- Título -->
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

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 32px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: #FFFFFF;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .stat-card span {
            color: #D63384;
            font-size: 28px;
            font-weight: 800;
        }

        .grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 22px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #D63384;
        }

        label {
            font-weight: 600;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        button,
        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 10px 15px;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-green {
            background: #3EB489;
        }

        .btn-dark {
            background: #2D2D2D;
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

        .badge.off {
            background: #D63384;
        }

        @media (max-width: 1000px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .grid,
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
        <a href="dashboardController.php">Dashboard</a>
        <a class="active" href="clienteController.php">Clientes</a>
        <a href="coachController.php">Coaches</a>
        <a href="asignacionController.php">Asignaciones</a>
        <a href="planController.php">Planes</a>
        <a href="pagoController.php">Pagos</a>
        <a href="solicitudController.php">Solicitudes</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Clientes</h1>
            <p>Gestiona clientas fijas, clientes aprobados, modalidad, estado y trazabilidad del servicio.</p>
        </section>

        <section class="stats">
            <div class="stat-card">
                <span><?= count($clientes) ?></span>
                <p>Total clientes</p>
            </div>

            <div class="stat-card">
                <span>
                    <?= count(array_filter($clientes, fn($c) => ($c['estado'] ?? '') === 'activo')) ?>
                </span>
                <p>Clientes activos</p>
            </div>

            <div class="stat-card">
                <span>
                    <?= count(array_filter($clientes, fn($c) => ($c['tipo_cliente'] ?? '') === 'institucional')) ?>
                </span>
                <p>Institucionales</p>
            </div>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Registrar clienta fija</h3>

                <form action="../../controller/admin/clienteController.php?accion=guardarClienteFijo" method="POST">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" required>

                    <label>Correo</label>
                    <input type="email" name="correo" required>

                    <label>Identificación</label>
                    <input type="text" name="identificacion" required>

                    <label>Edad</label>
                    <input type="number" name="edad" min="12" required>

                    <label>Celular</label>
                    <input type="text" name="celular" required>

                    <label>Tipo de cliente</label>
                    <select name="tipo_cliente" required>
                        <option value="individual">Individual</option>
                        <option value="institucional">Institucional</option>
                    </select>

                    <button type="submit">Guardar cliente</button>
                </form>
            </div>

            <div class="card">
                <h3>Listado de clientes</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="5">No hay clientes registrados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($clientes as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['nombre'] ?? 'Sin nombre') ?></strong><br>
                                    <small><?= e($item['identificacion'] ?? 'Sin identificación') ?></small>
                                </td>

                                <td>
                                    <?= e($item['correo'] ?? 'Sin correo') ?><br>
                                    <small><?= e($item['celular'] ?? 'Sin celular') ?></small>
                                </td>

                                <td><?= e($item['tipo_cliente'] ?? 'individual') ?></td>

                                <td>
                                    <span class="badge <?= (($item['estado'] ?? '') === 'activo') ? '' : 'off' ?>">
                                        <?= e($item['estado'] ?? 'sin estado') ?>
                                    </span>
                                </td>

                                <td>
                                    <a class="btn btn-dark" href="../../controller/admin/clienteController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">
                                        Ver
                                    </a>

                                    <?php if (($item['estado'] ?? '') === 'activo'): ?>
                                        <a class="btn" href="../../controller/admin/clienteController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=inactivo">
                                            Inactivar
                                        </a>
                                    <?php else: ?>
                                        <a class="btn btn-green" href="../../controller/admin/clienteController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=activo">
                                            Activar
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>

        <?php if ($cliente): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle del cliente</h3>
                <p><strong>Nombre:</strong> <?= e($cliente['nombre'] ?? '') ?></p>
                <p><strong>Plan activo:</strong> <?= e($plan['nombre'] ?? 'Sin plan activo') ?></p>
                <p><strong>Coach asignado:</strong> <?= e($coach['nombre'] ?? 'Sin coach asignado') ?></p>
                <p><strong>Pagos registrados:</strong> <?= count($pagos) ?></p>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>