<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$clientes = $clientes ?? [];
$sesiones = $sesiones ?? [];
$rutinasPendientes = $rutinasPendientes ?? [];
$mensajes = $mensajes ?? [];
$notificaciones = $notificaciones ?? [];

$totalClientes = count($clientes);
$totalSesiones = count($sesiones);
$totalRutinas = count($rutinasPendientes);
$totalMensajes = count($mensajes);

$vistaActiva = 'dashboard';
$nombreCoach = $_SESSION['nombre'] ?? 'coach';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Coach | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1">
</head>
<body class="fp-panel">

<div class="fp-layout coach-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCoach.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Coach</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreCoach) ?></p>
            </div>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero">
                <span class="fp-hero-tag">Panel profesional</span>
                <h1>Hola, <span><?= e($nombreCoach) ?></span></h1>
                <p>Gestiona tus clientes, sesiones, rutinas, nutrición, progreso y seguimiento virtual.</p>
            </section>

            <section class="fp-stats stats">
                <div class="fp-card card">
                    <h3>Clientes asignados</h3>
                    <p class="fp-number number"><?= e($totalClientes) ?></p>
                    <a class="fp-btn btn" href="../../controllers/coach/clientesController.php">Ver clientes</a>
                </div>

                <div class="fp-card card">
                    <h3>Sesiones próximas</h3>
                    <p class="fp-number number"><?= e($totalSesiones) ?></p>
                    <a class="fp-btn btn" href="../../controllers/coach/agendaController.php">Ver agenda</a>
                </div>

                <div class="fp-card card">
                    <h3>Rutinas pendientes</h3>
                    <p class="fp-number number"><?= e($totalRutinas) ?></p>
                    <a class="fp-btn btn" href="../../controllers/coach/entrenamientoController.php">Gestionar</a>
                </div>

                <div class="fp-card card">
                    <h3>Mensajes nuevos</h3>
                    <p class="fp-number number"><?= e($totalMensajes) ?></p>
                    <a class="fp-btn btn" href="../../controllers/coach/comunicacionController.php">Responder</a>
                </div>
            </section>

            <section class="fp-card card" style="margin-bottom: 28px;">
                <h3>Clientes asignados</h3>
                <?php if (empty($clientes)): ?>
                    <p>Aún no tienes clientes asignados por el administrador.</p>
                <?php endif; ?>
                <?php foreach ($clientes as $cliente): ?>
                    <?php $nombreCliente = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')); ?>
                    <div class="fp-timeline-item item">
                        <strong><?= e($nombreCliente !== '' ? $nombreCliente : 'Cliente') ?></strong>
                        <p><?= e($cliente['objetivos'] ?? 'Sin objetivos registrados') ?></p>
                        <span class="fp-badge badge"><?= e($cliente['tipo_cliente'] ?? 'INDIVIDUAL') ?></span>
                        <a class="fp-btn btn" href="../../controllers/coach/clientesController.php?accion=detalle&id=<?= e($cliente['id'] ?? '') ?>">Ver detalle</a>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="fp-grid grid">
                <div class="fp-card card">
                    <h3>Próximas sesiones</h3>
                    <?php if (empty($sesiones)): ?>
                        <p>No tienes sesiones próximas registradas.</p>
                    <?php endif; ?>
                    <?php foreach ($sesiones as $sesion): ?>
                        <div class="fp-timeline-item item">
                            <strong><?= e($sesion['titulo'] ?? 'Sesión FigueFit') ?></strong>
                            <p><?= e($sesion['fecha'] ?? '') ?> — <?= e($sesion['hora'] ?? '') ?></p>
                            <span class="fp-badge badge"><?= e($sesion['modalidad'] ?? 'modalidad') ?></span>
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
