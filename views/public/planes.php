<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planes = $planes ?? []; // Planes disponibles
$programas = $programas ?? []; // Programas disponibles

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Planes | StayFit</title> <!-- Título -->

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
            background: #FFFFFF;
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
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            padding: 70px 8%;
            text-align: center;
        }

        .hero h1 {
            font-size: 44px;
            margin: 0 0 12px;
        }

        .hero p {
            max-width: 760px;
            margin: auto;
            line-height: 1.7;
        }

        .section {
            padding: 55px 8%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .section-title h2 {
            color: #D63384;
            font-size: 34px;
            margin-bottom: 10px;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }

        .plan-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 12px 32px rgba(45, 45, 45, 0.10);
            border-top: 6px solid #D63384;
        }

        .plan-card h3 {
            color: #D63384;
            font-size: 26px;
            margin-top: 0;
        }

        .price {
            font-size: 34px;
            font-weight: 900;
            margin: 12px 0;
            color: #2D2D2D;
        }

        .badge {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
            margin: 6px 6px 12px 0;
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
    <h1>Planes StayFit</h1>
    <p>Elige el plan que mejor se adapte a tu proceso: presencial, virtual o mixto, con acompañamiento profesional y trazabilidad completa.</p>
</section>

<section class="section">
    <div class="section-title">
        <h2>Planes disponibles</h2>
        <p>Todos los planes se activan después de validar el pago.</p>
    </div>

    <div class="plans-grid">

        <?php if (empty($planes)): ?>
            <div class="empty">Por ahora no hay planes disponibles.</div>
        <?php endif; ?>

        <?php foreach ($planes as $plan): ?>
            <article class="plan-card">
                <h3><?= e($plan['nombre'] ?? 'Plan StayFit') ?></h3>

                <p><?= e($plan['descripcion'] ?? 'Plan diseñado para acompañar tu proceso fitness.') ?></p>

                <p class="price">$<?= e($plan['precio'] ?? '0') ?></p>

                <span class="badge"><?= e($plan['modalidad'] ?? 'modalidad') ?></span>
                <span class="badge"><?= e($plan['duracion'] ?? '0') ?> días</span>

                <br>

                <a class="btn-primary" href="solicitud.php?plan_id=<?= e($plan['id_plan'] ?? $plan['id'] ?? '') ?>">
                    Solicitar este plan
                </a>
            </article>
        <?php endforeach; ?>

    </div>
</section>

</body>
</html>