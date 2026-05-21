<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$sesionesGrupales = $sesionesGrupales ?? []; // Sesiones grupales asignadas

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Sesiones Grupales | StayFit</title> <!-- Título -->

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

        .session-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
        }

        .session-card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
            border-top: 6px solid #D63384;
        }

        .session-card h3 {
            margin-top: 0;
            color: #D63384;
        }

        .session-info {
            background: #fff7fb;
            border-radius: 16px;
            padding: 14px;
            margin: 12px 0;
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

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 11px 16px;
            border-radius: 14px;
            font-weight: 800;
            margin-top: 12px;
        }

        .btn-green {
            background: #3EB489;
        }

        .empty {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 28px;
            color: #777;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        @media (max-width: 900px) {
            .cliente-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
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
        <a class="active" href="../../controller/clienteIns/sesionGrupalController.php">Sesiones grupales</a>
        <a href="../../controller/clienteIns/calendarioController.php">Calendario</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Sesiones grupales</h1>
            <p>Consulta talleres, clases o actividades grupales asociadas a tu institución.</p>
        </section>

        <section class="session-grid">

            <?php if (empty($sesionesGrupales)): ?>
                <div class="empty">No tienes sesiones grupales programadas actualmente.</div>
            <?php endif; ?>

            <?php foreach ($sesionesGrupales as $sesion): ?>
                <article class="session-card">
                    <h3><?= e($sesion['titulo'] ?? 'Sesión grupal StayFit') ?></h3>

                    <p><?= e($sesion['descripcion'] ?? 'Actividad grupal institucional.') ?></p>

                    <div class="session-info">
                        <strong>Fecha:</strong>
                        <?= e($sesion['fecha'] ?? 'No registrada') ?>
                    </div>

                    <div class="session-info">
                        <strong>Hora:</strong>
                        <?= e($sesion['hora'] ?? 'No registrada') ?>
                    </div>

                    <div class="session-info">
                        <strong>Modalidad:</strong>
                        <?= e($sesion['modalidad'] ?? 'No definida') ?>
                    </div>

                    <span class="badge"><?= e($sesion['estado'] ?? 'programada') ?></span>

                    <br>

                    <a class="btn btn-green" href="../../controller/clienteIns/sesionGrupalController.php?accion=confirmarAsistencia&id=<?= e($sesion['id'] ?? '') ?>">
                        Confirmar asistencia
                    </a>
                </article>
            <?php endforeach; ?>

        </section>

    </main>
</div>
</body>
</html>