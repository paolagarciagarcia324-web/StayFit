<?php
// Página principal pública de StayFit
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayFit | Plataforma Fitness</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="container header-content">

        <a href="index.php" class="logo">
            <img src="../img/logo.png" alt="Logo StayFit">
            <span>Stay<span>Fit</span></span>
        </a>

        <input type="checkbox" id="menu-toggle">

        <label for="menu-toggle" class="menu-icon">
            ☰
        </label>

        <nav class="navbar">
            <a href="#inicio">Inicio</a>
            <a href="#nosotros">Nosotros</a>
            <a href="#servicios">Servicios</a>
            <a href="#planes">Planes</a>
            <a href="#instituciones">Instituciones</a>
            <a href="#contacto">Contacto</a>
        </nav>

        <div class="social">
            <a href="#">f</a>
            <a href="#">x</a>
            <a href="#">ig</a>
        </div>

    </div>
</header>

<section class="hero" id="inicio">
    <div class="hero-overlay"></div>

    <div class="container hero-content">
        <h1>HAZ QUE <span>OCURRA</span></h1>

        <p>
            Entrenamiento, nutrición y bienestar diseñados para transformar tu proceso físico
            con acompañamiento, disciplina y motivación.
        </p>

        <a href="#planes" class="hero-button">
            Ver planes
        </a>
    </div>

    <div class="hero-categories">
        <span>01. Fitness</span>
        <span>02. Nutrición</span>
        <span>03. Programas</span>
        <span>04. Progreso</span>
        <span>05. Eventos</span>
        <span>06. Bienestar</span>
    </div>
</section>

<section class="about" id="nosotros">
    <div class="container about-content">

        <div class="about-image">
            <img src="../img/about-woman.png" alt="Mujer fitness StayFit">
        </div>

        <div class="about-text">
            <span class="section-number">01</span>
            <span class="section-subtitle">La mejor experiencia</span>

            <h2>Nosotros</h2>

            <p class="bold-text">
                StayFit es una plataforma fitness enfocada en entrenamiento, nutrición,
                progreso físico y bienestar.
            </p>

            <p>
                Nuestro propósito es acompañar principalmente a mujeres en su proceso de
                transformación física y personal, ofreciendo planes, programas virtuales,
                sesiones, eventos y seguimiento organizado.
            </p>

            <a href="#servicios" class="btn-secondary">
                Conocer servicios
            </a>
        </div>

    </div>
</section>

<section class="services" id="servicios">
    <div class="container">
        <div class="section-title">
            <span>02</span>
            <h2>Servicios</h2>
            <p>Todo lo que necesitas para avanzar en tu proceso fitness.</p>
        </div>

        <div class="services-grid">
            <article class="service-card">
                <h3>Entrenamiento</h3>
                <p>Rutinas, ejercicios, materiales y seguimiento según tu plan activo.</p>
            </article>

            <article class="service-card">
                <h3>Nutrición</h3>
                <p>Planes nutricionales, recomendaciones y acompañamiento alimenticio.</p>
            </article>

            <article class="service-card">
                <h3>Programas virtuales</h3>
                <p>Acceso a videos, contenido digital y progreso por programa.</p>
            </article>

            <article class="service-card">
                <h3>Progreso</h3>
                <p>Registro de peso, medidas, fotos y observaciones del coach.</p>
            </article>
        </div>
    </div>
</section>

<section class="plans" id="planes">
    <div class="container">
        <div class="section-title light">
            <span>03</span>
            <h2>Planes y programas</h2>
            <p>Elige tu plan, valida cupo y empieza tu proceso con StayFit.</p>
        </div>

        <div class="plans-grid">
            <article class="plan-card">
                <h3>Plan Virtual</h3>
                <p>Entrenamiento con videos, seguimiento básico y acceso a contenido digital.</p>
                <strong>$80.000</strong>
                <a href="#" class="btn-primary">Comprar plan</a>
            </article>

            <article class="plan-card featured">
                <h3>Plan Integral</h3>
                <p>Entrenamiento, nutrición, progreso, sesiones y acompañamiento personalizado.</p>
                <strong>$150.000</strong>
                <a href="#" class="btn-primary">Comprar plan</a>
            </article>

            <article class="plan-card">
                <h3>Plan Institucional</h3>
                <p>Programas grupales, eventos, talleres y acompañamiento para instituciones.</p>
                <strong>Consultar</strong>
                <a href="#" class="btn-primary">Solicitar información</a>
            </article>
        </div>
    </div>
</section>

<section class="institutions" id="instituciones">
    <div class="container institutions-content">
        <div>
            <span class="section-number">04</span>
            <h2>Instituciones y eventos</h2>
            <p>
                StayFit también permite gestionar eventos, talleres, sesiones grupales
                y programas para instituciones, manteniendo control de cupos,
                participantes y seguimiento.
            </p>
        </div>

        <a href="#contacto" class="btn-secondary">
            Contactar
        </a>
    </div>
</section>

<section class="contact" id="contacto">
    <div class="container">
        <div class="section-title">
            <span>05</span>
            <h2>Contacto</h2>
            <p>Inicia tu proceso con StayFit y transforma tu bienestar.</p>
        </div>

        <form class="contact-form">
            <input type="text" placeholder="Nombre completo">
            <input type="email" placeholder="Correo electrónico">
            <input type="text" placeholder="Teléfono">
            <textarea placeholder="Mensaje"></textarea>

            <button type="submit">
                Enviar mensaje
            </button>
        </form>
    </div>
</section>

<footer class="footer">
    <p>© <?php echo date('Y'); ?> StayFit. Todos los derechos reservados.</p>
</footer>

</body>
</html>