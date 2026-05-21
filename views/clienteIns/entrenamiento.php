<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planEntrenamiento = $planEntrenamiento ?? null; // Plan asignado
$rutinas = $rutinas ?? []; // Rutinas asignadas
$videos = $videos ?? []; // Videos institucionales
$avanceVirtual = $avanceVirtual ?? 0; // Avance virtual

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Entrenamiento Institucional | StayFit</title> <!-- Título -->

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
            grid-template-columns: 1.2fr 1fr;
            gap: 22px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #D63384;
        }

        .box {
            border-left: 5px solid #D63384;
            background: #fff7fb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .video {
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
            background: #FFFFFF;
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
            padding: 10px 15px;
            border-radius: 14px;
            font-weight: 700;
            margin-top: 10px;
        }

        .btn-green {
            background: #3EB489;
        }

        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 14px;
            margin: 8px 0 12px;
            font-family: inherit;
            box-sizing: border-box;
        }

        button {
            background: #3EB489;
            color: #FFFFFF;
            border: none;
            padding: 11px 16px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .progress-box {
            background: #eee;
            height: 14px;
            border-radius: 20px;
            overflow: hidden;
            margin: 12px 0;
        }

        .progress-bar {
            width: <?= e($avanceVirtual) ?>%;
            height: 100%;
            background: #3EB489;
        }

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }

        @media (max-width: 900px) {
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
        <a class="active" href="../../controller/clienteIns/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/clienteIns/nutricionController.php">Nutrición</a>
        <a href="../../controller/clienteIns/progresoController.php">Progreso</a>
        <a href="../../controller/clienteIns/sesionGrupalController.php">Sesiones grupales</a>
        <a href="../../controller/clienteIns/calendarioController.php">Calendario</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Entrenamiento institucional</h1>
            <p>Consulta tus rutinas, videos pregrabados y actividades asociadas al convenio institucional.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Plan de entrenamiento</h3>

                <?php if (!$planEntrenamiento): ?>
                    <div class="empty">Aún no tienes un plan de entrenamiento asignado.</div>
                <?php else: ?>
                    <div class="box">
                        <strong><?= e($planEntrenamiento['nombre'] ?? 'Plan StayFit') ?></strong>
                        <p><?= e($planEntrenamiento['objetivo'] ?? 'Objetivo no definido') ?></p>
                        <span class="badge"><?= e($planEntrenamiento['estado'] ?? 'activo') ?></span>
                    </div>
                <?php endif; ?>

                <h3>Rutinas asignadas</h3>

                <?php if (empty($rutinas)): ?>
                    <div class="empty">No tienes rutinas institucionales asignadas.</div>
                <?php endif; ?>

                <?php foreach ($rutinas as $rutina): ?>
                    <div class="box">
                        <strong><?= e($rutina['nombre'] ?? 'Rutina') ?></strong>
                        <p><?= e($rutina['descripcion'] ?? 'Sin descripción') ?></p>
                        <span class="badge"><?= e($rutina['estado'] ?? 'asignada') ?></span>

                        <form action="../../controller/clienteIns/entrenamientoController.php?accion=marcarRutina" method="POST">
                            <input type="hidden" name="rutina_id" value="<?= e($rutina['id'] ?? '') ?>">

                            <select name="estado" required>
                                <option value="en_progreso">En progreso</option>
                                <option value="completada">Completada</option>
                                <option value="omitida">Omitida</option>
                            </select>

                            <textarea name="observacion" placeholder="Observación sobre la rutina"></textarea>

                            <button type="submit">Actualizar rutina</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Contenido virtual</h3>

                <p>Avance del contenido institucional: <strong><?= e($avanceVirtual) ?>%</strong></p>

                <div class="progress-box">
                    <div class="progress-bar"></div>
                </div>

                <?php if (empty($videos)): ?>
                    <div class="empty">No tienes videos institucionales asignados.</div>
                <?php endif; ?>

                <?php foreach ($videos as $video): ?>
                    <div class="video">
                        <strong><?= e($video['titulo'] ?? 'Video StayFit') ?></strong>
                        <p><?= e($video['descripcion'] ?? 'Contenido virtual del programa institucional.') ?></p>

                        <?php if (!empty($video['url_video'])): ?>
                            <a class="btn" href="<?= e($video['url_video']) ?>" target="_blank">Ver video</a>
                        <?php endif; ?>

                        <a class="btn btn-green" href="../../controller/clienteIns/contenidoVirtualController.php?accion=marcarVisto&video_id=<?= e($video['id'] ?? '') ?>">
                            Marcar como visto
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>