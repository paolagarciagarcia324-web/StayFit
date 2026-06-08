<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$cliente = $cliente ?? [];
$usuario = $usuario ?? [];
$datosFisicos = $datosFisicos ?? [];
$institucion = $institucion ?? [];

$nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
if ($nombreCompleto === '') {
    $nombreCompleto = $cliente['nombre'] ?? $cliente['nombre_completo'] ?? '';
}

$iniciales = '';
foreach (preg_split('/\s+/', trim($nombreCompleto)) as $parte) {
    if ($parte !== '') {
        $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
    }
    if (mb_strlen($iniciales) >= 2) {
        break;
    }
}
$iniciales = $iniciales !== '' ? $iniciales : 'FF';

$pesoVal = $datosFisicos['peso'] ?? $datosFisicos['peso_inicial'] ?? '';
if ($pesoVal !== '' && is_numeric($pesoVal)) {
    $pesoVal = rtrim(rtrim(number_format((float) $pesoVal, 1, '.', ''), '0'), '.');
}

$estadoCuenta = strtolower(trim((string) ($cliente['estado'] ?? $usuario['estado'] ?? 'activo')));
$estadoBadgeClass = match ($estadoCuenta) {
    'activa', 'activo' => 'fp-badge fp-badge-ok',
    'suspendida', 'suspendido' => 'fp-badge fp-badge-alert',
    default => 'fp-badge fp-badge-pending',
};
$estadoLabel = match ($estadoCuenta) {
    'activa', 'activo' => 'Cuenta activa',
    'inactiva', 'inactivo' => 'Cuenta inactiva',
    'suspendida' => 'Cuenta suspendida',
    default => $estadoCuenta !== '' ? ucfirst($estadoCuenta) : 'Sin estado',
};

$objetivoResumen = trim((string) ($datosFisicos['objetivo'] ?? $cliente['objetivos'] ?? ''));
if ($objetivoResumen === '') {
    $objetivoResumen = 'Aún no registrado';
} elseif (mb_strlen($objetivoResumen) > 80) {
    $objetivoResumen = mb_substr($objetivoResumen, 0, 77) . '…';
}

$tipoCliente = ucfirst(strtolower(trim((string) ($cliente['tipo_cliente'] ?? 'institucional'))));
$nombreTopbar = $nombreCompleto !== '' ? $nombreCompleto : ($_SESSION['nombre'] ?? 'Cliente institucional');
$institucionNombre = trim((string) ($institucion['nombre'] ?? $cliente['institucion'] ?? ''));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Perfil Institucional | StayFit</title>
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
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 13px 18px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        

        .info-box {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 14px;
        }

        .info-box strong {
            color: #D63384;
        }
    </style>
</head>
<body class="fp-panel">

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente institucional</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreTopbar) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Tu cuenta institucional</span>
                <h1>Mi <span>perfil</span></h1>
                <p>Actualiza tus datos personales y físicos para mantener una trazabilidad clara de tu proceso con tu institución y coach.</p>
            </section>

            <section class="fp-stats-premium">
                <article class="fp-stat-premium fp-stat-premium--fuchsia">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon fp-coach-avatar" aria-hidden="true"><?= e($iniciales) ?></div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:18px;line-height:1.3;"><?= e($nombreCompleto !== '' ? $nombreCompleto : 'Sin nombre') ?></p>
                    <p class="fp-stat-premium-label">Nombre registrado</p>
                </article>

                <form action="../../controllers/clienteIns/perfilController.php?accion=actualizar" method="POST">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?= e($usuario['nombre'] ?? $cliente['nombre'] ?? '') ?>" required>

                <article class="fp-stat-premium">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 7h8M8 11h8M8 15h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:15px;">
                        <?= e($institucionNombre !== '' ? $institucionNombre : 'Sin institución') ?>
                    </p>
                    <p class="fp-stat-premium-label">Institución vinculada</p>
                </article>
            </section>

            <div class="fp-perfil-grid">
                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                        <h3>Datos personales</h3>
                        <p>Información de contacto e identificación vinculada a tu cuenta institucional.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <form class="fp-form-premium" action="../../controllers/clienteIns/perfilController.php?accion=actualizar" method="POST">
                            <div class="fp-form-grid">
                                <div class="fp-field fp-field--full">
                                    <label for="perfil-nombre">Nombre completo</label>
                                    <input type="text" id="perfil-nombre" name="nombre" value="<?= e($nombreCompleto) ?>" required>
                                </div>
                                <div class="fp-field">
                                    <label for="perfil-correo">Correo</label>
                                    <input type="email" id="perfil-correo" name="correo" value="<?= e($usuario['correo'] ?? '') ?>" required>
                                </div>
                                <div class="fp-field">
                                    <label for="perfil-celular">Celular</label>
                                    <input type="text" id="perfil-celular" name="celular" value="<?= e($cliente['celular'] ?? $usuario['telefono'] ?? '') ?>" required>
                                </div>
                                <div class="fp-field">
                                    <label for="perfil-id">Identificación</label>
                                    <input type="text" id="perfil-id" name="identificacion" value="<?= e($cliente['identificacion'] ?? $usuario['documento_identidad'] ?? '') ?>" required>
                                </div>
                                <div class="fp-field">
                                    <label for="perfil-edad">Edad</label>
                                    <input type="number" id="perfil-edad" name="edad" min="10" max="120" value="<?= e($cliente['edad'] ?? '') ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="fp-form-submit">Actualizar perfil</button>
                        </form>
                    </div>
                </article>

                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--mint">
                        <h3>Registro de progreso</h3>
                        <p>Registra peso, medidas y evidencia visual para que tu coach haga seguimiento.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <form class="fp-form-premium" action="../../controllers/clienteIns/progresoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                            <div class="fp-form-grid">
                                <div class="fp-field">
                                    <label for="perfil-peso">Peso actual (kg)</label>
                                    <input type="number" id="perfil-peso" step="0.1" min="20" max="300" name="peso" value="<?= e($pesoVal) ?>" required>
                                </div>
                                <div class="fp-field">
                                    <label for="perfil-medidas">Medidas corporales</label>
                                    <input type="text" id="perfil-medidas" name="medidas" placeholder="Ej: cintura, cadera, brazo">
                                    <span class="fp-field-hint">Separa las medidas con comas si registras varias.</span>
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="perfil-observacion">Observación</label>
                                    <textarea id="perfil-observacion" name="observacion" rows="3" placeholder="Registra cómo te has sentido en el proceso"></textarea>
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="perfil-foto">Foto de progreso</label>
                                    <div class="fp-progreso-file">
                                        <input type="file" id="perfil-foto" name="foto" accept="image/*">
                                        <label for="perfil-foto" class="fp-progreso-file-label">
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
                            <button type="submit" class="fp-form-submit fp-perfil-submit-mint">Guardar progreso</button>
                        </form>
                    </div>
                </article>
            </div>

            <div class="fp-perfil-grid">
                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--neutral">
                        <h3>Accesos rápidos</h3>
                        <p>Consulta tu historial completo y el detalle de tu convenio institucional.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <div class="fp-perfil-resumen">
                            <a class="fp-perfil-resumen-item" href="../../controllers/clienteIns/progresoController.php" style="text-decoration:none;">
                                <span class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 19h16M6 16l3-6 4 4 5-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Ver historial de progreso</strong>
                                    <span>Revisa todos tus registros anteriores</span>
                                </div>
                            </a>
                            <a class="fp-perfil-resumen-item" href="../../controllers/clienteIns/institucionController.php" style="text-decoration:none;">
                                <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M8 7h8M8 11h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Mi institución</strong>
                                    <span>Datos del convenio y contacto</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </article>

                <form action="../../controllers/clienteIns/progresoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                    <label>Peso actual</label>
                    <input type="number" step="0.1" name="peso" value="<?= e($datosFisicos['peso'] ?? '') ?>" required>

                    <label>Medidas corporales</label>
                    <textarea name="medidas" placeholder="Ej: cintura, cadera, pierna, brazo"></textarea>

                    <label>Observación</label>
                    <textarea name="observacion" placeholder="Registra cómo te has sentido en el proceso"></textarea>

                    <label>Foto de progreso</label>
                    <input type="file" name="foto" accept="image/*">

                    <button class="btn-green" type="submit">Guardar progreso</button>
                </form>
            </div>

        </main>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('perfil-foto');
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
