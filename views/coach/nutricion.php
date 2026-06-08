<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('nombreClienteCoach')) {
    function nombreClienteCoach(array $cliente): string
    {
        $nombre = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
        if ($nombre === '' && !empty($cliente['nombre'])) {
            $nombre = trim((string) $cliente['nombre']);
        }

        return $nombre !== '' ? $nombre : 'Clienta';
    }
}

$clientes = $clientes ?? [];
$planesNutricionales = $planesNutricionales ?? [];
$comidas = $comidas ?? [];
$flash = $flash ?? null;

$tituloPagina = 'Nutrición Coach | FigueFit';
$vistaActiva = 'nutricion';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

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

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($clientes)): ?>
            <div class="fp-comunicacion-alert">
                <p>No tienes clientas asignadas. El administrador debe asignarte clientes desde <strong>Asignaciones</strong> antes de crear planes.</p>
            </div>
        <?php endif; ?>

        <div class="fp-perfil-grid">
            <article class="fp-card card fp-perfil-card">
                <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
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
            </article>

            <article class="fp-card card fp-perfil-card">
                <div class="fp-perfil-card-head fp-perfil-card-head--mint">
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
            </article>
        </div>

        <div class="fp-perfil-grid" style="margin-top: 22px;">
            <article class="fp-card card">
                <div class="fp-plan-card-head">
                    <h3>Planes nutricionales</h3>
                    <p><?= e((string) count($planesNutricionales)) ?> plan(es) creado(s)</p>
                </div>
                <div class="fp-plan-card-body">
                    <?php if (empty($planesNutricionales)): ?>
                        <div class="fp-pagos-empty">
                            <strong>Sin planes</strong>
                            <p>Crea tu primer plan con el formulario de la izquierda.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($planesNutricionales as $plan): ?>
                            <div class="fp-timeline-item item">
                                <strong><?= e($plan['nombre'] ?? 'Plan nutricional') ?></strong>
                                <?php if (!empty($plan['cliente'])): ?>
                                    <p>Clienta: <?= e($plan['cliente']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($plan['descripcion'])): ?>
                                    <p><?= e($plan['descripcion']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($plan['objetivo'])): ?>
                                    <p><strong>Objetivo:</strong> <?= e($plan['objetivo']) ?></p>
                                <?php endif; ?>
                                <span class="fp-badge fp-badge-ok"><?= e($plan['estado'] ?? 'activo') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="fp-card card">
                <div class="fp-plan-card-head">
                    <h3>Comidas registradas</h3>
                    <p><?= e((string) count($comidas)) ?> comida(s)</p>
                </div>
                <div class="fp-plan-card-body">
                    <?php if (empty($comidas)): ?>
                        <div class="fp-pagos-empty">
                            <strong>Sin comidas</strong>
                            <p>Agrega comidas a un plan existente.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comidas as $comida): ?>
                            <div class="fp-timeline-item item">
                                <strong><?= e($comida['nombre'] ?? $comida['tiempo_comida'] ?? 'Comida') ?></strong>
                                <?php if (!empty($comida['plan_nutricional'])): ?>
                                    <p>Plan: <?= e($comida['plan_nutricional']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($comida['descripcion'])): ?>
                                    <p><?= e($comida['descripcion']) ?></p>
                                <?php endif; ?>
                                <p>
                                    <?php if (!empty($comida['hora'])): ?>
                                        <span class="fp-tag-inline">Hora: <?= e($comida['hora']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($comida['calorias'])): ?>
                                        <span class="fp-tag-inline"><?= e($comida['calorias']) ?> kcal</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
        </div>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>
