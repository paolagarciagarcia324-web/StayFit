<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$plan = $plan ?? null; // Plan activo
$accesos = $accesos ?? []; // Accesos del cliente
$institucion = $institucion ?? []; // Institución vinculada
$pagos = $pagos ?? []; // Pagos si llegan desde controlador

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Plan Institucional | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.plan-title {
            font-size: 30px;
            font-weight: 800;
            color: #D63384;
            margin: 0 0 10px;
        }

        

        .badge-pink {
            background: #D63384;
        }

        .access-item {
            border-left: 5px solid #3EB489;
            background: #f6fffb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .empty {
            background: #f4f4f4;
            color: #777;
            border-radius: 16px;
            padding: 18px;
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Mi plan institucional</h1>
            <p>Consulta el plan activo, beneficios, modalidad, accesos y estado del convenio.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Plan activo</h3>

                <?php if (!$plan): ?>
                    <div class="empty">No tienes un plan institucional activo actualmente.</div>
                <?php else: ?>
                    <p class="plan-title"><?= e($plan['nombre'] ?? 'Plan institucional StayFit') ?></p>
                    <p><?= e($plan['descripcion'] ?? 'Plan asociado al convenio institucional.') ?></p>

                    <span class="badge"><?= e($plan['modalidad'] ?? 'modalidad') ?></span>
                    <span class="badge badge-pink"><?= e($plan['estado'] ?? 'activo') ?></span>

                    <p><strong>Precio:</strong> $<?= e($plan['precio'] ?? '0') ?></p>
                    <p><strong>Duración:</strong> <?= e($plan['duracion'] ?? '0') ?> días</p>
                    <p><strong>Inicio:</strong> <?= e($plan['fecha_inicio'] ?? 'No registrada') ?></p>
                    <p><strong>Vencimiento:</strong> <?= e($plan['fecha_fin'] ?? 'No registrada') ?></p>
                <?php endif; ?>

                <hr>

                <h3>Institución vinculada</h3>
                <p><strong><?= e($institucion['nombre'] ?? 'Sin institución vinculada') ?></strong></p>
                <p><?= e($institucion['correo'] ?? '') ?></p>
                <span class="badge"><?= e($institucion['estado'] ?? 'sin estado') ?></span>
            </div>

            <div class="card">
                <h3>Accesos habilitados</h3>

                <?php if (empty($accesos)): ?>
                    <div class="empty">No tienes accesos habilitados todavía.</div>
                <?php endif; ?>

                <?php foreach ($accesos as $acceso): ?>
                    <div class="access-item">
                        <strong><?= e($acceso['modulo'] ?? 'Módulo') ?></strong>
                        <p>Estado: <span class="badge"><?= e($acceso['estado'] ?? 'activo') ?></span></p>
                    </div>
                <?php endforeach; ?>

                <a class="btn" href="../../controllers/clienteIns/pagoController.php">Ver pagos o renovar</a>
            </div>

        </section>

        <?php if (!empty($pagos)): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Historial de pagos</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?= e($pago['plan'] ?? $pago['plan_id'] ?? 'Plan') ?></td>
                                <td>$<?= e($pago['monto'] ?? '0') ?></td>
                                <td><?= e($pago['estado'] ?? 'pendiente') ?></td>
                                <td><?= e($pago['fecha'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>