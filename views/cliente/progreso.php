<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('fpFormatearPeso')) {
    function fpFormatearPeso($peso) {
        if ($peso === null || $peso === '') {
            return '—';
        }

        return rtrim(rtrim(number_format((float) $peso, 2, '.', ''), '0'), '.') . ' kg';
    }
}

if (!function_exists('fpMedidasLista')) {
    function fpMedidasLista($item) {
        $mapa = [
            'cintura' => 'Cintura',
            'cadera' => 'Cadera',
            'brazos' => 'Brazo',
            'piernas' => 'Pierna',
        ];
        $partes = [];

        foreach ($mapa as $clave => $etiqueta) {
            if (!empty($item[$clave]) && is_numeric($item[$clave])) {
                $valor = rtrim(rtrim(number_format((float) $item[$clave], 1, '.', ''), '0'), '.');
                $partes[] = ['label' => $etiqueta, 'valor' => $valor . ' cm'];
            }
        }

        if (empty($partes) && !empty($item['medidas'])) {
            $partes[] = ['label' => 'Medidas', 'valor' => trim((string) $item['medidas'])];
        }

        return $partes;
    }
}

if (!function_exists('fpFormatearFechaProgreso')) {
    function fpFormatearFechaProgreso($fecha) {
        if ($fecha === null || $fecha === '') {
            return 'Sin fecha';
        }

        $ts = strtotime((string) $fecha);

        if ($ts === false) {
            return (string) $fecha;
        }

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        return date('d', $ts) . ' ' . $meses[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    }
}

$progresos = $progresos ?? [];
$nombreTopbar = $_SESSION['nombre'] ?? 'Cliente';

$totalRegistros = count($progresos);
$ultimo = $progresos[0] ?? null;
$anterior = $progresos[1] ?? null;
$pesoActual = $ultimo['peso'] ?? null;

$deltaPeso = null;
if ($ultimo && $anterior && is_numeric($ultimo['peso'] ?? null) && is_numeric($anterior['peso'] ?? null)) {
    $deltaPeso = round((float) $ultimo['peso'] - (float) $anterior['peso'], 1);
}

$conFoto = 0;
foreach ($progresos as $reg) {
    if (!empty($reg['fotos_evolucion'])) {
        $conFoto++;
    }
}

$pesoMini = array_slice(array_reverse($progresos), 0, 6);
$pesosNumericos = array_values(array_filter(array_map(function ($r) {
    return is_numeric($r['peso'] ?? null) ? (float) $r['peso'] : null;
}, $pesoMini), function ($v) {
    return $v !== null;
}));
$pesoMin = $pesosNumericos ? min($pesosNumericos) : 0;
$pesoMax = $pesosNumericos ? max($pesosNumericos) : 0;
$rangoPeso = max($pesoMax - $pesoMin, 0.1);

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
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Tu evolución</span>
                <h1>Mi <span>progreso</span></h1>
                <p>Registra tus avances físicos y revisa tu historial para ver cómo evolucionas con tu coach.</p>
            </section>

            <section class="fp-stats-premium">
                <article class="fp-stat-premium fp-stat-premium--fuchsia">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3v18M8 7h8M7 11h10M6 15h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e(fpFormatearPeso($pesoActual)) ?></p>
                    <p class="fp-stat-premium-label">Peso más reciente</p>
                </article>

                <article class="fp-stat-premium fp-stat-premium--mint">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M4 19h16M6 16l3-6 4 4 5-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value">
                        <?php if ($deltaPeso === null): ?>
                            —
                        <?php elseif ($deltaPeso > 0): ?>
                            +<?= e((string) $deltaPeso) ?> kg
                        <?php elseif ($deltaPeso < 0): ?>
                            <?= e((string) $deltaPeso) ?> kg
                        <?php else: ?>
                            0 kg
                        <?php endif; ?>
                    </p>
                    <p class="fp-stat-premium-label">Cambio vs. registro anterior</p>
                </article>

                <article class="fp-stat-premium">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="5" width="16" height="15" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 3v4M16 3v4M4 10h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e((string) $totalRegistros) ?></p>
                    <p class="fp-stat-premium-label">Registros · <?= e((string) $conFoto) ?> con foto</p>
                </article>
            </section>

            <?php if (count($pesosNumericos) >= 2): ?>
            <section class="fp-progreso-trend">
                <div class="fp-progreso-trend-head">
                    <strong>Tendencia de peso</strong>
                    <span>Últimos <?= e((string) count($pesosNumericos)) ?> registros</span>
                </div>
                <div class="fp-progreso-trend-bars" role="img" aria-label="Gráfico de tendencia de peso">
                    <?php foreach ($pesosNumericos as $pesoBar): ?>
                        <?php
                        $altura = 24 + (int) round((($pesoBar - $pesoMin) / $rangoPeso) * 56);
                        ?>
                        <div class="fp-progreso-trend-bar" style="height: <?= e((string) $altura) ?>px;" title="<?= e(fpFormatearPeso($pesoBar)) ?>"></div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <div class="fp-progreso-grid">
                <article class="fp-card card fp-progreso-card">
                    <div class="fp-progreso-card-head fp-progreso-card-head--fuchsia">
                        <h3>Registrar progreso</h3>
                        <p>Completa tu peso, medidas y una nota para que tu coach haga seguimiento.</p>
                    </div>
                    <div class="fp-progreso-card-body">
                        <form class="fp-form-premium fp-progreso-form" action="../../controllers/cliente/progresoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                            <div class="fp-form-grid">
                                <div class="fp-field">
                                    <label for="prog-peso">Peso actual (kg)</label>
                                    <input type="number" id="prog-peso" step="0.1" min="20" max="300" name="peso" placeholder="Ej: 78.5" required>
                                </div>
                                <div class="fp-field">
                                    <label for="prog-cintura">Cintura (cm)</label>
                                    <input type="number" id="prog-cintura" step="0.1" min="40" max="200" name="cintura" placeholder="Opcional">
                                </div>
                                <div class="fp-field">
                                    <label for="prog-cadera">Cadera (cm)</label>
                                    <input type="number" id="prog-cadera" step="0.1" min="40" max="200" name="cadera" placeholder="Opcional">
                                </div>
                                <div class="fp-field">
                                    <label for="prog-brazos">Brazo (cm)</label>
                                    <input type="number" id="prog-brazos" step="0.1" min="15" max="80" name="brazos" placeholder="Opcional">
                                </div>
                                <div class="fp-field">
                                    <label for="prog-piernas">Pierna (cm)</label>
                                    <input type="number" id="prog-piernas" step="0.1" min="30" max="120" name="piernas" placeholder="Opcional">
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="prog-observacion">Observación</label>
                                    <textarea id="prog-observacion" name="observacion" rows="3" placeholder="Cuéntale a tu coach cómo te has sentido, tu energía o cambios que notes"></textarea>
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="prog-foto">Foto de progreso</label>
                                    <div class="fp-progreso-file">
                                        <input type="file" id="prog-foto" name="foto" accept="image/*">
                                        <label for="prog-foto" class="fp-progreso-file-label">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 16l4-4 4 4 6-8 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                            <span>Seleccionar imagen</span>
                                            <small>JPG o PNG · opcional</small>
                                        </label>
                                        <span class="fp-progreso-file-name" data-file-name></span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="fp-form-submit fp-progreso-submit">Guardar progreso</button>
                        </form>
                    </div>
                </article>

                <article class="fp-card card fp-progreso-card">
                    <div class="fp-progreso-card-head fp-progreso-card-head--mint">
                        <h3>Historial de progreso</h3>
                        <p>Tus registros más recientes aparecen primero.</p>
                    </div>
                    <div class="fp-progreso-card-body">
                        <?php if (empty($progresos)): ?>
                            <div class="fp-progreso-empty">
                                <div class="fp-progreso-empty-icon" aria-hidden="true">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 19h16M6 16l3-6 4 4 5-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <strong>Aún no tienes registros</strong>
                                <p>Guarda tu primer peso y medidas para empezar a ver tu evolución aquí.</p>
                            </div>
                        <?php else: ?>
                            <div class="fp-progreso-timeline">
                                <?php foreach ($progresos as $index => $item): ?>
                                    <?php
                                    $medidas = fpMedidasLista($item);
                                    $foto = trim((string) ($item['fotos_evolucion'] ?? ''));
                                    $obs = trim((string) ($item['observacion'] ?? ''));
                                    ?>
                                    <article class="fp-progreso-item">
                                        <div class="fp-progreso-item-accent" aria-hidden="true"></div>
                                        <div class="fp-progreso-item-main">
                                            <header class="fp-progreso-item-head">
                                                <div>
                                                    <span class="fp-progreso-item-index">#<?= e((string) ($totalRegistros - $index)) ?></span>
                                                    <strong class="fp-progreso-item-peso"><?= e(fpFormatearPeso($item['peso'] ?? null)) ?></strong>
                                                </div>
                                                <time class="fp-progreso-item-fecha" datetime="<?= e($item['fecha'] ?? '') ?>">
                                                    <?= e(fpFormatearFechaProgreso($item['fecha'] ?? '')) ?>
                                                </time>
                                            </header>

                                            <?php if (!empty($medidas)): ?>
                                                <ul class="fp-progreso-medidas">
                                                    <?php foreach ($medidas as $medida): ?>
                                                        <li>
                                                            <span><?= e($medida['label']) ?></span>
                                                            <strong><?= e($medida['valor']) ?></strong>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="fp-progreso-sin-medidas">Sin medidas corporales registradas</p>
                                            <?php endif; ?>

                                            <?php if ($obs !== ''): ?>
                                                <p class="fp-progreso-obs"><?= e($obs) ?></p>
                                            <?php endif; ?>

                                            <?php if ($foto !== ''): ?>
                                                <a class="fp-progreso-foto" href="../../<?= e(ltrim($foto, '/')) ?>" target="_blank" rel="noopener">
                                                    <img src="../../<?= e(ltrim($foto, '/')) ?>" alt="Foto de progreso del <?= e(fpFormatearFechaProgreso($item['fecha'] ?? '')) ?>">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

        </main>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('prog-foto');
    var nameEl = document.querySelector('[data-file-name]');
    if (!input || !nameEl) return;
    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        nameEl.textContent = file ? file.name : '';
    });
})();
</script>
</body>
</html>
