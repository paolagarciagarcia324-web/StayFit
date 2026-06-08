<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$agenda = $agenda ?? []; // Agenda del cliente
$sesiones = $sesiones ?? []; // Sesiones asignadas

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Calendario | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.item strong {
            display: block;
            color: #2D2D2D;
            margin-bottom: 6px;
        }

        

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Calendario</h1>
            <p>Consulta tus sesiones, eventos, actividades programadas y recordatorios importantes.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Agenda personal</h3>

                <?php if (empty($agenda)): ?>
                    <div class="empty">No tienes actividades registradas en tu agenda.</div>
                <?php endif; ?>

                <?php foreach ($agenda as $item): ?>
                    <div class="item">
                        <strong><?= e($item['titulo'] ?? 'Actividad') ?></strong>
                        <span><?= e($item['fecha'] ?? '') ?> - <?= e($item['hora'] ?? '') ?></span>
                        <p><?= e($item['descripcion'] ?? 'Sin descripción') ?></p>
                        <span class="badge"><?= e($item['modalidad'] ?? 'modalidad') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Sesiones programadas</h3>

                <?php if (empty($sesiones)): ?>
                    <div class="empty">No tienes sesiones programadas por ahora.</div>
                <?php endif; ?>

                <?php foreach ($sesiones as $sesion): ?>
                    <div class="item">
                        <strong><?= e($sesion['titulo'] ?? 'Sesión StayFit') ?></strong>
                        <span><?= e($sesion['fecha'] ?? '') ?> - <?= e($sesion['hora'] ?? '') ?></span>
                        <p><?= e($sesion['descripcion'] ?? 'Sesión asignada por tu coach.') ?></p>
                        <span class="badge"><?= e($sesion['estado'] ?? 'programada') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>