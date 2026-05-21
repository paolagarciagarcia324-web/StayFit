<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$agenda = $agenda ?? []; // Agenda del coach
$sesiones = $sesiones ?? []; // Sesiones asignadas
$disponibilidades = $disponibilidades ?? []; // Horarios disponibles
$clientes = $clientes ?? []; // Clientes del coach

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Agenda Coach | StayFit</title> <!-- Título -->

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

        .grid {
            display: grid;
            grid-template-columns: 380px 1fr;
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

        label {
            font-weight: 700;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        button {
            width: 100%;
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 13px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .session-item {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .session-item strong {
            color: #D63384;
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
        <a href="../../controller/coach/dashboardController.php">Dashboard</a>
        <a href="../../controller/coach/clientesController.php">Clientes</a>
        <a class="active" href="../../controller/coach/agendaController.php">Agenda</a>
        <a href="../../controller/coach/entrenamientoController.php">Entrenamientos</a>
        <a href="../../controller/coach/nutricionController.php">Nutrición</a>
        <a href="../../controller/coach/progresoController.php">Progreso</a>
        <a href="../../controller/coach/comunicacionController.php">Comunicación</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Agenda del coach</h1>
            <p>Programa sesiones, revisa tus horarios y organiza el acompañamiento de tus clientas.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Programar sesión</h3>

                <form action="../../controller/coach/sesionController.php?accion=crear" method="POST">
                    <label>Cliente</label>
                    <select name="cliente_id" required>
                        <option value="">Seleccione cliente</option>

                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= e($cliente['id'] ?? '') ?>">
                                <?= e($cliente['nombre'] ?? 'Cliente') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Título</label>
                    <input type="text" name="titulo" required>

                    <label>Descripción</label>
                    <textarea name="descripcion"></textarea>

                    <label>Fecha</label>
                    <input type="date" name="fecha" required>

                    <label>Hora</label>
                    <input type="time" name="hora" required>

                    <label>Modalidad</label>
                    <select name="modalidad" required>
                        <option value="presencial">Presencial</option>
                        <option value="virtual">Virtual</option>
                        <option value="mixta">Mixta</option>
                    </select>

                    <label>Tipo</label>
                    <select name="tipo" required>
                        <option value="individual">Individual</option>
                        <option value="grupal">Grupal</option>
                    </select>

                    <button type="submit">Guardar sesión</button>
                </form>
            </div>

            <div class="card">
                <h3>Sesiones programadas</h3>

                <?php if (empty($sesiones)): ?>
                    <div class="empty">No tienes sesiones programadas.</div>
                <?php endif; ?>

                <?php foreach ($sesiones as $sesion): ?>
                    <div class="session-item">
                        <strong><?= e($sesion['titulo'] ?? 'Sesión StayFit') ?></strong>
                        <p><?= e($sesion['descripcion'] ?? 'Sin descripción') ?></p>
                        <p><?= e($sesion['fecha'] ?? '') ?> - <?= e($sesion['hora'] ?? '') ?></p>
                        <span class="badge"><?= e($sesion['modalidad'] ?? 'modalidad') ?></span>
                        <span class="badge"><?= e($sesion['estado'] ?? 'programada') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

        <section class="card" style="margin-top: 24px;">
            <h3>Disponibilidad registrada</h3>

            <?php if (empty($disponibilidades)): ?>
                <div class="empty">No tienes disponibilidad registrada todavía.</div>
            <?php endif; ?>

            <?php foreach ($disponibilidades as $item): ?>
                <div class="session-item">
                    <strong><?= e($item['dia'] ?? 'Día') ?></strong>
                    <p><?= e($item['hora_inicio'] ?? '') ?> - <?= e($item['hora_fin'] ?? '') ?></p>
                    <span class="badge"><?= e($item['modalidad'] ?? 'modalidad') ?></span>
                </div>
            <?php endforeach; ?>
        </section>

    </main>
</div>
</body>
</html>