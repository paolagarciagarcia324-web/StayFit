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
    <title>Nutrición | StayFit</title> <!-- Título -->

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
            grid-template-columns: 1fr 1.2fr;
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
        <a href="../../controllers/cliente/dashboardController.php">Dashboard</a>
        <a href="../../controllers/cliente/perfilController.php">Perfil</a>
        <a href="../../controllers/cliente/planController.php">Mi plan</a>
        <a href="../../controllers/cliente/entrenamientoController.php">Entrenamiento</a>
        <a class="active" href="../../controllers/cliente/nutricionController.php">Nutrición</a>
        <a href="../../controllers/cliente/progresoController.php">Progreso</a>
        <a href="../../controllers/cliente/calendarioController.php">Calendario</a>
        <a href="../../controllers/cliente/comunicacionController.php">Comunicación</a>
        <a href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
    </aside>

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