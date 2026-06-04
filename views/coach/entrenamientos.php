<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Clientes asignados
$planes = $planes ?? []; // Planes de entrenamiento
$rutinas = $rutinas ?? []; // Rutinas creadas
$ejercicios = $ejercicios ?? []; // Ejercicios
$materiales = $materiales ?? []; // Materiales
$rutina = $rutina ?? null; // Rutina seleccionada
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Entrenamientos Coach | StayFit</title> <!-- Título -->

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
            margin-bottom: 22px;
        }

        .card h3 {
            color: #D63384;
            margin-top: 0;
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

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 12px;
            font-weight: 700;
            margin-top: 8px;
        }

        .btn-green {
            background: #3EB489;
        }

        .box {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
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
        <a href="../../controllers/coach/dashboardController.php">Dashboard</a>
        <a href="../../controllers/coach/clientesController.php">Clientes</a>
        <a href="../../controllers/coach/agendaController.php">Agenda</a>
        <a class="active" href="../../controllers/coach/entrenamientoController.php">Entrenamientos</a>
        <a href="../../controllers/coach/nutricionController.php">Nutrición</a>
        <a href="../../controllers/coach/progresoController.php">Progreso</a>
        <a href="../../controllers/coach/comunicacionController.php">Comunicación</a>
        <a href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Entrenamientos</h1>
            <p>Crea planes, rutinas, ejercicios y material de apoyo para tus clientas.</p>
        </section>

        <section class="grid">

            <div>
                <div class="card">
                    <h3>Crear plan</h3>

                    <form action="../../controllers/coach/entrenamientoController.php?accion=crearPlan" method="POST">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($cliente['nombre'] ?? 'Cliente') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre del plan</label>
                        <input type="text" name="nombre" required>

                        <label>Objetivo</label>
                        <textarea name="objetivo" required></textarea>

                        <label>Nivel</label>
                        <select name="nivel" required>
                            <option value="basico">Básico</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>

                        <label>Duración</label>
                        <input type="text" name="duracion" placeholder="Ej: 8 semanas" required>

                        <button type="submit">Crear plan</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Crear rutina</h3>

                    <form action="../../controllers/coach/rutinaController.php?accion=guardar" method="POST">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($cliente['nombre'] ?? 'Cliente') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre</label>
                        <input type="text" name="nombre" required>

                        <label>Descripción</label>
                        <textarea name="descripcion" required></textarea>

                        <label>Nivel</label>
                        <select name="nivel" required>
                            <option value="basico">Básico</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>

                        <button type="submit">Crear rutina</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Crear ejercicio</h3>

                    <form action="../../controllers/coach/ejercicioController.php?accion=guardar" method="POST">
                        <label>Rutina</label>
                        <select name="rutina_id" required>
                            <option value="">Seleccione rutina</option>
                            <?php foreach ($rutinas as $item): ?>
                                <option value="<?= e($item['id'] ?? '') ?>"><?= e($item['nombre'] ?? 'Rutina') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre</label>
                        <input type="text" name="nombre" required>

                        <label>Descripción</label>
                        <textarea name="descripcion" required></textarea>

                        <label>Series</label>
                        <input type="number" name="series" required>

                        <label>Repeticiones</label>
                        <input type="text" name="repeticiones" required>

                        <label>Descanso</label>
                        <input type="text" name="descanso" placeholder="Ej: 45 segundos" required>

                        <button type="submit">Guardar ejercicio</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Rutinas registradas</h3>

                    <?php if (empty($rutinas)): ?>
                        <div class="empty">No tienes rutinas registradas.</div>
                    <?php endif; ?>

                    <?php foreach ($rutinas as $item): ?>
                        <div class="box">
                            <strong><?= e($item['nombre'] ?? 'Rutina') ?></strong>
                            <p><?= e($item['descripcion'] ?? '') ?></p>
                            <span class="badge"><?= e($item['estado'] ?? 'activa') ?></span>
                            <br>
                            <a class="btn" href="../../controllers/coach/rutinaController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">Ver detalle</a>
                            <a class="btn btn-green" href="../../controllers/coach/rutinaController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=finalizada">Finalizar</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h3>Ejercicios</h3>

                    <?php if (empty($ejercicios)): ?>
                        <div class="empty">No hay ejercicios registrados.</div>
                    <?php endif; ?>

                    <?php foreach ($ejercicios as $ejercicio): ?>
                        <div class="box">
                            <strong><?= e($ejercicio['nombre'] ?? 'Ejercicio') ?></strong>
                            <p><?= e($ejercicio['descripcion'] ?? '') ?></p>
                            <p><strong>Series:</strong> <?= e($ejercicio['series'] ?? '') ?></p>
                            <p><strong>Repeticiones:</strong> <?= e($ejercicio['repeticiones'] ?? '') ?></p>
                            <span class="badge"><?= e($ejercicio['estado'] ?? 'activo') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($rutina): ?>
                    <div class="card">
                        <h3>Detalle de rutina</h3>
                        <p><strong><?= e($rutina['nombre'] ?? '') ?></strong></p>
                        <p><?= e($rutina['descripcion'] ?? '') ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>