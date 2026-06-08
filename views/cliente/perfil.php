<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$cliente = $cliente ?? [];
$usuario = $usuario ?? [];
$datosFisicos = $datosFisicos ?? [];
$cuenta = $cuenta ?? [];

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

$estaturaVal = $datosFisicos['estatura'] ?? '';
if ($estaturaVal !== '' && is_numeric($estaturaVal)) {
    $estaturaVal = rtrim(rtrim(number_format((float) $estaturaVal, 2, '.', ''), '0'), '.');
}

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

$objetivoResumen = trim((string) ($datosFisicos['objetivo'] ?? ''));
if ($objetivoResumen === '') {
    $objetivoResumen = 'Aún no registrado';
} elseif (mb_strlen($objetivoResumen) > 80) {
    $objetivoResumen = mb_substr($objetivoResumen, 0, 77) . '…';
}

$nombreTopbar = $nombreCompleto !== '' ? $nombreCompleto : ($_SESSION['nombre'] ?? 'Cliente');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Perfil | StayFit</title>
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

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente individual</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreTopbar) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Tu cuenta</span>
                <h1>Mi <span>perfil</span></h1>
                <p>Actualiza tus datos personales, físicos y de acceso para mantener tu información al día con tu coach.</p>
            </section>

            <section class="fp-stats-premium">
                <article class="fp-stat-premium fp-stat-premium--fuchsia">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon fp-coach-avatar" aria-hidden="true"><?= e($iniciales) ?></div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:18px;line-height:1.3;"><?= e($nombreCompleto !== '' ? $nombreCompleto : 'Sin nombre') ?></p>
                    <p class="fp-stat-premium-label">Nombre registrado</p>
                </article>

                <form action="../../controllers/cliente/perfilController.php?accion=actualizar" method="POST">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?= e($usuario['nombre'] ?? $cliente['nombre'] ?? '') ?>" required>

                <article class="fp-stat-premium">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:15px;">
                        <span class="<?= e($estadoBadgeClass) ?>"><?= e($estadoLabel) ?></span>
                    </p>
                    <p class="fp-stat-premium-label">Estado de membresía</p>
                </article>
            </section>

            <div class="fp-perfil-grid">
                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                        <h3>Datos personales</h3>
                        <p>Información de contacto e identificación vinculada a tu cuenta.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <form class="fp-form-premium" action="../../controllers/cliente/perfilController.php?accion=actualizar" method="POST">
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
                        <h3>Datos físicos</h3>
                        <p>Métricas y objetivos que tu coach usa para personalizar tu plan.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <form class="fp-form-premium" action="../../controllers/cliente/datosFisicosController.php?accion=actualizar" method="POST">
                            <div class="fp-form-grid">
                                <div class="fp-field">
                                    <label for="perfil-peso">Peso (kg)</label>
                                    <input type="number" id="perfil-peso" step="0.1" min="20" max="300" name="peso" value="<?= e($pesoVal) ?>" required>
                                </div>
                                <div class="fp-field">
                                    <label for="perfil-estatura">Estatura (m)</label>
                                    <input type="number" id="perfil-estatura" step="0.01" min="1" max="2.5" name="estatura" value="<?= e($estaturaVal) ?>" placeholder="Ej: 1.70" required>
                                    <span class="fp-field-hint">Usa metros con punto decimal (ej. 1.65).</span>
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="perfil-objetivo">Objetivo principal</label>
                                    <textarea id="perfil-objetivo" name="objetivo" rows="3" placeholder="Ej: tonificar, bajar grasa, ganar resistencia" required><?= e($datosFisicos['objetivo'] ?? '') ?></textarea>
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="perfil-restricciones">Restricciones médicas</label>
                                    <textarea id="perfil-restricciones" name="restricciones" rows="2" placeholder="Lesiones, alergias o limitaciones"><?= e($datosFisicos['restricciones'] ?? '') ?></textarea>
                                </div>
                                <div class="fp-field fp-field--full">
                                    <label for="perfil-observaciones">Observaciones para tu coach</label>
                                    <textarea id="perfil-observaciones" name="observaciones" rows="2" placeholder="Comentarios adicionales"><?= e($datosFisicos['observaciones'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <button type="submit" class="fp-form-submit fp-perfil-submit-mint">Guardar datos físicos</button>
                        </form>
                    </div>
                </article>
            </div>

            <div class="fp-perfil-grid">
                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--neutral">
                        <h3>Seguridad</h3>
                        <p>Protege tu acceso con una contraseña segura y actualizada.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <form class="fp-form-premium" action="../../controllers/cliente/cuentaController.php?accion=cambiarPassword" method="POST">
                            <div class="fp-field fp-field--full">
                                <label for="perfil-password">Nueva contraseña</label>
                                <input type="password" id="perfil-password" name="password" minlength="6" placeholder="Mínimo 6 caracteres" required>
                            </div>
                            <button type="submit" class="fp-form-submit">Actualizar contraseña</button>
                        </form>
                    </div>
                </article>

                <form action="../../controllers/cliente/datosFisicosController.php?accion=actualizar" method="POST">
                    <label>Peso (kg)</label>
                    <input type="number" step="0.1" name="peso" value="<?= e($datosFisicos['peso'] ?? '') ?>" required>

                    <label>Estatura (cm)</label>
                    <input type="number" step="0.1" name="estatura" value="<?= e($datosFisicos['estatura'] ?? '') ?>" required>

                    <label>Objetivo</label>
                    <textarea name="objetivo" placeholder="Ej: tonificar, bajar grasa, ganar resistencia" required><?= e($datosFisicos['objetivo'] ?? '') ?></textarea>

                    <label>Restricciones</label>
                    <textarea name="restricciones" placeholder="Lesiones, alergias o limitaciones"><?= e($datosFisicos['restricciones'] ?? '') ?></textarea>

                    <label>Observaciones</label>
                    <textarea name="observaciones" placeholder="Comentarios adicionales para tu coach"><?= e($datosFisicos['observaciones'] ?? '') ?></textarea>

                    <button class="btn-green" type="submit">Guardar datos físicos</button>
                </form>
            </div>

        </section>

        <section class="grid" style="margin-top: 24px;">

            <div class="card">
                <h3>Cambiar contraseña</h3>

                <form action="../../controllers/cliente/cuentaController.php?accion=cambiarPassword" method="POST">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password" minlength="6" required>

                    <button type="submit">Actualizar contraseña</button>
                </form>
            </div>

            <div class="card">
                <h3>Resumen de cuenta</h3>

                <div class="info-box">
                    <strong>Correo:</strong>
                    <?= e($usuario['correo'] ?? $cuenta['correo'] ?? '') ?>
                </div>

                <div class="info-box">
                    <strong>Estado:</strong>
                    <?= e($cliente['estado'] ?? $usuario['estado'] ?? 'activo') ?>
                </div>

                <div class="info-box">
                    <strong>Objetivo actual:</strong>
                    <?= e($datosFisicos['objetivo'] ?? 'Aún no registrado') ?>
                </div>
            </div>

        </section>

    </main>
</div>
</body>
</html>
