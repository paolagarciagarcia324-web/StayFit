<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planEntrenamiento = $planEntrenamiento ?? null; // Plan asignado
$rutinas = $rutinas ?? []; // Rutinas asignadas
$videos = $videos ?? [];
$avanceVirtual = $avanceVirtual ?? 0;
$programaVirtual = $programaVirtual ?? null;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Entrenamiento Institucional | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.box {
            border-left: 5px solid #D63384;
            background: #fff7fb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .video, .leccion-card {
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
            background: #FFFFFF;
        }

        .leccion-embed { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; margin: 12px 0; }
        .leccion-embed iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
        .leccion-video, .leccion-img { width: 100%; max-height: 360px; border-radius: 12px; margin: 12px 0; }
        .leccion-badge { font-size: 12px; padding: 4px 10px; border-radius: 12px; background: #eee; }
        .leccion-badge--completado { background: #3EB489; color: #fff; }

        

        

        

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
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

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

                        <form action="../../controllers/clienteIns/entrenamientoController.php?accion=marcarRutina" method="POST">
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
                <h3>Programa virtual</h3>

                <?php if ($programaVirtual): ?>
                    <p><strong><?= e($programaVirtual['nombre'] ?? '') ?></strong></p>
                    <?php if (!empty($programaVirtual['descripcion'])): ?>
                        <p><?= nl2br(e($programaVirtual['descripcion'])) ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <p>Avance: <strong><?= e($avanceVirtual) ?>%</strong></p>

                <?php if (empty($videos)): ?>
                    <div class="empty">No hay material virtual asignado.</div>
                <?php endif; ?>

                <?php foreach ($videos as $video): ?>
                    <?php
                    $clienteController = '../../controllers/clienteIns/contenidoVirtualController.php';
                    require __DIR__ . '/../cliente/partials/materialVirtual.php';
                    ?>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>