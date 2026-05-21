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
    <title>Calendario Institucional | StayFit</title> <!-- Título -->

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
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 22px;
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

        .item {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .item strong {
            display: block;
            margin-bottom: 6px;
        }

        .badge {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 8px;
        }

        .empty {
            background: #f4f4f4;
            color: #777;
            padding: 18px;
            border-radius: 16px;
        }

        @media (max-width: 1000px) {
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
        <a href="../../controller/clienteIns/dashboardController.php">Dashboard</a>
        <a href="../../controller/clienteIns/perfilController.php">Perfil</a>
        <a href="../../controller/clienteIns/institucionController.php">Institución</a>
        <a href="../../controller/clienteIns/planController.php">Mi plan</a>
        <a href="../../controller/clienteIns/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/clienteIns/nutricionController.php">Nutrición</a>
        <a href="../../controller/clienteIns/progresoController.php">Progreso</a>
        <a href="../../controller/clienteIns/sesionGrupalController.php">Sesiones grupales</a>
        <a class="active" href="../../controller/clienteIns/calendarioController.php">Calendario</a>
        <a href="../../controller/clienteIns/comunicacionController.php">Comunicación</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

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