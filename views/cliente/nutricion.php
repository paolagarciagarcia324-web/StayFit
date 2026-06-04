<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planNutricional = $planNutricional ?? null; // Plan nutricional asignado
$comidas = $comidas ?? []; // Comidas del plan

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Nutrición | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.meal {
            border-left: 5px solid #3EB489;
            background: #f6fffb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .meal strong {
            display: block;
            margin-bottom: 6px;
            color: #2D2D2D;
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
            <h1>Nutrición</h1>
            <p>Consulta tu plan nutricional, comidas sugeridas y recomendaciones asignadas por tu coach.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Plan nutricional</h3>

                <?php if (!$planNutricional): ?>
                    <div class="empty">Aún no tienes un plan nutricional asignado.</div>
                <?php else: ?>
                    <p><strong><?= e($planNutricional['nombre'] ?? 'Plan nutricional StayFit') ?></strong></p>
                    <p><?= e($planNutricional['descripcion'] ?? 'Sin descripción registrada.') ?></p>
                    <p><strong>Objetivo:</strong> <?= e($planNutricional['objetivo'] ?? 'No definido') ?></p>
                    <span class="badge"><?= e($planNutricional['estado'] ?? 'activo') ?></span>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Comidas asignadas</h3>

                <?php if (empty($comidas)): ?>
                    <div class="empty">No tienes comidas registradas en tu plan nutricional.</div>
                <?php endif; ?>

                <?php foreach ($comidas as $comida): ?>
                    <div class="meal">
                        <strong><?= e($comida['nombre'] ?? 'Comida') ?></strong>
                        <p><?= e($comida['descripcion'] ?? 'Sin descripción') ?></p>
                        <p><strong>Hora sugerida:</strong> <?= e($comida['hora'] ?? 'No definida') ?></p>
                        <p><strong>Calorías:</strong> <?= e($comida['calorias'] ?? 'No registradas') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>