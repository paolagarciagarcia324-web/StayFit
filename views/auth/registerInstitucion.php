<?php

session_start();

if (!function_exists('rutaBaseProyecto')) {
    require_once __DIR__ . '/../../config/helpers.php';
}

$alert = $_SESSION['alert'] ?? null;
$old = $_SESSION['old_inst'] ?? [];
unset($_SESSION['alert'], $_SESSION['old_inst']);

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$token = $token ?? '';
$enlace = $enlace ?? null;
$errorEnlace = $errorEnlace ?? null;
$basePath = $basePath ?? rutaBaseProyecto();
$institucionNombre = $enlace['institucion_nombre'] ?? 'Institución';
$planNombre = $enlace['plan_nombre'] ?? '';
$planDuracion = (int) ($enlace['plan_duracion'] ?? 30);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro institucional | FigueFit</title>
    <style>
        :root {
            --fuchsia: #ff2fa0;
            --mint: #55eadc;
            --white: #ffffff;
            --text-soft: #b8c0d4;
            --text-muted: #8b93a7;
            --card-dark: #171922;
            --input-bg: #2a2d3a;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background:
                radial-gradient(ellipse at top left, rgba(255,47,160,0.18) 0%, transparent 45%),
                radial-gradient(ellipse at bottom right, rgba(85,234,220,0.12) 0%, transparent 45%),
                linear-gradient(160deg, #1a0e1f 0%, #0d0d14 40%, #0a0b10 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        a { text-decoration: none; color: var(--mint); }
        .auth-wrapper {
            width: min(100%, 1080px);
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.45);
            border: 1px solid rgba(255,255,255,0.07);
        }
        .auth-visual {
            position: relative;
            padding: 36px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 520px;
            background:
                linear-gradient(160deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.25) 100%),
                url("<?= e($basePath) ?>/img/register.png") center/cover no-repeat;
        }
        .brand { display:inline-flex; align-items:center; gap:10px; font-size:24px; font-weight:900; color:var(--white); position:relative; z-index:1; }
        .brand img { width:52px; height:52px; object-fit:contain; border-radius:10px; background:rgba(255,255,255,0.08); padding:5px; }
        .brand em { color:var(--fuchsia); font-style:normal; }
        .visual-body { position:relative; z-index:1; margin-top:30px; }
        .visual-body h1 { font-size:28px; font-weight:900; line-height:1.2; margin-bottom:12px; }
        .visual-body h1 span { color:var(--mint); }
        .visual-body p { color:rgba(255,255,255,0.88); font-size:14px; line-height:1.65; max-width:360px; }
        .inst-badge {
            display:inline-block;
            margin-top:16px;
            padding:8px 14px;
            border-radius:999px;
            background:rgba(85,234,220,0.12);
            border:1px solid rgba(85,234,220,0.35);
            color:var(--mint);
            font-size:12px;
            font-weight:700;
        }
        .auth-panel { background:var(--card-dark); padding:40px 52px; display:flex; align-items:center; justify-content:center; }
        .auth-card { width:100%; max-width:420px; }
        .auth-card h2 { font-size:26px; font-weight:900; margin-bottom:4px; }
        .subtitle { color:var(--text-muted); font-size:13px; margin-bottom:24px; line-height:1.5; }
        .alert { margin-bottom:14px; padding:10px 14px; border-radius:10px; background:rgba(255,47,160,0.08); border:1px solid rgba(255,47,160,0.25); color:var(--text-soft); font-size:13px; }
        .alert strong { display:block; color:var(--fuchsia); margin-bottom:3px; }
        .alert-error { background:rgba(255,80,80,0.08); border-color:rgba(255,80,80,0.3); }
        .alert-error strong { color:#ff6b6b; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px 20px; }
        .form-group { display:flex; flex-direction:column; gap:8px; }
        .form-group.full { grid-column:1 / -1; }
        label { font-size:12px; font-weight:700; color:var(--text-soft); }
        .input-wrap { position:relative; }
        .input-wrap .ico { position:absolute; top:50%; left:13px; transform:translateY(-50%); color:var(--text-muted); font-size:14px; pointer-events:none; }
        .input-wrap input { width:100%; height:44px; background:var(--input-bg); border:1px solid rgba(255,255,255,0.12); border-radius:10px; padding:0 12px 0 38px; font-size:13.5px; color:var(--white); outline:none; }
        .input-wrap input:focus { border-color:var(--fuchsia); box-shadow:0 0 0 3px rgba(255,47,160,0.13); }
        .helper-text { font-size:11.5px; color:var(--text-muted); line-height:1.5; margin:20px 0 18px; }
        .btn-register { width:100%; height:48px; border:none; border-radius:12px; background:var(--fuchsia); color:var(--white); font-size:14.5px; font-weight:900; cursor:pointer; box-shadow:0 4px 14px rgba(255,47,160,0.25); }
        .btn-register:hover { background:#e0238e; }
        .login-box { margin-top:18px; padding:14px 18px; border-radius:12px; border:2px solid var(--fuchsia); text-align:center; color:var(--text-muted); font-size:13px; }
        .home-link { display:block; margin-top:14px; text-align:center; color:var(--text-muted); font-size:12.5px; }
        @media (max-width:860px) {
            .auth-wrapper { grid-template-columns:1fr; max-width:480px; }
            .auth-visual { min-height:220px; }
            .auth-panel { padding:28px; }
        }
        @media (max-width:480px) { .form-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>

<section class="auth-wrapper">

    <aside class="auth-visual">
        <a href="<?= e($basePath) ?>/public/index.php" class="brand">
            <img src="<?= e($basePath) ?>/img/icono.png" alt="Logo FigueFit">
            <span>Figue<em>Fit</em></span>
        </a>

        <div class="visual-body">
            <?php if ($enlace && !$errorEnlace): ?>
                <h1>Registro para<br><span><?= e($institucionNombre) ?></span></h1>
                <p>Completa el formulario para crear tu cuenta corporativa. Quedarás vinculado únicamente a esta institución.</p>
                <?php if ($planNombre !== ''): ?>
                    <span class="inst-badge">Plan: <?= e($planNombre) ?> · <?= e((string) $planDuracion) ?> días</span>
                <?php endif; ?>
            <?php else: ?>
                <h1>Enlace no<br><span>disponible</span></h1>
                <p>El enlace de registro institucional no es válido o fue desactivado. Contacta al administrador de tu organización.</p>
            <?php endif; ?>
        </div>
    </aside>

    <section class="auth-panel">
        <div class="auth-card">

            <?php if ($errorEnlace): ?>
                <h2>Acceso restringido</h2>
                <p class="subtitle"><?= e($errorEnlace) ?></p>
                <div class="alert alert-error"><strong>No puedes registrarte</strong>Solicita un nuevo enlace al administrador de FigueFit o de tu institución.</div>
                <a href="<?= e($basePath) ?>/public/index.php" class="home-link">← Volver al inicio</a>
            <?php else: ?>

                <h2>Crear cuenta institucional</h2>
                <p class="subtitle">Registro exclusivo para miembros de <strong><?= e($institucionNombre) ?></strong>. Tu acceso será inmediato al panel institucional.</p>

                <?php if ($alert): ?>
                    <div class="alert">
                        <strong><?= e($alert['title'] ?? 'Aviso') ?></strong>
                        <?= e($alert['text'] ?? '') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= e(urlRegistroInstitucionForm($token)) ?>" method="POST">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="nombre_completo">Nombre completo</label>
                            <div class="input-wrap">
                                <span class="ico">👤</span>
                                <input id="nombre_completo" type="text" name="nombre_completo" placeholder="Nombre y apellido" value="<?= e($old['nombre_completo'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo electrónico</label>
                            <div class="input-wrap">
                                <span class="ico">✉</span>
                                <input id="correo" type="email" name="correo" placeholder="nombre@correo.com" value="<?= e($old['correo'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <div class="input-wrap">
                                <span class="ico">☎</span>
                                <input id="telefono" type="text" name="telefono" placeholder="Opcional" value="<?= e($old['telefono'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group full">
                            <label for="documento_identidad">Documento de identidad</label>
                            <div class="input-wrap">
                                <span class="ico">🪪</span>
                                <input id="documento_identidad" type="text" name="documento_identidad" placeholder="Número de documento" value="<?= e($old['documento_identidad'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group full">
                            <label for="cargo">Cargo / área (opcional)</label>
                            <div class="input-wrap">
                                <span class="ico">🏢</span>
                                <input id="cargo" type="text" name="cargo" placeholder="Ej. Recursos humanos" value="<?= e($old['cargo'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <div class="input-wrap">
                                <span class="ico">🔒</span>
                                <input id="password" type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirmar contraseña</label>
                            <div class="input-wrap">
                                <span class="ico">🔒</span>
                                <input id="password_confirm" type="password" name="password_confirm" placeholder="Repite tu contraseña" required>
                            </div>
                        </div>
                    </div>

                    <p class="helper-text">Al registrarte aceptas crear una cuenta vinculada a <?= e($institucionNombre) ?> con el plan de convenio asignado.</p>

                    <button type="submit" class="btn-register">Registrarme en la institución →</button>
                </form>

                <div class="login-box">
                    ¿Ya tienes cuenta? <a href="<?= e($basePath) ?>/views/auth/login.php">Iniciar sesión</a>
                </div>
                <a href="<?= e($basePath) ?>/public/index.php" class="home-link">← Volver al inicio</a>

            <?php endif; ?>

        </div>
    </section>

</section>

</body>
</html>
