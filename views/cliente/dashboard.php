<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$cliente = $cliente ?? []; // Datos cliente
$plan = $plan ?? []; // Plan activo
$coach = $coach ?? null; // Coach asignado
$accesos = $accesos ?? []; // Accesos
$progreso = $progreso ?? []; // Último progreso
$avanceVirtual = $avanceVirtual ?? 0; // Avance virtual
$notificaciones = $notificaciones ?? []; // Notificaciones

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Dashboard Cliente | StayFit</title> <!-- Título -->

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

        .hero {
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            border-radius: 26px;
            padding: 34px;
            margin-bottom: 28px;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: 34px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
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

        .number {
            font-size: 32px;
            font-weight: 800;
            color: #D63384;
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 22px;
        }

        .progress-box {
            background: #f3f3f3;
            border-radius: 20px;
            height: 14px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress-bar {
            background: #3EB489;
            height: 100%;
            width: <?= e($avanceVirtual) ?>%;
        }

        .item {
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .item:last-child {
            border-bottom: none;
        }

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 14px;
            font-weight: 700;
            margin-top: 12px;
        }

        .btn-green {
            background: #3EB489;
        }

        .badge {
            background: #3EB489;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
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
        <a class="active" href="../../controller/cliente/dashboardController.php">Dashboard</a>
        <a href="../../controller/cliente/perfilController.php">Perfil</a>
        <a href="../../controller/cliente/planController.php">Mi plan</a>
        <a href="../../controller/cliente/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/cliente/nutricionController.php">Nutrición</a>
        <a href="../../controller/cliente/progresoController.php">Progreso</a>
        <a href="../../controller/cliente/calendarioController.php">Calendario</a>
        <a href="../../controller/cliente/comunicacionController.php">Comunicación</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="hero">
            <h1>Hola, <?= e($cliente['nombre'] ?? $_SESSION['nombre'] ?? 'cliente') ?></h1>
            <p>Este es tu espacio para seguir tu entrenamiento, nutrición, progreso y acompañamiento.</p>
        </section>

        <section class="stats">
            <div class="card">
                <h3>Plan activo</h3>
                <p class="number"><?= e($plan['nombre'] ?? 'Sin plan') ?></p>
                <p><?= e($plan['modalidad'] ?? 'Modalidad no definida') ?></p>
                <span class="badge"><?= e($plan['estado'] ?? 'pendiente') ?></span>
            </div>

            <div class="card">
                <h3>Progreso reciente</h3>
                <p class="number"><?= e($progreso['peso'] ?? '0') ?> kg</p>
                <p><?= e($progreso['fecha'] ?? 'Sin registro reciente') ?></p>
                <a class="btn" href="../../controller/cliente/progresoController.php">Registrar progreso</a>
            </div>

            <div class="card">
                <h3>Avance virtual</h3>
                <p class="number"><?= e($avanceVirtual) ?>%</p>
                <div class="progress-box">
                    <div class="progress-bar"></div>
                </div>
                <a class="btn btn-green" href="../../controller/cliente/contenidoVirtualController.php">Ver videos</a>
            </div>

            <div class="card">
                <h3>Tu coach</h3>
                <?php if ($coach): ?>
                    <p class="number" style="font-size: 22px;"><?= e($coach['nombre_completo'] ?? trim(($coach['nombre'] ?? '') . ' ' . ($coach['apellido'] ?? ''))) ?></p>
                    <p><?= e($coach['especialidad'] ?? 'Especialidad no registrada') ?></p>
                    <p><?= e($coach['correo'] ?? '') ?></p>
                    <a class="btn" href="../../controller/cliente/comunicacionController.php">Contactar coach</a>
                <?php else: ?>
                    <?php
                    $modalidadPlan = strtoupper($plan['modalidad'] ?? '');
                    $requiereCoach = !empty($plan['requiere_coach']) || in_array($modalidadPlan, ['PRESENCIAL', 'MIXTA'], true);
                    ?>
                    <?php if ($requiereCoach): ?>
                        <p>Pendiente de asignación por el administrador.</p>
                    <?php else: ?>
                        <p>Tu plan no incluye coach asignado.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Accesos habilitados</h3>

                <?php if (empty($accesos)): ?>
                    <p>No tienes accesos activos todavía.</p>
                <?php endif; ?>

                <?php foreach ($accesos as $acceso): ?>
                    <div class="item">
                        <strong><?= e($acceso['modulo'] ?? 'Módulo') ?></strong>
                        <p>Estado: <span class="badge"><?= e($acceso['estado'] ?? 'activo') ?></span></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Notificaciones</h3>

                <?php if (empty($notificaciones)): ?>
                    <p>No tienes notificaciones pendientes.</p>
                <?php endif; ?>

                <?php foreach ($notificaciones as $notificacion): ?>
                    <div class="item">
                        <strong><?= e($notificacion['titulo'] ?? 'Notificación') ?></strong>
                        <p><?= e($notificacion['mensaje'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>