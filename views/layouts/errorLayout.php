<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$tituloPagina = $tituloPagina ?? 'Error | StayFit'; // Título por defecto
$codigoError = $codigoError ?? '404'; // Código del error
$tituloError = $tituloError ?? 'Página no encontrada'; // Título del error
$mensajeError = $mensajeError ?? 'La página que intentas abrir no existe o no está disponible.'; // Mensaje
$rutaRegreso = $rutaRegreso ?? '../../views/auth/login.php'; // Ruta de regreso

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title><?= e($tituloPagina) ?></title> <!-- Título dinámico -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #2D2D2D;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-card {
            width: 100%;
            max-width: 520px;
            background: #FFFFFF;
            border-radius: 26px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 18px 45px rgba(45, 45, 45, 0.25);
        }

        .code {
            font-size: 76px;
            font-weight: 900;
            color: #D63384;
            margin: 0;
            line-height: 1;
        }

        h1 {
            margin: 16px 0 10px;
            font-size: 30px;
            color: #2D2D2D;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 26px;
        }

        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: 800;
        }

        .btn:hover {
            background: #b92b70;
        }

        .brand {
            color: #D63384;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 18px;
        }
    </style>
</head>

<body>

<section class="error-card">
    <div class="brand">StayFit</div>

    <p class="code"><?= e($codigoError) ?></p>

    <h1><?= e($tituloError) ?></h1>

    <p><?= e($mensajeError) ?></p>

    <a class="btn" href="<?= e($rutaRegreso) ?>">Volver</a>
</section>

</body>
</html>