<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Clientes asignados
$sesiones = $sesiones ?? []; // Sesiones próximas
$rutinasPendientes = $rutinasPendientes ?? []; // Rutinas pendientes
$mensajes = $mensajes ?? []; // Mensajes no leídos
$avanceVirtual = $avanceVirtual ?? []; // Avance virtual
$notificaciones = $notificaciones ?? []; // Notificaciones

$totalClientes = count($clientes); // Total clientes
$totalSesiones = count($sesiones); // Total sesiones
$totalRutinas = count($rutinasPendientes); // Total rutinas pendientes
$totalMensajes = count($mensajes); // Total mensajes
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Dashboard Coach | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .coach-wrapper {
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
            color: #D63384;
            margin-top: 0;
        }

        .number {
            font-size: 34px;
            font-weight: 800;
            color: #D63384;
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 22px;
        }

        .item {
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .item:last-child {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 14px;
            font-weight: 700;
            margin-top: 10px;
        }

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }

        @media (max-width: 900px) {
            .coach-wrapper {
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
<div class="coach-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a class="active" href="../../controller/coach/dashboardController.php">Dashboard</a>
        <a href="../../controller/coach/clientesController.php">Clientes</a>
        <a href="../../controller/coach/agendaController.php">Agenda</a>
        <a href="../../controller/coach/entrenamientoController.php">Entrenamientos</a>
        <a href="../../controller/coach/nutricionController.php">Nutrición</a>
        <a href="../../controller/coach/progresoController.php">Progreso</a>
        <a href="../../controller/coach/comunicacionController.php">Comunicación</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="hero">
            <h1>Hola, <?= e($_SESSION['nombre'] ?? 'coach') ?></h1>
            <p>Gestiona tus clientas, sesiones, rutinas, nutrición, progreso y seguimiento virtual.</p>
        </section>

        <section class="stats">
            <div class="card">
                <h3>Clientes asignadas</h3>
                <p class="number"><?= e($totalClientes) ?></p>
                <a class="btn" href="../../controller/coach/clientesController.php">Ver clientes</a>
            </div>

            <div class="card">
                <h3>Sesiones próximas</h3>
                <p class="number"><?= e($totalSesiones) ?></p>
                <a class="btn" href="../../controller/coach/agendaController.php">Ver agenda</a>
            </div>

            <div class="card">
                <h3>Rutinas pendientes</h3>
                <p class="number"><?= e($totalRutinas) ?></p>
                <a class="btn" href="../../controller/coach/entrenamientoController.php">Gestionar</a>
            </div>

            <div class="card">
                <h3>Mensajes nuevos</h3>
                <p class="number"><?= e($totalMensajes) ?></p>
                <a class="btn" href="../../controller/coach/comunicacionController.php">Responder</a>
            </div>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Próximas sesiones</h3>

                <?php if (empty($sesiones)): ?>
                    <div class="empty">No tienes sesiones próximas registradas.</div>
                <?php endif; ?>

                <?php foreach ($sesiones as $sesion): ?>
                    <div class="item">
                        <strong><?= e($sesion['titulo'] ?? 'Sesión StayFit') ?></strong>
                        <p><?= e($sesion['fecha'] ?? '') ?> - <?= e($sesion['hora'] ?? '') ?></p>
                        <span class="badge"><?= e($sesion['modalidad'] ?? 'modalidad') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Notificaciones</h3>

                <?php if (empty($notificaciones)): ?>
                    <div class="empty">No tienes notificaciones pendientes.</div>
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