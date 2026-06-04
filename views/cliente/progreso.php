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
    <title>Progreso | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
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

        

        .empty {
            background: #f4f4f4;
            color: #777;
            border-radius: 16px;
            padding: 18px;
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Mi progreso</h1>
            <p>Registra tus avances físicos y revisa tu evolución durante el proceso StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Registrar progreso</h3>

                <form action="../../controllers/cliente/progresoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
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