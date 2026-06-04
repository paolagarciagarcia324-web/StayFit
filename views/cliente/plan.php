<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$plan = $plan ?? null; // Plan activo
$coach = $coach ?? null;
$accesos = $accesos ?? []; // Accesos del cliente

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Mi plan | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.plan-title {
            font-size: 32px;
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

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Mi plan</h1>
            <p>Consulta tu plan activo, modalidad, beneficios y módulos habilitados.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Plan activo</h3>

                <?php if (!$plan): ?>
                    <div class="empty">No tienes un plan activo actualmente.</div>
                    <a class="btn" href="../../public/planPublico.php">Ver planes disponibles</a>
                <?php else: ?>
                    <p class="plan-title"><?= e($plan['nombre'] ?? 'Plan StayFit') ?></p>

                    <p><?= e($plan['descripcion'] ?? 'Plan diseñado para acompañar tu proceso fitness.') ?></p>

                    <span class="badge"><?= e($plan['modalidad'] ?? 'modalidad') ?></span>
                    <span class="badge badge-pink"><?= e($plan['estado'] ?? 'activo') ?></span>

                    <p><strong>Precio:</strong> $<?= e($plan['precio'] ?? '0') ?></p>
                    <p><strong>Duración:</strong> <?= e($plan['duracion'] ?? '0') ?> días</p>
                    <p><strong>Inicio:</strong> <?= e($plan['fecha_inicio'] ?? 'No registrada') ?></p>
                    <p><strong>Vencimiento:</strong> <?= e($plan['fecha_fin'] ?? 'No registrada') ?></p>

                    <h3 style="margin-top: 24px;">Coach asignado</h3>
                    <?php if ($coach || !empty($plan['coach_nombre'])): ?>
                        <p><strong><?= e($coach['nombre_completo'] ?? $plan['coach_nombre'] ?? '') ?></strong></p>
                        <p><?= e($coach['especialidad'] ?? $plan['coach_especialidad'] ?? '') ?></p>
                        <p><?= e($coach['correo'] ?? $plan['coach_correo'] ?? '') ?></p>
                    <?php else: ?>
                        <?php
                        $modalidadPlan = strtoupper($plan['modalidad'] ?? '');
                        $requiereCoach = !empty($plan['requiere_coach']) || in_array($modalidadPlan, ['PRESENCIAL', 'MIXTA', 'MIXTO'], true);
                        ?>
                        <p><?= $requiereCoach ? 'Pendiente de asignación por el administrador.' : 'No aplica para tu modalidad.' ?></p>
                    <?php endif; ?>
                <?php endif; ?>
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

                <a class="btn" href="../../controllers/cliente/pagoController.php">Renovar o enviar pago</a>
            </div>

        </section>

    </main>
</div>
</body>
</html>
