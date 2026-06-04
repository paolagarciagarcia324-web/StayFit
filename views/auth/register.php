<?php

session_start();

$alert = $_SESSION['alert'] ?? null;
$old   = $_SESSION['old'] ?? [];
unset($_SESSION['alert'], $_SESSION['old']);

function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | FigueFit</title>
    <style>
        :root {
            --fuchsia:   #ff2fa0;
            --mint:      #55eadc;
            --mint-dark: #1fbfb2;
            --white:     #ffffff;
            --text-soft: #b8c0d4;
            --text-muted:#8b93a7;
            --card-dark: #171922;
            --input-bg:  #2a2d3a;
            --border:    rgba(255,255,255,0.14);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background:
                radial-gradient(ellipse at top left,     rgba(255,47,160,0.18) 0%, transparent 45%),
                radial-gradient(ellipse at bottom right, rgba(85,234,220,0.12) 0%, transparent 45%),
                linear-gradient(160deg, #1a0e1f 0%, #0d0d14 40%, #0a0b10 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        a { text-decoration: none; }

        /* ── WRAPPER ── */
        .auth-wrapper {
            width: min(100vw, 1080px);
            height: min(100vh, 700px);
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.45);
            border: 1px solid rgba(255,255,255,0.07);
        }

        /* ── PANEL IZQUIERDO ── */
        .auth-visual {
            position: relative;
            padding: 36px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background:
                linear-gradient(160deg, rgba(0,0,0,0.38) 0%, rgba(0,0,0,0.12) 100%),
                url("../../img/register.png") center/cover no-repeat;
        }

        .auth-visual::after {
            content: "";
            position: absolute; inset: 0;
            background:
                radial-gradient(circle at 80% 15%, rgba(255,47,160,0.22), transparent 32%),
                radial-gradient(circle at 10% 90%, rgba(85,234,220,0.16), transparent 32%);
            pointer-events: none;
        }

        .brand {
            position: relative; z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 900;
            color: var(--white);
        }

        .brand img {
            width: 52px; height: 52px;
            object-fit: contain;
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            padding: 5px;
        }

        .brand em { color: var(--fuchsia); font-style: normal; }

        .visual-body {
            position: relative; z-index: 1;
            margin-top: 30px;
        }

        .visual-body h1 {
            font-size: 30px;
            font-weight: 900;
            line-height: 1.15;
            margin-bottom: 12px;
        }

        .visual-body h1 span { color: var(--fuchsia); }

        .visual-body p {
            color: rgba(255,255,255,0.88);
            font-size: 14px;
            line-height: 1.65;
            max-width: 360px;
        }

        .secure-box {
            position: relative; z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            backdrop-filter: blur(12px);
        }

        .secure-icon {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: rgba(85,234,220,0.15);
            color: var(--mint);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .secure-box strong { display:block; color:var(--white); font-size:13px; margin-bottom:2px; }
        .secure-box small  { color:var(--text-soft); font-size:12px; line-height:1.4; }

        /* ── PANEL DERECHO ── */
        .auth-panel {
            background: var(--card-dark);
            padding: 40px 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-panel::before {
            content: "";
            position: absolute;
            top: 0; left: 48px; right: 48px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--fuchsia), transparent);
        }
        

        .auth-card { width: 100%; max-width: 420px; }

        .auth-card h2 {
            font-size: 26px;
            font-weight: 900;
            color: var(--white);
            margin-bottom: 4px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 24px;
        }

        /* Alerta */
        .alert {
            margin-bottom: 14px;
            padding: 10px 14px;
            border-radius: 10px;
            background: rgba(255,47,160,0.08);
            border: 1px solid rgba(255,47,160,0.25);
            color: var(--text-soft);
            font-size: 13px;
        }

        .alert strong { display:block; color:var(--fuchsia); margin-bottom:3px; }

        /* Grid de campos */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 20px;
        }

        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-soft);
            letter-spacing: 0.2px;
        }

        .input-wrap { position: relative; }

        .input-wrap .ico {
            position: absolute;
            top: 50%; left: 13px;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            height: 44px;
            background: var(--input-bg);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 0 12px 0 38px;
            font-size: 13.5px;
            color: var(--white);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-wrap input::placeholder { color: #4b5263; }

        .input-wrap input:focus {
            border-color: var(--fuchsia);
            box-shadow: 0 0 0 3px rgba(255,47,160,0.13);
        }

        /* Texto de ayuda */
        .helper-text {
            font-size: 11.5px;
            color: var(--text-muted);
            line-height: 1.5;
            margin: 20px 0 18px;
        }

        /* Botón crear cuenta */
        .btn-register {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            background: var(--fuchsia);
            color: var(--white);
            font-size: 14.5px;
            font-weight: 900;
            letter-spacing: 0.4px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
            box-shadow: 0 4px 14px rgba(255,47,160,0.25);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            background: #e0238e;
            box-shadow: 0 6px 20px rgba(255,47,160,0.38);
        }

        .btn-register::after {
            content: "";
            position: absolute;
            top: 0; left: -75%;
            width: 50%; height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.20), transparent);
            transform: skewX(-20deg);
            transition: left 0.5s;
        }

        .btn-register:hover::after { left: 150%; }

        /* Caja iniciar sesión */
        .login-box {
            margin-top: 18px;
            padding: 14px 18px;
            border-radius: 12px;
            background: var(--card-dark);
            border: 2px solid var(--fuchsia);
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
            white-space: nowrap;
            box-shadow: 0 0 10px rgba(255,47,160,0.15);
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
        }

        .login-box:hover {
            border-color: var(--mint);
            background: rgba(85,234,220,0.06);
            box-shadow: 0 0 20px rgba(85,234,220,0.25);
        }

        .login-box a {
            color: var(--fuchsia);
            font-weight: 900;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.4px;
            transition: color 0.3s;
        }

        .login-box:hover a { color: var(--mint); }

        .home-link {
            display: block;
            margin-top: 14px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12.5px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .home-link:hover { color: var(--mint); }

        /* ── RESPONSIVE ── */
        @media (max-width: 860px) {
            body { height: auto; overflow: auto; padding: 24px 16px; }
            .auth-wrapper { grid-template-columns: 1fr; height: auto; max-width: 480px; }
            .auth-visual { min-height: 260px; padding: 28px 28px; }
            .auth-panel  { padding: 28px 28px; }
            .auth-panel::before { left: 28px; right: 28px; }
        }

        @media (max-width: 480px) {
            .form-grid { grid-template-columns: 1fr; }
            .auth-panel { padding: 24px 20px; }
        }
    </style>
</head>
<body>

<section class="auth-wrapper">

    <!-- Panel izquierdo -->
    <aside class="auth-visual">
        <a href="../../public/index.php" class="brand">
            <img src="../../img/icono.png" alt="Logo FigueFit">
            <span>Figue<em>Fit</em></span>
        </a>

        <div class="visual-body">
            <h1>Tu cuenta fitness<br><span>empieza aquí.</span></h1>
            <p>Crea tu acceso para consultar planes, progreso, pagos, contenido virtual, agenda y acompañamiento dentro de la plataforma.</p>
        </div>

        <div class="secure-box">
            <div class="secure-icon">✓</div>
            <div>
                <strong>Registro seguro</strong>
                <small>Tus datos serán usados para crear tu cuenta en FigueFit.</small>
            </div>
        </div>
    </aside>

    <!-- Panel derecho -->
    <section class="auth-panel">
        <div class="auth-card">

            <h2>Crear cuenta</h2>
            <p class="subtitle">Completa tus datos para iniciar tu registro en FigueFit.</p>

            <?php if ($alert): ?>
                <div class="alert">
                    <strong><?= e($alert['title'] ?? 'Aviso') ?></strong>
                    <?= e($alert['text'] ?? '') ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/auth/registerController.php" method="POST">
                <div class="form-grid">

                    <div class="form-group full">
                        <label for="nombre_completo">Nombre completo</label>
                        <div class="input-wrap">
                            <span class="ico">👤</span>
                            <input id="nombre_completo" type="text" name="nombre_completo"
                                placeholder="Nombre y apellido"
                                value="<?= e($old['nombre_completo'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <div class="input-wrap">
                            <span class="ico">✉</span>
                            <input id="correo" type="email" name="correo"
                                placeholder="nombre@correo.com"
                                value="<?= e($old['correo'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <div class="input-wrap">
                            <span class="ico">☎</span>
                            <input id="telefono" type="text" name="telefono"
                                placeholder="Opcional"
                                value="<?= e($old['telefono'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group full">
                        <label for="documento_identidad">Documento de identidad</label>
                        <div class="input-wrap">
                            <span class="ico">🪪</span>
                            <input id="documento_identidad" type="text" name="documento_identidad"
                                placeholder="Número de documento"
                                value="<?= e($old['documento_identidad'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrap">
                            <span class="ico">🔒</span>
                            <input id="password" type="password" name="password"
                                placeholder="Mínimo 6 caracteres" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <div class="input-wrap">
                            <span class="ico">🔒</span>
                            <input id="password_confirm" type="password" name="password_confirm"
                                placeholder="Repite tu contraseña" required>
                        </div>
                    </div>

                </div>

                <p class="helper-text">
                    Tu cuenta se registrará como usuario interesado. El acceso a módulos dependerá de la compra y validación del plan.
                </p>

                <button type="submit" class="btn-register">Crear cuenta →</button>
            </form>

            <div class="login-box">
                ¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a>
            </div>

            <a href="../../public/index.php" class="home-link">← Volver al inicio</a>

        </div>
    </section>

</section>

</body>
</html>
