<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planes = $planes ?? []; // Planes activos
$programas = $programas ?? []; // Programas activos

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>StayFit | Inicio</title> <!-- Título -->

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
            background: #FFFFFF;
            box-shadow: 0 6px 20px rgba(45, 45, 45, 0.06);
        }

        .brand {
            color: #D63384;
            font-size: 30px;
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
            padding: 80px 8%;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 40px;
            align-items: center;
            background: linear-gradient(135deg, #fff7fb, #FFFFFF);
        }

        .hero h1 {
            font-size: 52px;
            line-height: 1.1;
            margin: 0 0 18px;
            color: #2D2D2D;
        }

        .hero h1 span {
            color: #D63384;
        }

        .hero p {
            font-size: 18px;
            line-height: 1.7;
            color: #555;
        }

        .hero-card {
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            padding: 34px;
            border-radius: 28px;
            box-shadow: 0 18px 45px rgba(45, 45, 45, 0.20);
        }

        .hero-card h3 {
            font-size: 28px;
            margin-top: 0;
        }

        .btn-primary {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 14px;
            font-weight: 900;
            margin-top: 16px;
        }

        .btn-secondary {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 14px;
            font-weight: 900;
            margin-top: 16px;
            margin-left: 10px;
        }

        .section {
            padding: 60px 8%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 36px;
        }

        .section-title h2 {
            color: #D63384;
            font-size: 36px;
            margin-bottom: 10px;
        }

        .benefits {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .benefit-card,
        .plan-card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 26px;
            box-shadow: 0 12px 32px rgba(45, 45, 45, 0.10);
        }

        .benefit-card h3,
        .plan-card h3 {
            color: #D63384;
            margin-top: 0;
        }

        .plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }

        .plan-card {
            border-top: 6px solid #D63384;
        }

        .price {
            font-size: 30px;
            font-weight: 900;
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

        .cta {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            text-align: center;
            padding: 65px 8%;
        }

        .cta h2 {
            font-size: 38px;
            margin-top: 0;
        }

        .footer {
            background: #2D2D2D;
            color: #FFFFFF;
            text-align: center;
            padding: 24px;
        }

        @media (max-width: 900px) {
            .navbar {
                flex-direction: column;
                gap: 16px;
            }

            .nav a {
                margin: 6px;
                display: inline-block;
            }

            .hero {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 38px;
            }

            .btn-secondary {
                margin-left: 0;
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
    <div>
        <h1>Entrena con propósito, <span>progresa con control.</span></h1>

        <p>
            StayFit es una plataforma fitness para mujeres donde puedes acceder a planes,
            programas virtuales, seguimiento de progreso, nutrición, pagos y acompañamiento profesional.
        </p>

        <a class="btn-primary" href="planPublico.php">Ver planes</a>
        <a class="btn-secondary" href="solicitud.php">Quiero inscribirme</a>
    </div>

    <div class="hero-card">
        <h3>Tu proceso en un solo lugar</h3>
        <p>Entrenamiento presencial, virtual o mixto.</p>
        <p>Contenido pregrabado, coaches, progreso, nutrición y trazabilidad.</p>
        <p>El acceso se activa después de validar tu pago.</p>
    </div>
</section>

<section class="section">
    <div class="section-title">
        <h2>¿Qué ofrece StayFit?</h2>
        <p>Una experiencia clara, organizada y profesional para tu proceso fitness.</p>
    </div>

    <div class="benefits">
        <div class="benefit-card">
            <h3>Entrenamiento</h3>
            <p>Rutinas, sesiones y programas según tu objetivo y modalidad.</p>
        </div>

        <div class="benefit-card">
            <h3>Nutrición</h3>
            <p>Planes nutricionales y comidas asignadas para acompañar tu progreso.</p>
        </div>

        <div class="benefit-card">
            <h3>Seguimiento</h3>
            <p>Registro de avances físicos, observaciones y trazabilidad del proceso.</p>
        </div>

        <div class="benefit-card">
            <h3>Modalidad virtual</h3>
            <p>Videos pregrabados asignados por plan para entrenar a tu ritmo.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-title">
        <h2>Planes destacados</h2>
        <p>Selecciona un plan y envía tu solicitud con comprobante de pago.</p>
    </div>

    <div class="plans">
        <?php if (empty($planes)): ?>
            <div class="benefit-card">No hay planes disponibles por ahora.</div>
        <?php endif; ?>

        <?php foreach (array_slice($planes, 0, 3) as $plan): ?>
            <article class="plan-card">
                <h3><?= e($plan['nombre'] ?? 'Plan StayFit') ?></h3>
                <p><?= e($plan['descripcion'] ?? 'Plan fitness personalizado.') ?></p>
                <p class="price">$<?= e($plan['precio'] ?? '0') ?></p>
                <span class="badge"><?= e($plan['duracion_dias'] ? $plan['duracion_dias'] . ' días' : 'Plan') ?></span>
                <br>
                <a class="btn-primary" href="solicitud.php?plan_id=<?= e($plan['id_plan'] ?? '') ?>">Solicitar</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="cta">
    <h2>Empieza tu proceso StayFit</h2>
    <p>Llena el formulario, adjunta tu comprobante y espera la validación del administrador.</p>
    <a class="btn-secondary" href="solicitud.php">Enviar solicitud</a>
</section>

<footer class="footer">
    © <?= date('Y') ?> StayFit. Todos los derechos reservados.
</footer>

</body>
</html>