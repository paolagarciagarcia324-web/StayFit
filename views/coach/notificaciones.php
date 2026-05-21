<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$notificaciones = $notificaciones ?? []; // Lista de notificaciones
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Notificaciones Coach | StayFit</title> <!-- Título -->

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

        .page-header {
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .notification-list {
            display: grid;
            gap: 18px;
        }

        .notification-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
            border-left: 6px solid #D63384;
        }

        .notification-card.leida {
            border-left-color: #3EB489;
            opacity: 0.85;
        }

        .notification-card h3 {
            margin: 0 0 8px;
            color: #D63384;
        }

        .btn {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 12px;
            font-weight: 700;
            margin-top: 12px;
        }

        .empty {
            background: #FFFFFF;
            padding: 28px;
            border-radius: 20px;
            color: #777;
        }

        @media (max-width: 900px) {
            .coach-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }
        }
    </style>
</head>

<body>
<div class="coach-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/coach/dashboardController.php">Dashboard</a>
        <a href="../../controller/coach/clientesController.php">Clientes</a>
        <a href="../../controller/coach/agendaController.php">Agenda</a>
        <a href="../../controller/coach/entrenamientoController.php">Entrenamientos</a>
        <a href="../../controller/coach/nutricionController.php">Nutrición</a>
        <a href="../../controller/coach/progresoController.php">Progreso</a>
        <a class="active" href="../../controller/coach/notificacionController.php">Notificaciones</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Notificaciones</h1>
            <p>Alertas sobre clientas, sesiones, mensajes, rutinas y seguimiento pendiente.</p>
        </section>

        <section class="notification-list">

            <?php if (empty($notificaciones)): ?>
                <div class="empty">No tienes notificaciones pendientes.</div>
            <?php endif; ?>

            <?php foreach ($notificaciones as $item): ?>
                <article class="notification-card <?= (($item['estado'] ?? '') === 'leida') ? 'leida' : '' ?>">
                    <h3><?= e($item['titulo'] ?? 'Notificación StayFit') ?></h3>
                    <p><?= e($item['mensaje'] ?? '') ?></p>
                    <small><?= e($item['fecha'] ?? '') ?></small>

                    <?php if (($item['estado'] ?? '') !== 'leida'): ?>
                        <br>
                        <a class="btn" href="../../controller/coach/notificacionController.php?accion=marcarLeida&id=<?= e($item['id'] ?? '') ?>">
                            Marcar como leída
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>

        </section>

    </main>
</div>
</body>
</html>