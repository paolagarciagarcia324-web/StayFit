<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$tituloPagina = $tituloPagina ?? 'StayFit | Fitness femenino'; // Título por defecto
$contenido = $contenido ?? ''; // Contenido público
$vistaActiva = $vistaActiva ?? 'inicio'; // Vista activa

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
            background: #FFFFFF;
            color: #2D2D2D;
        }

        .navbar {
            background: #FFFFFF;
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 6px 20px rgba(45, 45, 45, 0.06);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .brand {
            color: #D63384;
            font-size: 28px;
            font-weight: 900;
            text-decoration: none;
        }

        .menu {
            display: flex;
            gap: 18px;
            align-items: center;
        }

        .menu a {
            color: #2D2D2D;
            text-decoration: none;
            font-weight: 700;
            padding: 9px 12px;
            border-radius: 12px;
        }

        .menu a:hover,
        .menu a.active {
            background: #fff1f7;
            color: #D63384;
        }

        .btn-login {
            background: #D63384 !important;
            color: #FFFFFF !important;
        }

        .public-content {
            min-height: calc(100vh - 180px);
        }

        .footer {
            background: #2D2D2D;
            color: #FFFFFF;
            padding: 34px 8%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .footer h3 {
            color: #D63384;
            margin-top: 0;
        }

        .footer a {
            color: #FFFFFF;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
        }

        .footer-bottom {
            background: #242424;
            color: #FFFFFF;
            text-align: center;
            padding: 14px;
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .navbar {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .menu {
                flex-wrap: wrap;
            }

            .footer {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="brand" href="../../public/index.php">StayFit</a>

    <nav class="menu">
        <a class="<?= $vistaActiva === 'inicio' ? 'active' : '' ?>" href="../../public/index.php">Inicio</a>
        <a class="<?= $vistaActiva === 'planes' ? 'active' : '' ?>" href="../../public/planPublico.php">Planes</a>
        <a class="<?= $vistaActiva === 'solicitud' ? 'active' : '' ?>" href="../../public/solicitud.php">Inscripción</a>
        <a class="btn-login" href="../../views/auth/login.php">Ingresar</a>
    </nav>
</header>

<main class="public-content">
    <?= $contenido ?>
</main>

<footer class="footer">
    <div>
        <h3>StayFit</h3>
        <p>Entrenamiento, nutrición, progreso y acompañamiento para mujeres que buscan un proceso organizado y profesional.</p>
    </div>

    <div>
        <h3>Accesos rápidos</h3>
        <a href="../../public/planPublico.php">Ver planes</a>
        <a href="../../public/solicitud.php">Enviar solicitud</a>
        <a href="../../views/auth/login.php">Iniciar sesión</a>
    </div>
</footer>

<div class="footer-bottom">
    © <?= date('Y') ?> StayFit. Todos los derechos reservados.
</div>

</body>
</html>