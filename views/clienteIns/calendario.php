<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$agenda = $agenda ?? []; // Agenda individual
$sesiones = $sesiones ?? []; // Sesiones individuales
$sesionesGrupales = $sesionesGrupales ?? []; // Sesiones grupales institucionales

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Calendario Institucional | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.item strong {
            display: block;
            margin-bottom: 6px;
        }

        

        .empty {
            background: #f4f4f4;
            color: #777;
            padding: 18px;
            border-radius: 16px;
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Calendario institucional</h1>
            <p>Consulta tus actividades individuales, sesiones con coach y encuentros grupales de tu institución.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Agenda personal</h3>

                <?php if (empty($agenda)): ?>
                    <div class="empty">No tienes actividades personales registradas.</div>
                <?php endif; ?>

                <?php foreach ($agenda as $item): ?>
                    <div class="item">
                        <strong><?= e($item['titulo'] ?? 'Actividad') ?></strong>
                        <p><?= e($item['descripcion'] ?? 'Sin descripción') ?></p>
                        <span><?= e($item['fecha'] ?? '') ?> - <?= e($item['hora'] ?? '') ?></span>
                        <br>
                        <span class="badge"><?= e($item['modalidad'] ?? 'modalidad') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Sesiones individuales</h3>

                <?php if (empty($sesiones)): ?>
                    <div class="empty">No tienes sesiones individuales programadas.</div>
                <?php endif; ?>

                <?php foreach ($sesiones as $sesion): ?>
                    <div class="item">
                        <strong><?= e($sesion['titulo'] ?? 'Sesión StayFit') ?></strong>
                        <p><?= e($sesion['descripcion'] ?? 'Sesión asignada por tu coach.') ?></p>
                        <span><?= e($sesion['fecha'] ?? '') ?> - <?= e($sesion['hora'] ?? '') ?></span>
                        <br>
                        <span class="badge"><?= e($sesion['estado'] ?? 'programada') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Sesiones grupales</h3>

                <?php if (empty($sesionesGrupales)): ?>
                    <div class="empty">No tienes sesiones grupales asignadas.</div>
                <?php endif; ?>

                <?php foreach ($sesionesGrupales as $sesion): ?>
                    <div class="item">
                        <strong><?= e($sesion['titulo'] ?? 'Sesión grupal') ?></strong>
                        <p><?= e($sesion['descripcion'] ?? 'Actividad institucional StayFit.') ?></p>
                        <span><?= e($sesion['fecha'] ?? '') ?> - <?= e($sesion['hora'] ?? '') ?></span>
                        <br>
                        <span class="badge"><?= e($sesion['modalidad'] ?? 'grupal') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>