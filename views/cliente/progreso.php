<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$progresos = $progresos ?? []; // Historial de progresos

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Progreso | StayFit</title> <!-- Título -->

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
            grid-template-columns: 360px 1fr;
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
            min-height: 90px;
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

        .progress-item {
            border-left: 5px solid #D63384;
            background: #fff7fb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .progress-item strong {
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
            border-radius: 16px;
            padding: 18px;
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
        <a href="../../controller/cliente/dashboardController.php">Dashboard</a>
        <a href="../../controller/cliente/perfilController.php">Perfil</a>
        <a href="../../controller/cliente/planController.php">Mi plan</a>
        <a href="../../controller/cliente/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/cliente/nutricionController.php">Nutrición</a>
        <a class="active" href="../../controller/cliente/progresoController.php">Progreso</a>
        <a href="../../controller/cliente/calendarioController.php">Calendario</a>
        <a href="../../controller/cliente/comunicacionController.php">Comunicación</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Mi progreso</h1>
            <p>Registra tus avances físicos y revisa tu evolución durante el proceso StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Registrar progreso</h3>

                <form action="../../controller/cliente/progresoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                    <label>Peso actual</label>
                    <input type="number" step="0.1" name="peso" required>

                    <label>Medidas corporales</label>
                    <textarea name="medidas" placeholder="Ej: cintura, cadera, pierna, brazo"></textarea>

                    <label>Observación</label>
                    <textarea name="observacion" placeholder="Cuéntale a tu coach cómo te has sentido"></textarea>

                    <label>Foto de progreso</label>
                    <input type="file" name="foto" accept="image/*">

                    <button type="submit">Guardar progreso</button>
                </form>
            </div>

            <div class="card">
                <h3>Historial de progreso</h3>

                <?php if (empty($progresos)): ?>
                    <div class="empty">Aún no tienes registros de progreso.</div>
                <?php endif; ?>

                <?php foreach ($progresos as $item): ?>
                    <div class="progress-item">
                        <strong><?= e($item['peso'] ?? '0') ?> kg</strong>
                        <p><strong>Medidas:</strong> <?= e($item['medidas'] ?? 'No registradas') ?></p>
                        <p><?= e($item['observacion'] ?? 'Sin observación') ?></p>
                        <span class="badge"><?= e($item['fecha'] ?? 'Fecha no registrada') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>