<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$planes = $planes ?? [];
$planSeleccionado = (string) ($_GET['plan_id'] ?? '');
$alert = $_SESSION['alert'] ?? null;
$alertTipo = $_SESSION['alert_tipo'] ?? 'error';
unset($_SESSION['alert'], $_SESSION['alert_tipo']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de ingreso | FigueFit</title>
    <link rel="stylesheet" href="solicitud.css?v=2">
</head>
<body class="sol-page">

<header class="sol-topbar">
    <a href="index.php" class="sol-brand">
        <img src="../img/Logo.png" alt="FigueFit">
        Figue<em>Fit</em>
    </a>

    <nav class="sol-topnav">
        <a href="index.php">Inicio</a>
        <a href="planPublico.php">Planes</a>
        <a href="solicitud.php" class="is-active">Inscripción</a>
        <a href="../views/auth/login.php" class="sol-btn-login">Ingresar</a>
    </nav>
</header>

<div class="sol-shell">
    <div class="sol-wrapper">

        <aside class="sol-visual" aria-hidden="true"></aside>

        <section class="sol-panel">
            <header class="sol-form-head">
                <h2>Formulario de inscripción</h2>
                <p>Los campos marcados son obligatorios. Revisa que el plan y el monto coincidan con tu comprobante.</p>
            </header>

            <div class="sol-note">
                <span class="sol-note-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M12 10v6M12 7h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Después de enviar quedarás en estado <strong>pendiente</strong> hasta que el administrador valide tu pago.</span>
            </div>

            <?php if ($alert): ?>
                <div class="sol-alert sol-alert--<?= ($alertTipo ?? '') === 'success' ? 'success' : 'error' ?>">
                    <?= e($alert) ?>
                </div>
            <?php endif; ?>

            <form action="solicitud.php" method="POST" enctype="multipart/form-data" id="formSolicitud">

                <div class="sol-section">
                    <div class="sol-section-head">
                        <span class="sol-section-bar"></span>
                        <h3>Datos personales</h3>
                        <span>Paso 1</span>
                    </div>

                    <div class="sol-grid">
                        <div class="sol-field sol-field--full">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Ej. María García" required>
                        </div>

                        <div class="sol-field">
                            <label for="edad">Edad</label>
                            <input type="number" id="edad" name="edad" min="12" placeholder="25" required>
                        </div>

                        <div class="sol-field">
                            <label for="celular">Celular</label>
                            <input type="text" id="celular" name="celular" placeholder="300 123 4567" required>
                        </div>

                        <div class="sol-field sol-field--full">
                            <label for="correo">Correo electrónico</label>
                            <input type="email" id="correo" name="correo" placeholder="tu@correo.com" required>
                            <p class="sol-field-hint">Lo usarás para iniciar sesión cuando aprueben tu solicitud.</p>
                        </div>

                        <div class="sol-field">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" minlength="6" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
                        </div>

                        <div class="sol-field">
                            <label for="password_confirm">Confirmar contraseña</label>
                            <input type="password" id="password_confirm" name="password_confirm" minlength="6" placeholder="Repite la contraseña" required autocomplete="new-password">
                        </div>

                        <div class="sol-field sol-field--full">
                            <label for="identificacion">Número de identificación</label>
                            <input type="text" id="identificacion" name="identificacion" placeholder="Documento de identidad" required>
                        </div>
                    </div>
                </div>

                <div class="sol-section">
                    <div class="sol-section-head">
                        <span class="sol-section-bar"></span>
                        <h3>Plan y modalidad</h3>
                        <span>Paso 2</span>
                    </div>

                    <div class="sol-grid">
                        <div class="sol-field sol-field--full">
                            <label for="plan_id">Plan seleccionado</label>
                            <select id="plan_id" name="plan_id" required>
                                <option value="">Seleccione un plan</option>
                                <?php foreach ($planes as $plan): ?>
                                    <?php $planId = $plan['id_plan'] ?? $plan['id'] ?? ''; ?>
                                    <option value="<?= e($planId) ?>"
                                            data-modalidad="<?= e(strtolower($plan['modalidad'] ?? 'virtual')) ?>"
                                            <?= ($planSeleccionado !== '' && $planSeleccionado === (string) $planId) ? 'selected' : '' ?>>
                                        <?= e($plan['nombre'] ?? 'Plan') ?> — $<?= e(number_format((float) ($plan['precio'] ?? 0), 0, ',', '.')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="sol-field sol-field--full">
                            <label for="modalidad">Modalidad</label>
                            <select id="modalidad" name="modalidad" required>
                                <option value="">Seleccione modalidad</option>
                                <option value="presencial">Presencial</option>
                                <option value="virtual">Virtual</option>
                                <option value="mixta">Mixta</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="sol-section">
                    <div class="sol-section-head">
                        <span class="sol-section-bar"></span>
                        <h3>Información de pago</h3>
                        <span>Paso 3</span>
                    </div>

                    <div class="sol-grid">
                        <div class="sol-field">
                            <label for="tipo_cuenta">Tipo de cuenta</label>
                            <select id="tipo_cuenta" name="tipo_cuenta" required>
                                <option value="">Seleccione</option>
                                <option value="ahorros">Ahorros</option>
                                <option value="corriente">Corriente</option>
                                <option value="nequi">Nequi</option>
                                <option value="daviplata">Daviplata</option>
                            </select>
                        </div>

                        <div class="sol-field">
                            <label for="numero_cuenta">Número de cuenta</label>
                            <input type="text" id="numero_cuenta" name="numero_cuenta" placeholder="Cuenta origen del pago" required>
                        </div>

                        <div class="sol-field sol-field--full">
                            <label for="comprobante">Comprobante de pago</label>
                            <div class="sol-upload">
                                <input type="file" id="comprobante" name="comprobante" accept="image/*,.pdf" required>
                                <div class="sol-upload-box">
                                    <span class="sol-upload-icon" aria-hidden="true">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 16V4M12 4l-4 4M12 4l4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div class="sol-upload-text">
                                        <strong>Arrastra o selecciona tu comprobante</strong>
                                        <span>Formatos: JPG, PNG o PDF — máx. recomendado 5 MB</span>
                                    </div>
                                </div>
                                <p class="sol-upload-name" id="comprobanteNombre" hidden></p>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="sol-submit">Enviar solicitud</button>

                <p class="sol-form-foot">
                    ¿Ya tienes cuenta? <a href="../views/auth/login.php">Inicia sesión</a>
                    · <a href="planPublico.php">Ver planes</a>
                </p>
            </form>
        </section>

    </div>
</div>

<script>
(function () {
    var planSelect = document.getElementById('plan_id');
    var modalidadSelect = document.getElementById('modalidad');
    var fileInput = document.getElementById('comprobante');
    var fileName = document.getElementById('comprobanteNombre');

    if (planSelect && modalidadSelect) {
        function sincronizarModalidad() {
            var opt = planSelect.options[planSelect.selectedIndex];
            var mod = opt && opt.getAttribute('data-modalidad');
            if (!mod) return;
            for (var i = 0; i < modalidadSelect.options.length; i++) {
                if (modalidadSelect.options[i].value === mod) {
                    modalidadSelect.selectedIndex = i;
                    break;
                }
            }
        }

        planSelect.addEventListener('change', sincronizarModalidad);
        sincronizarModalidad();
    }

    if (fileInput && fileName) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) {
                fileName.textContent = 'Archivo: ' + fileInput.files[0].name;
                fileName.hidden = false;
            } else {
                fileName.hidden = true;
                fileName.textContent = '';
            }
        });
    }

    var form = document.getElementById('formSolicitud');
    var pass = document.getElementById('password');
    var passConfirm = document.getElementById('password_confirm');

    if (form && pass && passConfirm) {
        form.addEventListener('submit', function (e) {
            if (pass.value !== passConfirm.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                passConfirm.focus();
            }
        });
    }
})();
</script>

</body>
</html>
