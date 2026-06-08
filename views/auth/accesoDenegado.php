<?php

session_start(); // Inicia sesión

$rol = $_SESSION['rol'] ?? 'sin rol'; // Obtiene rol actual

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Acceso denegado | StayFit</title> <!-- Título -->

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
            max-width: 460px;
            padding: 38px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 18px 45px rgba(45, 45, 45, 0.25);
        }

        .icon {
            width: 78px;
            height: 78px;
            background: #D63384;
            color: #FFFFFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 34px;
            font-weight: 800;
        }

        h1 {
            margin: 0 0 12px;
            color: #D63384;
            font-size: 30px;
        }

        p {
            line-height: 1.6;
            color: #555;
        }

        .btn {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: 700;
            margin-top: 18px;
        }

        .btn-dark {
            background: #2D2D2D;
            margin-left: 8px;
        }
    </style>
</head>

<body>

    <section class="card">
        <div class="icon">!</div>

        <h1>Acceso denegado</h1>

        <p>No tienes permisos para ingresar a este módulo.</p>

        <p>
            Rol actual:
            <strong><?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>

        <a class="btn" href="../../views/auth/login.php">Volver al login</a>
        <a class="btn btn-dark" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
    </section>

</body>
</html>