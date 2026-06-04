<?php

session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | FigueFit</title>

    <style>
        :root {
            --black:        #0a0b0f;
            --dark:         #111318;
            --dark-card:    #16181f;
            --dark-input:   #1e2029;
            --fuchsia:      #ff2fa0;
            --fuchsia-glow: rgba(255, 47, 160, 0.28);
            --mint:         #55eadc;
            --white:        #ffffff;
            --text-muted:   #8b93a7;
            --text-soft:    #c8d0e0;
            --border:       rgba(255,255,255,0.07);
            --shadow-card:  0 40px 100px rgba(0,0,0,0.55);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background:
                radial-gradient(ellipse at top left,     rgba(255, 47, 160, 0.18) 0%, transparent 45%),
                radial-gradient(ellipse at bottom right, rgba(85, 234, 220, 0.12) 0%, transparent 45%),
                linear-gradient(160deg, #1a0e1f 0%, #0d0d14 40%, #0a0b10 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Destellos de fondo */
        body::before {
            content: "";
            position: fixed;
            top: -200px; right: -200px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(255,47,160,0.12) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: "";
            position: fixed;
            bottom: -200px; left: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(85,234,220,0.09) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        a { text-decoration: none; }

        /* ── WRAPPER ─────────────────────────────── */
        .auth-wrapper {
            position: relative;
            z-index: 1;
            width: min(100%, 1080px);
            min-height: 620px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border);
        }

        /* ── PANEL IZQUIERDO (imagen) ─────────────── */
        .auth-visual {
            position: relative;
            padding: 52px 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background:
                linear-gradient(160deg, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.40) 100%),
                url("../../img/login.png") center/cover no-repeat;
        }

        .auth-visual::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 85% 15%, rgba(255,47,160,0.25), transparent 35%),
                radial-gradient(circle at 10% 90%, rgba(85,234,220,0.18), transparent 35%);
            pointer-events: none;
        }

        .brand {
            position: relative; z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 0.3px;
            color: var(--white);
        }

        .brand img {
            width: 58px; height: 58px;
            object-fit: contain;
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            padding: 6px;
        }

        .brand em { color: var(--fuchsia); font-style: normal; }

        .visual-body {
            position: relative; z-index: 1;
            margin-top: 60px;
        }

        .visual-tag {
            display: inline-block;
            background: rgba(255,47,160,0.18);
            border: 1px solid rgba(255,47,160,0.35);
            color: var(--fuchsia);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 30px;
            margin-bottom: 20px;
        }

        .visual-body h2 {
            font-size: 38px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 18px;
            color: var(--white);
        }

        .visual-body h2 span { color: var(--fuchsia); }

        .visual-body p {
        color: #ffffff;
        font-size: 16.5px;
        line-height: 1.8;
        font-weight: 600;
        max-width: 430px;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.65);
}

        .visual-footer {
            position: relative; z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            backdrop-filter: blur(12px);
            margin-top: auto;
            margin-bottom: 0;
        }

        .vf-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(85,234,220,0.15);
            color: var(--mint);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .vf-text strong {
            display: block;
            color: var(--white);
            font-size: 13px;
            margin-bottom: 2px;
        }

        .vf-text small {
            color: var(--text-soft);
            font-size: 12px;
            line-height: 1.4;
        }

        /* ── PANEL DERECHO (formulario) ────────────── */
        .auth-panel {
            background: var(--dark-card);
            padding: 58px 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Línea decorativa superior fucsia */
        .auth-panel::before {
            content: "";
            position: absolute;
            top: 0; left: 56px; right: 56px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--fuchsia), transparent);
            border-radius: 2px;
        }

        .auth-card {
            width: 100%;
            max-width: 400px;
        }

        /* Encabezado del formulario */
        .form-header {
            margin-bottom: 36px;
        }

        .form-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,47,160,0.10);
            border: 1px solid rgba(255,47,160,0.25);
            color: var(--fuchsia);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            padding: 5px 13px;
            border-radius: 30px;
            margin-bottom: 18px;
        }

        .form-tag::before {
            content: "";
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--fuchsia);
            animation: pulse-dot 1.8s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        .form-header h2 {
            font-size: 34px;
            font-weight: 900;
            color: var(--white);
            line-height: 1.1;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
        }

        /* Alerta */
        .alert {
            margin-bottom: 24px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(255,47,160,0.08);
            border: 1px solid rgba(255,47,160,0.25);
            color: var(--text-soft);
            font-size: 14px;
        }

        .alert strong {
            display: block;
            color: var(--fuchsia);
            margin-bottom: 4px;
        }

        /* Campos */
        .form-group { margin-bottom: 20px; }

        .form-hint {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.45;
            color: var(--text-muted);
        }

        .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .label-row label,
        .form-group > label {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-soft);
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 10px;
        }

        .label-row label { margin-bottom: 0; }

        .forgot-link {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--fuchsia);
            opacity: 0.85;
            transition: opacity 0.2s;
        }

        .forgot-link:hover { opacity: 1; }

        .input-wrap {
            position: relative;
        }

        .input-wrap .ico {
            position: absolute;
            top: 50%; left: 16px;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            height: 54px;
            background: #2a2d3a;
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 12px;
            padding: 0 16px 0 46px;
            font-size: 15px;
            color: var(--white);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s;
        }

        .input-wrap input::placeholder { color: #8b96b0; }

        .input-wrap input:focus {
            border-color: var(--fuchsia);
            background: #313444;
            box-shadow: 0 0 0 3px rgba(255,47,160,0.15);
        }

        /* Checkbox */
        .check-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 6px 0 28px;
        }

        .check-row input[type="checkbox"] {
            width: 17px; height: 17px;
            accent-color: var(--fuchsia);
            cursor: pointer;
        }

        .check-row span {
            font-size: 13.5px;
            color: var(--text-muted);
        }

        /* Botón principal */
        .btn-login {
            width: 100%;
            height: 56px;
            border: none;
            border-radius: 14px;
            background: var(--fuchsia);
            color: var(--white);
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
            box-shadow: 0 4px 14px rgba(255, 47, 160, 0.25);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            background: #e0238e;
            box-shadow: 0 6px 20px rgba(255, 47, 160, 0.38);
        }

        .btn-login:active { transform: translateY(0); }

        /* Brillo deslizante al hover */
        .btn-login::after {
            content: "";
            position: absolute;
            top: 0; left: -75%;
            width: 50%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.22), transparent);
            transform: skewX(-20deg);
            transition: left 0.5s;
        }

        .btn-login:hover::after { left: 150%; }

        /* Registro */
        .register-box {
            margin-top: 22px;
            padding: 13px 18px;
            border-radius: 14px;
            background: var(--dark-card);
            border: 2px solid var(--fuchsia);
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
            box-shadow: 0 0 10px rgba(255, 47, 160, 0.20);
        }

        .register-box:hover {
            border-color: var(--mint);
            background: rgba(85, 234, 220, 0.07);
            box-shadow: 0 0 22px rgba(85, 234, 220, 0.30);
        }

        .register-box a {
            color: var(--fuchsia);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color 0.3s;
        }

        .register-box:hover a {
            color: var(--mint);
        }

        /* Volver */
        .home-link {
            display: block;
            margin-top: 18px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13.5px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .home-link:hover { color: var(--mint); }

        /* Divisor */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 26px 0;
            color: rgba(255,255,255,0.15);
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.08);
        }

        /* ── RESPONSIVE ──────────────────────────── */
        @media (max-width: 860px) {
            .auth-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }

            .auth-visual {
                min-height: 320px;
                padding: 36px 34px;
            }

            .visual-body h2 { font-size: 28px; }

            .auth-panel {
                padding: 44px 34px;
            }

            .auth-panel::before { left: 34px; right: 34px; }
        }

        @media (max-width: 480px) {
            .auth-panel { padding: 36px 24px; }
            .auth-panel::before { left: 24px; right: 24px; }
            .form-header h2 { font-size: 28px; }
        }
    </style>
</head>

<body>

<main>
    <section class="auth-wrapper">

        <!-- Panel izquierdo visual -->
        <aside class="auth-visual">
            <a href="../../public/index.php" class="brand">
                <img src="../../img/icono.png" alt="Logo FigueFit">
                <span>Figue<em>Fit</em></span>
            </a>

            <div class="visual-body">
                

                <h2>
                    Tu proceso fitness,<br>
                    <span>empieza aquí.</span>
                </h2>

                <p>
                    Entrena con propósito. Vive tu mejor versión con planes fitness personalizados,
                    seguimiento profesional y bienestar diseñado para ti.
                </p>
            </div>

            <div class="visual-footer">
                <div class="vf-icon">✓</div>
                <div class="vf-text">
                    <strong>Acceso seguro</strong>
                    <small>Ingreso protegido para administradores, coaches, clientes e instituciones.</small>
                </div>
            </div>
        </aside>

        <!-- Panel derecho formulario -->
        <section class="auth-panel">
            <div class="auth-card">

                <div class="form-header">
                    <h2>Iniciar sesión</h2>
                    <p>Ingresa tus credenciales para continuar en FigueFit.</p>
                </div>

                <?php if ($alert): ?>
                    <div class="alert">
                        <strong><?= e($alert['title'] ?? 'Aviso') ?></strong>
                        <?= e($alert['text'] ?? '') ?>
                    </div>
                <?php endif; ?>

                <form action="../../controllers/auth/loginController.php" method="POST">

                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <div class="input-wrap">
                            <span class="ico">✉</span>
                            <input
                                id="correo"
                                type="email"
                                name="correo"
                                placeholder="nombre@correo.com"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="label-row">
                            <label for="password">Contraseña</label>
                            <a href="recuperarPassword.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="input-wrap">
                            <span class="ico">🔒</span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="••••••••••"
                                autocomplete="current-password"
                                required
                            >
                        </div>
                        <p class="form-hint">Si te inscribiste antes de crear contraseña, usa tu número de identificación como contraseña.</p>
                    </div>

                    <div class="check-row">
                        <input type="checkbox" name="recordar" id="recordar">
                        <span>Recordar mi sesión en este equipo</span>
                    </div>

                    <button type="submit" class="btn-login">
                        Entrar al sistema →
                    </button>

                </form>

                <div class="register-box">
                    ¿Aún no tienes una cuenta?
                    <a href="register.php">Crear cuenta de cliente</a>
                </div>

                <a href="../../public/index.php" class="home-link">
                    ← Volver al inicio
                </a>

            </div>
        </section>

    </section>
</main>

</body>
</html>
