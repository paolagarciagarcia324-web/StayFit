<?php

session_start(); // Inicia sesión

$alert = $_SESSION['alert'] ?? null; // Obtiene alerta
$token = $_SESSION['token_recuperacion'] ?? null; // Obtiene token temporal
unset($_SESSION['alert']); // Limpia alerta

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Recuperar contraseña | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #2D2D2D;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #FFFFFF;
            width: 100%;
            max-width: 450px;
            padding: 36px;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(45, 45, 45, 0.25);
        }

        .brand {
            color: #D63384;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
        }

        p {
            color: #666;
            line-height: 1.6;
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
            box-sizing: border-box;
            font-size: 15px;
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

        .btn-green {
            background: #3EB489;
            margin-top: 8px;
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

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #D63384;
            text-decoration: none;
            font-weight: 700;
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 26px 0;
        }
    </style>
</head>

<body>

    <section class="card">
        <div class="brand">StayFit</div>

        <h1>Recuperar contraseña</h1>
        <p>Ingresa tu correo para iniciar el proceso de recuperación de acceso.</p>

        <?php if ($alert): ?>
            <div class="alert">
                <strong><?= htmlspecialchars($alert['title'] ?? 'Aviso', ENT_QUOTES, 'UTF-8') ?></strong>
                <?= htmlspecialchars($alert['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="../../controllers/auth/recuperacion_passwordController.php" method="POST">
            <label>Correo electrónico</label>
            <input type="email" name="correo" placeholder="ejemplo@correo.com" required>

            <button type="submit">Solicitar recuperación</button>
        </form>

        <?php if ($token): ?>
            <div class="divider"></div>

            <h1>Nueva contraseña</h1>
            <p>Proceso temporal para actualizar la contraseña desde el sistema.</p>

            <form action="../../controllers/auth/recuperacion_passwordController.php?accion=cambiar" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <label>Nueva contraseña</label>
                <input type="password" name="password" placeholder="Nueva contraseña" required>

                <button class="btn-green" type="submit">Actualizar contraseña</button>
            </form>
        <?php endif; ?>

        <div class="links">
            <a href="login.php">Volver al login</a>
        </div>
    </section>

</body>
</html>