<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$cliente = $cliente ?? [];
$plan = $plan ?? [];
$coach = $coach ?? null;
$accesos = $accesos ?? [];
$progreso = $progreso ?? [];
$avanceVirtual = $avanceVirtual ?? 0;
$notificaciones = $notificaciones ?? [];

$vistaActiva = 'dashboard';
$nombreCliente = $cliente['nombre'] ?? $_SESSION['nombre'] ?? 'cliente';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1">
    <style>.progress-bar { width: <?= (int) $avanceVirtual ?>%; }</style>
</head>
<body class="fp-panel">

<div class="fp-layout cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente individual</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreCliente) ?></p>
            </div>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero">
                <span class="fp-hero-tag">Tu espacio fitness</span>
                <h1>Bienvenido, <span><?= e($nombreCliente) ?></span></h1>
                <p>Tu espacio para seguir entrenamiento, nutrición, progreso y acompañamiento profesional.</p>
            </section>

            <section class="fp-stats stats">
                <div class="fp-card card">
                    <h3>Plan activo</h3>
                    <p class="fp-number number"><?= e($plan['nombre'] ?? 'Sin plan') ?></p>
                    <p><?= e($plan['modalidad'] ?? 'Modalidad no definida') ?></p>
                    <span class="fp-badge badge"><?= e($plan['estado'] ?? 'pendiente') ?></span>
                </div>

                <div class="fp-card card">
                    <h3>Progreso reciente</h3>
                    <p class="fp-number number"><?= e($progreso['peso'] ?? '0') ?> kg</p>
                    <p><?= e($progreso['fecha'] ?? 'Sin registro reciente') ?></p>
                    <a class="fp-btn btn" href="../../controllers/cliente/progresoController.php">Registrar progreso</a>
                </div>

                <div class="fp-card card">
                    <h3>Avance virtual</h3>
                    <p class="fp-number number"><?= e($avanceVirtual) ?>%</p>
                    <div class="fp-progress-box progress-box">
                        <div class="fp-progress-bar progress-bar"></div>
                    </div>
                    <a class="fp-btn fp-btn-green btn btn-green" href="../../controllers/cliente/contenidoVirtualController.php">Ver videos</a>
                </div>

                <div class="fp-card card">
                    <h3>Tu coach</h3>
                    <?php if ($coach): ?>
                        <p class="fp-number number" style="font-size: 22px;"><?= e($coach['nombre_completo'] ?? trim(($coach['nombre'] ?? '') . ' ' . ($coach['apellido'] ?? ''))) ?></p>
                        <p><?= e($coach['especialidad'] ?? 'Especialidad no registrada') ?></p>
                        <p><?= e($coach['correo'] ?? '') ?></p>
                        <a class="fp-btn btn" href="../../controllers/cliente/comunicacionController.php">Contactar coach</a>
                    <?php else: ?>
                        <?php
                        $modalidadPlan = strtoupper($plan['modalidad'] ?? '');
                        $requiereCoach = !empty($plan['requiere_coach']) || in_array($modalidadPlan, ['PRESENCIAL', 'MIXTA', 'MIXTO'], true);
                        ?>
                        <?php if ($requiereCoach): ?>
                            <p>Pendiente de asignación por el administrador.</p>
                        <?php else: ?>
                            <p>Tu plan no incluye coach asignado.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="fp-grid grid">
                <div class="fp-card card">
                    <h3>Accesos habilitados</h3>
                    <?php if (empty($accesos)): ?>
                        <p>No tienes accesos activos todavía.</p>
                    <?php endif; ?>
                    <?php foreach ($accesos as $acceso): ?>
                        <div class="fp-timeline-item item">
                            <strong><?= e($acceso['modulo'] ?? 'Módulo') ?></strong>
                            <p>Estado: <span class="fp-badge badge"><?= e($acceso['estado'] ?? 'activo') ?></span></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="fp-card card">
                    <h3>Notificaciones</h3>
                    <?php if (empty($notificaciones)): ?>
                        <p>No tienes notificaciones pendientes.</p>
                    <?php endif; ?>
                    <?php foreach ($notificaciones as $notificacion): ?>
                        <div class="fp-timeline-item item">
                            <strong><?= e($notificacion['titulo'] ?? 'Notificación') ?></strong>
                            <p><?= e($notificacion['mensaje'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </main>
    </div>
</div>

</body>
</html>
