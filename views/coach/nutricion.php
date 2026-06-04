<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Clientes asignados
$planesNutricionales = $planesNutricionales ?? []; // Planes nutricionales
$comidas = $comidas ?? []; // Comidas registradas
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Nutrición Coach | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
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

        .box {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .meal {
            background: #f6fffb;
            border-left: 5px solid #3EB489;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
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
<div class="coach-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCoach.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Nutrición</h1>
            <p>Crea planes nutricionales y comidas personalizadas para tus clientas.</p>
        </section>

        <section class="grid">

            <div>
                <div class="card">
                    <h3>Crear plan nutricional</h3>

                    <form action="../../controllers/coach/nutricionController.php?accion=crearPlan" method="POST">
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

                        <label>Objetivo nutricional</label>
                        <textarea name="objetivo" required></textarea>

                        <button type="submit">Crear plan</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Agregar comida</h3>

                    <form action="../../controllers/coach/comidaController.php?accion=guardar" method="POST">
                        <label>Plan nutricional</label>
                        <select name="plan_nutricional_id" required>
                            <option value="">Seleccione plan</option>
                            <?php foreach ($planesNutricionales as $plan): ?>
                                <option value="<?= e($plan['id'] ?? '') ?>"><?= e($plan['nombre'] ?? 'Plan') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre comida</label>
                        <input type="text" name="nombre" required>

                        <label>Descripción</label>
                        <textarea name="descripcion" required></textarea>

                        <label>Hora sugerida</label>
                        <input type="time" name="hora" required>

                        <label>Calorías</label>
                        <input type="number" name="calorias">

                        <button type="submit">Guardar comida</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Planes nutricionales</h3>

                    <?php if (empty($planesNutricionales)): ?>
                        <div class="empty">No tienes planes nutricionales creados.</div>
                    <?php endif; ?>

                    <?php foreach ($planesNutricionales as $plan): ?>
                        <div class="box">
                            <strong><?= e($plan['nombre'] ?? 'Plan nutricional') ?></strong>
                            <p><?= e($plan['descripcion'] ?? '') ?></p>
                            <p><strong>Objetivo:</strong> <?= e($plan['objetivo'] ?? '') ?></p>
                            <span class="badge"><?= e($plan['estado'] ?? 'activo') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h3>Comidas registradas</h3>

                    <?php if (empty($comidas)): ?>
                        <div class="empty">No hay comidas registradas.</div>
                    <?php endif; ?>

                    <?php foreach ($comidas as $comida): ?>
                        <div class="meal">
                            <strong><?= e($comida['nombre'] ?? 'Comida') ?></strong>
                            <p><?= e($comida['descripcion'] ?? '') ?></p>
                            <p><strong>Hora:</strong> <?= e($comida['hora'] ?? 'No definida') ?></p>
                            <p><strong>Calorías:</strong> <?= e($comida['calorias'] ?? 'No registradas') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </section>

    </main>
</div>
</body>
</html>