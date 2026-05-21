<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$tituloPagina = $tituloPagina ?? 'Acceso | StayFit'; // Título por defecto
$contenido = $contenido ?? ''; // Contenido de la vista auth
$alert = $_SESSION['alert'] ?? null; // Alerta de sesión
unset($_SESSION['alert']); // Limpia alerta

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title><?= e($tituloPagina) ?></title> <!-- Título dinámico -->

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            background: #f7f7f7;
            color: #2D2D2D;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }

        .auth-hero {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            padding: 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-hero span {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 14px;
            border-radius: 20px;
            margin-bottom: 20px;
            font-weight: 700;
            width: fit-content;
        }

        .auth-hero h1 {
            font-size: 46px;
            line-height: 1.1;
            margin: 0 0 18px;
        }

        .auth-hero p {
            max-width: 560px;
            font-size: 18px;
            line-height: 1.6;
        }

        .auth-content {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px;
        }

        .auth-card {
            background: #FFFFFF;
            width: 100%;
            max-width: 430px;
            padding: 36px;
            border-radius: 24px;
            box-shadow: 0 14px 35px rgba(45, 45, 45, 0.12);
        }

        .brand {
            color: #D63384;
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 8px;
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

        input,
        button {
            width: 100%;
            padding: 13px;
            border-radius: 14px;
            font-size: 15px;
        }

        input {
            border: 1px solid #ddd;
            margin: 8px 0 18px;
        }

        button {
            background: #D63384;
            color: #FFFFFF;
            border: none;
            font-weight: 800;
            cursor: pointer;
        }

        button:hover {
            background: #b92b70;
        }

        a {
            color: #D63384;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 900px) {
            body {
                grid-template-columns: 1fr;
            }

            .auth-hero {
                padding: 42px 28px;
            }

            .auth-hero h1 {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>

<section class="auth-hero">
    <span>Fitness femenino profesional</span>
    <h1>StayFit</h1>
    <p>Accede a tu panel para gestionar entrenamiento, nutrición, progreso, pagos y acompañamiento.</p>
</section>

<section class="auth-content">
    <div class="auth-card">

        <div class="brand">StayFit</div>

        <?php if ($alert): ?>
            <div class="alert">
                <strong><?= e($alert['title'] ?? 'Aviso') ?></strong>
                <?= e($alert['text'] ?? '') ?>
            </div>
        <?php endif; ?>

        <?= $contenido ?>

    </div>
</section>

</body>
</html>