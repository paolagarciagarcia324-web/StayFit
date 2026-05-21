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
    <title>Notificaciones | StayFit</title> <!-- Título -->
    <link rel="stylesheet" href="../../public/style.css"> <!-- Estilos generales -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .admin-wrapper {
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
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            border-radius: 22px;
            padding: 30px;
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

        .notification-card.read {
            border-left-color: #3EB489;
            opacity: 0.85;
        }

        .notification-card h3 {
            margin: 0 0 8px;
            color: #2D2D2D;
        }

        .notification-card p {
            margin: 0 0 14px;
        }

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 12px;
            font-weight: 700;
        }

        .btn-green {
            background: #3EB489;
        }

        @media (max-width: 900px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }
        }
    </style>
</head>

<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/admin/dashboardController.php">Dashboard</a>
        <a href="../../controller/admin/solicitudController.php">Solicitudes</a>
        <a href="../../controller/admin/pagoController.php">Pagos</a>
        <a href="../../controller/admin/clienteController.php">Clientes</a>
        <a class="active" href="../../controller/admin/notificacionController.php">Notificaciones</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Notificaciones</h1>
            <p>Alertas importantes sobre solicitudes, pagos, accesos, vencimientos y actividad del sistema.</p>
        </section>

        <section class="notification-list">

            <?php if (empty($notificaciones)): ?>
                <article class="notification-card read">
                    <h3>Sin notificaciones</h3>
                    <p>No tienes alertas pendientes en este momento.</p>
                </article>
            <?php endif; ?>

            <?php foreach ($notificaciones as $item): ?>
                <article class="notification-card <?= (($item['estado'] ?? '') === 'leida') ? 'read' : '' ?>">
                    <h3><?= e($item['titulo'] ?? 'Notificación') ?></h3>
                    <p><?= e($item['mensaje'] ?? '') ?></p>
                    <small><?= e($item['fecha'] ?? '') ?></small>
                    <br><br>

                    <?php if (($item['estado'] ?? '') !== 'leida'): ?>
                        <a class="btn btn-green" href="../../controller/admin/notificacionController.php?accion=marcarLeida&id=<?= e($item['id'] ?? '') ?>">
                            Marcar como leída
                        </a>
                    <?php endif; ?>

                    <a class="btn" href="../../controller/admin/notificacionController.php?accion=eliminar&id=<?= e($item['id'] ?? '') ?>">
                        Eliminar
                    </a>
                </article>
            <?php endforeach; ?>

        </section>

    </main>
</div>
</body>
</html>