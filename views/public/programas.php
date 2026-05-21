<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$programas = $programas ?? []; // Programas disponibles

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Programas | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #FFFFFF;
            color: #2D2D2D;
        }

        .navbar {
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 6px 20px rgba(45, 45, 45, 0.06);
        }

        .brand {
            color: #D63384;
            font-size: 28px;
            font-weight: 900;
            text-decoration: none;
        }

        .nav a {
            color: #2D2D2D;
            text-decoration: none;
            margin-left: 18px;
            font-weight: 700;
        }

        .nav .btn {
            background: #D63384;
            color: #FFFFFF;
            padding: 10px 16px;
            border-radius: 14px;
        }

        .hero {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            padding: 70px 8%;
            text-align: center;
        }

        .hero h1 {
            font-size: 44px;
            margin: 0 0 12px;
        }

        .program-grid {
            padding: 55px 8%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }

        .program-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 12px 32px rgba(45, 45, 45, 0.10);
            border-left: 6px solid #3EB489;
        }

        .program-card h3 {
            color: #D63384;
            margin-top: 0;
            font-size: 25px;
        }

        .badge {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 10px;
        }

        .btn-primary {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 800;
            margin-top: 14px;
        }

        .empty {
            background: #f4f4f4;
            padding: 24px;
            border-radius: 20px;
            text-align: center;
            color: #777;
        }

        @media (max-width: 800px) {
            .navbar {
                flex-direction: column;
                gap: 16px;
            }

            .nav a {
                margin: 6px;
                display: inline-block;
            }

            .hero h1 {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="brand" href="index.php">StayFit</a>

    <nav class="nav">
        <a href="index.php">Inicio</a>
        <a href="planPublico.php">Planes</a>
        <a href="solicitud.php">Inscripción</a>
        <a class="btn" href="../views/auth/login.php">Ingresar</a>
    </nav>
</header>

<section class="hero">
    <h1>Programas virtuales</h1>
    <p>Entrenamientos pregrabados para avanzar a tu ritmo, con seguimiento y trazabilidad dentro del sistema.</p>
</section>

<section class="program-grid">

    <?php if (empty($programas)): ?>
        <div class="empty">Por ahora no hay programas virtuales disponibles.</div>
    <?php endif; ?>

    <?php foreach ($programas as $programa): ?>
        <article class="program-card">
            <h3><?= e($programa['nombre'] ?? 'Programa StayFit') ?></h3>

            <p><?= e($programa['descripcion'] ?? 'Programa virtual diseñado para entrenamiento progresivo.') ?></p>

            <p><strong>Duración:</strong> <?= e($programa['duracion'] ?? 'No definida') ?></p>

            <span class="badge"><?= e($programa['estado'] ?? 'activo') ?></span>

            <br>

            <a class="btn-primary" href="solicitud.php?programa_id=<?= e($programa['id'] ?? '') ?>">
                Solicitar programa
            </a>
        </article>
    <?php endforeach; ?>

</section>

</body>
</html>