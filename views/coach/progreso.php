<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Clientes asignados
$progresos = $progresos ?? []; // Progresos registrados
$avanceVirtual = $avanceVirtual ?? []; // Seguimiento virtual
$reporteClientes = $reporteClientes ?? []; // Reporte clientes
$reporteProgreso = $reporteProgreso ?? []; // Reporte progreso
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Progreso Coach | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
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
            min-height: 110px;
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
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 15px;
        }

        .virtual-item {
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
            <h1>Progreso y seguimiento</h1>
            <p>Revisa avances físicos, seguimiento virtual y registra observaciones profesionales.</p>
        </section>

        <section class="grid">

            <div>
                <div class="card">
                    <h3>Registrar observación</h3>

                    <form action="../../controllers/coach/progresoController.php?accion=observacion" method="POST">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($cliente['nombre'] ?? 'Cliente') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Observación profesional</label>
                        <textarea name="observacion" placeholder="Escribe una recomendación o análisis del progreso..." required></textarea>

                        <button type="submit">Guardar observación</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Seguimiento virtual</h3>

                    <?php if (empty($avanceVirtual)): ?>
                        <div class="empty">No hay seguimiento virtual registrado.</div>
                    <?php endif; ?>

                    <?php foreach ($avanceVirtual as $item): ?>
                        <div class="virtual-item">
                            <strong><?= e($item['cliente'] ?? 'Cliente') ?></strong>
                            <p>Avance: <?= e($item['avance'] ?? 0) ?>%</p>
                            <span class="badge"><?= e($item['estado'] ?? 'activo') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Progresos registrados</h3>

                    <?php if (empty($progresos)): ?>
                        <div class="empty">No hay registros de progreso todavía.</div>
                    <?php endif; ?>

                    <?php foreach ($progresos as $item): ?>
                        <div class="progress-item">
                            <strong><?= e($item['cliente'] ?? 'Cliente') ?></strong>
                            <p><strong>Peso:</strong> <?= e($item['peso'] ?? '0') ?> kg</p>
                            <p><strong>Medidas:</strong> <?= e($item['medidas'] ?? 'No registradas') ?></p>
                            <p><?= e($item['observacion'] ?? 'Sin observación') ?></p>
                            <span class="badge"><?= e($item['fecha'] ?? 'Fecha no registrada') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h3>Resumen de reportes</h3>

                    <p><strong>Clientes en reporte:</strong> <?= e(count($reporteClientes)) ?></p>
                    <p><strong>Indicadores de progreso:</strong> <?= e(count($reporteProgreso)) ?></p>
                </div>
            </div>

        </section>

    </main>
</div>
</body>
</html>