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
    <title>Mi plan | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .cliente-wrapper {
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
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 22px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #D63384;
        }

        .plan-title {
            font-size: 32px;
            font-weight: 800;
            color: #D63384;
            margin: 0 0 10px;
        }

        .badge {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            margin: 6px 6px 6px 0;
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

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 14px;
            font-weight: 800;
            margin-top: 16px;
        }

        @media (max-width: 900px) {
            .cliente-wrapper {
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
<div class="cliente-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/cliente/dashboardController.php">Dashboard</a>
        <a href="../../controller/cliente/perfilController.php">Perfil</a>
        <a class="active" href="../../controller/cliente/planController.php">Mi plan</a>
        <a href="../../controller/cliente/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/cliente/nutricionController.php">Nutrición</a>
        <a href="../../controller/cliente/progresoController.php">Progreso</a>
        <a href="../../controller/cliente/calendarioController.php">Calendario</a>
        <a href="../../controller/cliente/pagoController.php">Pagos</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

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
                        $requiereCoach = !empty($plan['requiere_coach']) || in_array($modalidadPlan, ['PRESENCIAL', 'MIXTA'], true);
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

                <a class="btn" href="../../controller/cliente/pagoController.php">Renovar o enviar pago</a>
            </div>

        </section>

    </main>
</div>
</body>
</html>