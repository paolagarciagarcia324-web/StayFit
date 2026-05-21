<?php

session_start(); // Inicia sesión

$alert = $_SESSION['alert'] ?? null; // Obtiene alerta
unset($_SESSION['alert']); // Limpia alerta

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Login | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }

        .hero {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            padding: 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero h1 {
            font-size: 46px;
            margin: 0 0 18px;
            line-height: 1.1;
        }

        .hero p {
            font-size: 18px;
            max-width: 560px;
            line-height: 1.6;
        }

        .tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 14px;
            border-radius: 20px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .login-area {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px;
        }

        .card {
            background: #FFFFFF;
            width: 100%;
            max-width: 420px;
            padding: 34px;
            border-radius: 24px;
            box-shadow: 0 14px 35px rgba(45, 45, 45, 0.12);
        }

        .brand {
            color: #D63384;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        h2 {
            margin: 0 0 8px;
            color: #2D2D2D;
        }

        .subtitle {
            color: #666;
            margin-bottom: 26px;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 14px;
            margin-bottom: 18px;
            font-size: 15px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #b92b70;
        }

        .links {
            margin-top: 20px;
            text-align: center;
        }

        .links a {
            color: #D63384;
            text-decoration: none;
            font-weight: 700;
        }

        .alert {
            background: #fff1f7;
            border-left: 5px solid #D63384;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
        }

        .alert strong {
            display: block;
            color: #D63384;
            margin-bottom: 4px;
        }

        @media (max-width: 900px) {
            body {
                grid-template-columns: 1fr;
            }

            .hero {
                padding: 42px 28px;
            }

            .hero h1 {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>

    <section class="hero">
        <span class="tag">Fitness femenino profesional</span>
        <h1>Entrena, progresa y mantén el control de tu proceso.</h1>
        <p>
            StayFit conecta clientas, coaches, planes, nutrición, contenido virtual,
            pagos y trazabilidad en una sola plataforma.
        </p>
    </section>

    <section class="login-area">
        <div class="card">

            <div class="brand">StayFit</div>
            <h2>Iniciar sesión</h2>
            <p class="subtitle">Ingresa con tus credenciales para acceder a tu panel.</p>

            <?php if ($alert): ?>
                <div class="alert">
                    <strong><?= htmlspecialchars($alert['title'] ?? 'Aviso', ENT_QUOTES, 'UTF-8') ?></strong>
                    <?= htmlspecialchars($alert['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="../../controller/auth/loginController.php" method="POST">
                <label>Correo electrónico</label>
                <input type="email" name="correo" placeholder="ejemplo@correo.com" required>

                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Ingresa tu contraseña" required>

                <button type="submit">Entrar al sistema</button>
            </form>

            <div class="links">
                <a href="recuperarPassword.php">¿Olvidaste tu contraseña?</a>
                <br><br>
                <a href="../../public/index.php">Volver al inicio</a>
            </div>

        </div>
    </section>

</body>
</html>
