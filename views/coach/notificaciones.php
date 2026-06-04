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
    <title>Notificaciones Coach | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
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

        

        .empty {
            background: #FFFFFF;
            padding: 28px;
            border-radius: 20px;
            color: #777;
        }
    </style>
</head>

<body class="fp-panel">
<div class="coach-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCoach.php'; ?>

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
                        <a class="btn" href="../../controllers/coach/notificacionController.php?accion=marcarLeida&id=<?= e($item['id'] ?? '') ?>">
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