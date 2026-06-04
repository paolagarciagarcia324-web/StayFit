<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

$clientes = $clientes ?? [];
$cliente = $cliente ?? null;
$plan = $plan ?? null;
$progreso = $progreso ?? [];
$esDetalle = !empty($cliente);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esDetalle ? 'Detalle cliente' : 'Mis clientas' ?> | StayFit Coach</title>

    <style>
th, 

        

        

        .btn-secondary {
            background: #2D2D2D;
        }

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }
    </style>
</head>

<body class="fp-panel">
<div class="coach-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCoach.php'; ?>

    <main class="content">

        <?php if ($esDetalle): ?>
            <?php
            $nombreCliente = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
            ?>
            <section class="page-header">
                <h1><?= e($nombreCliente !== '' ? $nombreCliente : 'Cliente') ?></h1>
                <p>Detalle de la clienta asignada a tu acompañamiento.</p>
            </section>

            <a class="btn btn-secondary" href="../../controllers/coach/clientesController.php">Volver al listado</a>

            <section class="card" style="margin-top: 24px;">
                <h3>Información</h3>
                <p><strong>Correo:</strong> <?= e($cliente['correo'] ?? 'No registrado') ?></p>
                <p><strong>Tipo:</strong> <?= e($cliente['tipo_cliente'] ?? 'INDIVIDUAL') ?></p>
                <p><strong>Objetivos:</strong> <?= e($cliente['objetivos'] ?? 'Sin objetivos') ?></p>
            </section>

            <section class="card">
                <h3>Plan activo</h3>
                <?php if (!$plan): ?>
                    <div class="empty">Sin plan activo registrado.</div>
                <?php else: ?>
                    <p><strong><?= e($plan['nombre'] ?? 'Plan') ?></strong></p>
                    <p>Modalidad: <span class="badge"><?= e($plan['modalidad'] ?? 'N/D') ?></span></p>
                    <p>Estado: <?= e($plan['estado'] ?? 'ACTIVO') ?></p>
                    <p>Vigencia: <?= e($plan['fecha_inicio'] ?? '') ?> — <?= e($plan['fecha_fin'] ?? '') ?></p>
                <?php endif; ?>
            </section>

            <section class="card">
                <h3>Progreso reciente</h3>
                <?php if (empty($progreso)): ?>
                    <div class="empty">Sin registros de progreso.</div>
                <?php else: ?>
                    <?php foreach ($progreso as $registro): ?>
                        <div class="item">
                            <strong><?= e($registro['fecha'] ?? '') ?></strong>
                            <p>Peso: <?= e($registro['peso'] ?? 'N/D') ?> kg</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

        <?php else: ?>

            <section class="page-header">
                <h1>Mis clientas</h1>
                <p>Clientas que el administrador te ha asignado según su plan.</p>
            </section>

            <section class="card">
                <?php if (empty($clientes)): ?>
                    <div class="empty">Aún no tienes clientas asignadas. El administrador debe asignarte clientes desde el panel de Asignaciones.</div>
                <?php else: ?>
                    <table>
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
                                ?>
                                <tr>
                                    <td><?= e($nombre !== '' ? $nombre : 'Cliente') ?></td>
                                    <td><?= e($item['correo'] ?? '') ?></td>
                                    <td><?= e($item['tipo_cliente'] ?? 'INDIVIDUAL') ?></td>
                                    <td><span class="badge"><?= e($item['estado_plan'] ?? 'ACTIVO') ?></span></td>
                                    <td>
                                        <a class="btn" href="../../controllers/coach/clientesController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">Ver</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

        <?php endif; ?>

    </main>
</div>
</body>
</html>
