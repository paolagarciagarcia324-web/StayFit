<?php
// Página principal pública de StayFit
$whatsappNumero = '573213642994'; // 57 + 3213642994
$whatsappMensaje = 'Hola, me interesa FigueFit';
$whatsappUrl = 'https://api.whatsapp.com/send?phone=' . $whatsappNumero . '&text=' . rawurlencode($whatsappMensaje);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FigueFit | Plataforma Fitness </title>

    <link rel="stylesheet" href="style.css?v=31">
</head>
<body class="page-home">

<header class="header">
    <div class="container header-content">

        <a href="index.php" class="logo">
            <img src="../img/Logo.png" alt="Logo FigueFit">
            <span>Figue<span>Fit</span></span>
        </a>

        <input type="checkbox" id="menu-toggle">

        <label for="menu-toggle" class="menu-icon">
           
        </label>

        <nav class="navbar">
            <a href="#inicio">Inicio</a>
            <a href="#servicios">Servicios</a>
            <a href="planPublico.php">Planes</a>
            <a href="#instituciones">Instituciones</a>
            <a href="#contacto">Contacto</a>
        </nav>

        <div class="header-actions">
    <a class="login-link" href="../views/auth/login.php">Iniciar sesión</a>
    <a class="register-link" href="../views/auth/register.php">Crear cuenta</a>
</div>

    </div>
</header>

<section class="hero" id="inicio">
    <div class="hero-slider">
        <div class="slide active">
            <img src="../img/extra/heroo1.png" alt="Slider 1">
        </div>
        <div class="slide">
            <img src="../img/extra/slider4.png" alt="Slider 4">
        </div>
        <div class="slide">
            <img src="../img/extra/slider6.png" alt="Slider 6">
        </div>
    </div>

    <div class="hero-overlay"></div>

    <div class="container hero-content">
         <h2>Plataforma fitness</h2>
        <h1 class="hero-title">
    <span class="text-mint">Rompe Tus</span> 
    <span class="text-fuchsia">Límites</span>
</h1>
        <p>
            <h2>Todo en un solo lugar</h2>
        </p>
    </div>

</section>

<nav class="section-dock" id="categoryNav" aria-label="Navegación por secciones">
    <div class="section-dock-track">
        <a href="#nosotros" class="section-dock-link is-active" data-target="#nosotros">
            <span class="dock-num">01</span><span class="dock-label">Nosotros</span>
        </a>
        <a href="#servicios" class="section-dock-link" data-target="#servicios">
            <span class="dock-num">02</span><span class="dock-label">Servicios</span>
        </a>
        <a href="#programas" class="section-dock-link" data-target="#programas">
            <span class="dock-num">03</span><span class="dock-label">Planes</span>
        </a>
        <a href="#instituciones" class="section-dock-link" data-target="#instituciones">
            <span class="dock-num">04</span><span class="dock-label">Instituciones</span>
        </a>
        <a href="#contacto" class="section-dock-link" data-target="#contacto">
            <span class="dock-num">05</span><span class="dock-label">Contacto</span>
        </a>
        <span class="section-dock-indicator" id="dockIndicator" aria-hidden="true"></span>
    </div>
</nav>

<script>
    const slides = document.querySelectorAll('.hero-slider .slide');
    let current = 0;

    if (slides.length > 0) {
        setInterval(() => {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 2500);
    }
</script>

<section class="about" id="nosotros">
    <div class="about-glow about-glow--left" aria-hidden="true"></div>
    <div class="about-glow about-glow--right" aria-hidden="true"></div>

    <div class="container">
        <div class="about-panel">
            <div class="about-content">

                <div class="about-image">
                    <img src="../img/extra/slider4.png" alt="Equipo fitness FigueFit">
                </div>

                <div class="about-text">
                    <div class="about-meta">
                        <span class="section-number">01</span>
                        <span class="section-subtitle">La mejor experiencia</span>
                    </div>

                    <h2>Nosotros</h2>

                    <p class="bold-text">
                        FigueFit es una plataforma fitness enfocada en entrenamiento, nutrición,
                        progreso físico y bienestar.
                    </p>

                    <p class="about-desc">
                        Nuestro propósito es acompañar principalmente a mujeres en su proceso de
                        transformación física y personal, ofreciendo planes, programas virtuales,
                        sesiones, eventos y seguimiento organizado.
                    </p>

                    <a href="#servicios" class="btn-about">
                        Conocer servicios →
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

<section class="services" id="servicios">
    <div class="services-glow services-glow--left" aria-hidden="true"></div>
    <div class="services-glow services-glow--right" aria-hidden="true"></div>

    <div class="container">
        <div class="services-header">
            <div class="services-meta">
                <span class="services-num">02</span>
                <span class="services-kicker">Tu proceso completo</span>
            </div>
            <h2>Servicios</h2>
            <p class="services-lead">Todo lo que necesitas para avanzar en tu proceso fitness.</p>
        </div>

        <div class="services-grid">
            <article class="service-card" data-animate style="--i: 0">
                <div class="service-card-media">
                    <img src="../img/Entrenamiento.png" alt="Entrenamiento FigueFit">
                </div>
                <div class="service-card-body">
                    <h3>Entrenamiento</h3>
                    <p>Rutinas, ejercicios, materiales y seguimiento según tu plan activo.</p>
                </div>
            </article>

            <article class="service-card" data-animate style="--i: 1">
                <div class="service-card-media">
                    <img src="../img/nutricion.png" alt="Nutrición FigueFit">
                </div>
                <div class="service-card-body">
                    <h3>Nutrición</h3>
                    <p>Planes nutricionales, recomendaciones y acompañamiento alimenticio.</p>
                </div>
            </article>

            <article class="service-card" data-animate style="--i: 2">
                <div class="service-card-media">
                    <img src="../img/programas-virtuales.png" alt="Programas virtuales FigueFit">
                </div>
                <div class="service-card-body">
                    <h3>Programas virtuales</h3>
                    <p>Acceso a videos, contenido digital y progreso por programa.</p>
                </div>
            </article>

            <article class="service-card" data-animate style="--i: 3">
                <div class="service-card-media">
                    <img src="../img/pogreso.png" alt="Progreso FigueFit">
                </div>
                <div class="service-card-body">
                    <h3>Progreso</h3>
                    <p>Registro de peso, medidas, fotos y observaciones del coach.</p>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="plans plans-premium" id="programas">
    <div class="plans-glow plans-glow--left" aria-hidden="true"></div>
    <div class="plans-glow plans-glow--right" aria-hidden="true"></div>

    <div class="container">
        <header class="plans-header">
            <div class="plans-meta">
                <span class="plans-num">03</span>
                <span class="plans-kicker">Membresías FigueFit</span>
            </div>
            <h2>Planes y programas</h2>
            <p class="plans-lead">Elige tu plan, valida cupo y empieza tu proceso con FigueFit.</p>
        </header>

        <div class="plans-grid plans-grid--catalog">
            <article class="plan-card-premium plan-card-premium--activate" data-animate style="--i: 0">
                <span class="plan-tier plan-tier--activate">Actívate</span>
                <div class="plan-icon-wrap plan-icon-wrap--sm" aria-hidden="true">
                    <svg class="plan-icon" viewBox="0 0 48 48" fill="none"><circle cx="18" cy="20" r="5" stroke="currentColor" stroke-width="1.4"/><circle cx="30" cy="20" r="5" stroke="currentColor" stroke-width="1.4"/><path d="M12 32c2-4 6-6 12-6s10 2 12 6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                </div>
                <h3>Plan Básico</h3>
                <p class="plan-tagline">Actívate con entrenamiento grupal</p>
                <ul class="plan-features plan-features--compact">
                    <li><span>Entrenamiento grupal</span></li>
                </ul>
                <div class="plan-price plan-price--dual">
                    <span class="plan-price-label">Inversión</span>
                    <div class="plan-price-rows">
                        <div class="plan-price-row"><span>Presencial</span><strong>$80.000</strong></div>
                        <div class="plan-price-row"><span>Virtual</span><strong>$60.000</strong></div>
                    </div>
                </div>
                <a href="solicitud.php" class="plan-cta">Elegir plan</a>
            </article>

            <article class="plan-card-premium plan-card-premium--evolution is-featured" data-animate style="--i: 1">
                <span class="plan-badge">Recomendado</span>
                <span class="plan-tier plan-tier--evolution">Transforma</span>
                <div class="plan-icon-wrap plan-icon-wrap--sm" aria-hidden="true">
                    <svg class="plan-icon" viewBox="0 0 48 48" fill="none"><path d="M24 8v28M16 20l8-8 8 8M16 36h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3>Evolución</h3>
                <p class="plan-tagline">Proceso guiado con seguimiento mensual</p>
                <ul class="plan-features plan-features--compact plan-features--split">
                    <li><span>Entrenamiento grupal</span><em>$80.000</em></li>
                    <li><span>Valoración inicial</span><em>$50.000</em></li>
                    <li><span>Test físico</span><em>$20.000</em></li>
                    <li><span>Seguimiento mensual</span><em>$40.000</em></li>
                </ul>
                <p class="plan-value-real">Valor real <s>$190.000</s></p>
                <div class="plan-price plan-price--dual">
                    <span class="plan-price-label">Inversión</span>
                    <div class="plan-price-rows">
                        <div class="plan-price-row"><span>Presencial</span><strong>$120.000</strong></div>
                        <div class="plan-price-row"><span>Virtual</span><strong>$100.000</strong></div>
                    </div>
                </div>
                <a href="solicitud.php" class="plan-cta plan-cta--featured">Elegir plan</a>
            </article>

            <article class="plan-card-premium plan-card-premium--premium" data-animate style="--i: 2">
                <span class="plan-tier plan-tier--premium">Premium</span>
                <div class="plan-icon-wrap plan-icon-wrap--sm" aria-hidden="true">
                    <svg class="plan-icon" viewBox="0 0 48 48" fill="none"><path d="M24 6l4.2 8.5 9.4 1.4-6.8 6.6 1.6 9.3L24 27.8l-8.4 4.4 1.6-9.3-6.8-6.6 9.4-1.4L24 6z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                </div>
                <h3>Plan Premium</h3>
                <p class="plan-tagline">Nutrición y seguimiento quincenal</p>
                <ul class="plan-features plan-features--compact plan-features--split">
                    <li><span>Entrenamiento grupal</span><em>$80.000</em></li>
                    <li><span>Valoración inicial</span><em>$50.000</em></li>
                    <li><span>Test físico</span><em>$20.000</em></li>
                    <li><span>Plan alimentación</span><em>$80.000</em></li>
                    <li><span>Seguimiento quincenal</span><em>$70.000</em></li>
                </ul>
                <p class="plan-value-real">Valor real <s>$300.000</s></p>
                <div class="plan-price plan-price--dual">
                    <span class="plan-price-label">Inversión</span>
                    <div class="plan-price-rows">
                        <div class="plan-price-row"><span>Presencial</span><strong>$170.000</strong></div>
                        <div class="plan-price-row"><span>Virtual</span><strong>$150.000</strong></div>
                    </div>
                </div>
                <a href="solicitud.php" class="plan-cta">Elegir plan</a>
            </article>

            <article class="plan-card-premium plan-card-premium--unlimited" data-animate style="--i: 3">
                <span class="plan-tier plan-tier--unlimited">Sin límites</span>
                <div class="plan-unlimited-layout">
                    <div class="plan-unlimited-head">
                        <div class="plan-icon-wrap plan-icon-wrap--sm" aria-hidden="true">
                            <svg class="plan-icon" viewBox="0 0 48 48" fill="none"><path d="M14 38V22l10-10 10 10v16" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M20 38v-8h8v8" stroke="currentColor" stroke-width="1.5"/></svg>
                        </div>
                        <div>
                            <h3>Plan Sin Límites</h3>
                            <p class="plan-tagline">Entrenamiento personalizado y acompañamiento integral</p>
                            <p class="plan-value-real plan-value-real--inline">Valor real <s>$620.000 – $820.000</s></p>
                        </div>
                    </div>
                    <ul class="plan-features plan-features--compact plan-features--split plan-features--cols">
                        <li><span>Entrenamiento 3× semana (12 ses.)</span><em>$300.000</em></li>
                        <li><span>Entrenamiento 4–5× semana</span><em>$500.000</em></li>
                        <li><span>Valoración</span><em>$50.000</em></li>
                        <li><span>Test físico</span><em>$20.000</em></li>
                        <li><span>Nutrición personalizada</span><em>$80.000</em></li>
                        <li><span>Seguimiento quincenal</span><em>$70.000</em></li>
                        <li><span>Monitoreo</span><em>$40.000</em></li>
                        <li><span>Masaje</span><em>$60.000</em></li>
                    </ul>
                    <div class="plan-price plan-price--dual plan-price--highlight">
                        <span class="plan-price-label">Inversión</span>
                        <div class="plan-price-rows">
                            <div class="plan-price-row"><span>12 sesiones</span><strong>$400.000</strong></div>
                            <div class="plan-price-row"><span>16–20 sesiones</span><strong>$600.000</strong></div>
                        </div>
                    </div>
                </div>
                <a href="solicitud.php" class="plan-cta">Consultar plan</a>
            </article>
        </div>
    </div>
</section>

<section class="institutions institutions-premium" id="instituciones">
    <div class="institutions-glow institutions-glow--left" aria-hidden="true"></div>
    <div class="institutions-glow institutions-glow--right" aria-hidden="true"></div>

    <div class="container">
        <div class="institutions-panel" data-animate style="--i: 0">
            <div class="institutions-layout">
                <div class="institutions-copy">
                    <div class="institutions-meta">
                        <span class="institutions-num">04</span>
                        <span class="institutions-kicker">Alianzas estratégicas</span>
                    </div>

                    <h2>Instituciones y eventos</h2>
                    <p class="institutions-lead">
                        FigueFit permite gestionar eventos, talleres, sesiones grupales
                        y programas para instituciones, con control de cupos,
                        participantes y seguimiento en un solo lugar.
                    </p>

                    <ul class="institutions-pillars">
                        <li>
                            <span class="pillar-icon" aria-hidden="true">
                                <svg viewBox="0 0 32 32" fill="none"><rect x="6" y="8" width="20" height="18" rx="2" stroke="currentColor" stroke-width="1.4"/><path d="M6 13h20M12 6v4M20 6v4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                            </span>
                            <span class="pillar-text"><strong>Eventos</strong>Programación y control de asistencia</span>
                        </li>
                        <li>
                            <span class="pillar-icon" aria-hidden="true">
                                <svg viewBox="0 0 32 32" fill="none"><path d="M8 24V12l8-5 8 5v12" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M13 24v-8h6v8" stroke="currentColor" stroke-width="1.4"/></svg>
                            </span>
                            <span class="pillar-text"><strong>Talleres</strong>Experiencias formativas para grupos</span>
                        </li>
                        <li>
                            <span class="pillar-icon" aria-hidden="true">
                                <svg viewBox="0 0 32 32" fill="none"><circle cx="11" cy="12" r="3" stroke="currentColor" stroke-width="1.4"/><circle cx="21" cy="12" r="3" stroke="currentColor" stroke-width="1.4"/><path d="M6 24c0-3 3-5 5-5s4 2 5 2 5-2 5-2 5 2 5 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                            </span>
                            <span class="pillar-text"><strong>Sesiones grupales</strong>Acompañamiento colectivo organizado</span>
                        </li>
                        <li>
                            <span class="pillar-icon" aria-hidden="true">
                                <svg viewBox="0 0 32 32" fill="none"><path d="M8 22V10h16v12H8z" stroke="currentColor" stroke-width="1.4"/><path d="M12 18l3 3 5-6" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="pillar-text"><strong>Cupos y seguimiento</strong>Gestión de participantes en tiempo real</span>
                        </li>
                    </ul>

                    <a href="#contacto" class="institutions-cta">Contactar →</a>
                </div>

                <div class="institutions-visual" aria-hidden="true">
                    <div class="institutions-orbit">
                        <div class="orbit-ring orbit-ring--outer"></div>
                        <div class="orbit-ring orbit-ring--inner"></div>
                        <div class="orbit-core">
                            <svg class="orbit-building" viewBox="0 0 120 140" fill="none">
                                <path d="M20 130V50l40-22 40 22v80H20z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
                                <path d="M44 130V78h32v52H44z" stroke="currentColor" stroke-width="2"/>
                                <path d="M20 72h80" stroke="currentColor" stroke-width="1.5" opacity="0.4"/>
                                <rect x="52" y="88" width="16" height="20" rx="1" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <div class="orbit-node orbit-node--1">
                            <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="14" stroke="currentColor" stroke-width="1.3"/><path d="M14 20h12M20 14v12" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <div class="orbit-node orbit-node--2">
                            <svg viewBox="0 0 40 40" fill="none"><circle cx="14" cy="16" r="4" stroke="currentColor" stroke-width="1.2"/><circle cx="26" cy="16" r="4" stroke="currentColor" stroke-width="1.2"/><path d="M8 28c2-3 5-4 6-4s3 1 6 4" stroke="currentColor" stroke-width="1.2"/></svg>
                        </div>
                        <div class="orbit-node orbit-node--3">
                            <svg viewBox="0 0 40 40" fill="none"><path d="M10 28V14h20v14H10z" stroke="currentColor" stroke-width="1.3"/><path d="M16 22l4 4 8-10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="contact contact-premium" id="contacto">
    <div class="contact-glow contact-glow--left" aria-hidden="true"></div>
    <div class="contact-glow contact-glow--right" aria-hidden="true"></div>

    <div class="container">
        <header class="contact-header">
            <div class="contact-meta">
                <span class="contact-num">05</span>
                <span class="contact-kicker">Conecta con FigueFit</span>
            </div>
            <h2>Contacto</h2>
            <p class="contact-lead">Envía tu solicitud o escríbenos por WhatsApp. Síguenos en TikTok e Instagram.</p>
        </header>

        <div class="contact-panel" data-animate style="--i: 0">
            <div class="contact-hub" aria-hidden="true">
                <div class="contact-phone-visual">
                    <span class="contact-phone-ring contact-phone-ring--outer"></span>
                    <span class="contact-phone-ring contact-phone-ring--inner"></span>
                    <div class="contact-phone-glow"></div>
                    <div class="contact-phone-icon-wrap">
                        <img src="icons/phone-classic.svg" alt="" class="contact-phone-illust" width="112" height="112" decoding="async">
                    </div>
                </div>
            </div>

            <div class="contact-cards">
                <article class="contact-card" data-animate style="--i: 1">
                    <div class="contact-card-icon">
                        <svg viewBox="0 0 40 40" fill="none" aria-hidden="true"><path d="M6 20L34 10v12l-28 8V20z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M14 20v8l6-3v-5" stroke="currentColor" stroke-width="1.3"/></svg>
                    </div>
                    <h3>Enviar solicitud</h3>
                    <p>Cuéntanos tu objetivo y recibe orientación para empezar tu plan.</p>
                    <a href="solicitud.php" class="contact-card-cta contact-card-cta--primary">Enviar solicitud</a>
                </article>

                <a href="<?php echo htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8'); ?>"
                   class="contact-card contact-card--whatsapp contact-card--link"
                   data-animate
                   style="--i: 2"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Escribir por WhatsApp al 321 364 2994">
                    <div class="contact-card-icon contact-card-icon--whatsapp">
                        <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
                            <path d="M20 6C12.268 6 6 12.268 6 20c0 2.69.668 5.22 1.844 7.448L6 34l6.734-1.79A13.94 13.94 0 0020 34c7.732 0 14-6.268 14-14S27.732 6 20 6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M15.2 17.8c.35 1.92 2.44 4.01 4.55 4.53.79.18 1.48.17 2.14-.09.58-.22 1.05-.55 1.49-.98.42-.4.45-1.05.08-1.48l-.92-.98c-.38-.4-.36-1.05.04-1.43.48-.46 1.18-1.12 1.35-1.82.12-.5-.18-.97-.67-1.12-1.22-.36-2.99-1.24-3.72-2.98-.24-.55-.05-1.18.45-1.48l1.1-.73c.34-.23.44-.7.22-1.05l-1.12-1.73c-.25-.38-.77-.5-1.15-.27l-1.58 1.01c-.73.47-1.1 1.34-.95 2.22.28 1.58 1.35 3.35 2.93 4.52z" stroke="currentColor" stroke-width="1.15" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>WhatsApp</h3>
                    <p>Chatea con nosotros al <strong>321 364 2994</strong>.</p>
                    <span class="contact-card-cta contact-card-cta--whatsapp">Escribir por WhatsApp</span>
                </a>

                <article class="contact-card contact-card--tiktok" data-animate style="--i: 3">
                    <div class="contact-card-icon contact-card-icon--tiktok">
                        <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
                            <path d="M22 9v14.2a4.8 4.8 0 11-4.8-4.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M22 9c1.6 2.1 3.8 3.6 7 4.2V20.5c-2.2-.15-4.4-.85-7-2.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.5 28.5a5.5 5.5 0 1011 0 5.5 5.5 0 00-11 0z" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <h3>TikTok</h3>
                    <p>Rutinas, tips y contenido diario para mantenerte motivado.</p>
                    <a href="https://www.tiktok.com/@figuefit" class="contact-card-cta contact-card-cta--tiktok" target="_blank" rel="noopener noreferrer">Seguir en TikTok</a>
                </article>

                <article class="contact-card contact-card--instagram" data-animate style="--i: 4">
                    <div class="contact-card-icon contact-card-icon--instagram">
                        <svg viewBox="0 0 40 40" fill="none" aria-hidden="true">
                            <rect x="9" y="9" width="22" height="22" rx="6" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="20" cy="20" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="27.8" cy="12.2" r="1.3" fill="currentColor"/>
                        </svg>
                    </div>
                    <h3>Instagram</h3>
                    <p>Resultados, comunidad y novedades de FigueFit en tu feed.</p>
                    <a href="https://www.instagram.com/figuefit" class="contact-card-cta contact-card-cta--instagram" target="_blank" rel="noopener noreferrer">Seguir en Instagram</a>
                </article>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="site-footer__line" aria-hidden="true"></div>
    <div class="container site-footer__grid">
        <div class="site-footer__brand">
            <a href="index.php" class="site-footer__logo">
                <img src="../img/Logo.png" alt="" width="48" height="48" decoding="async">
                <span>Figue<span class="site-footer__logo-accent">Fit</span></span>
            </a>
            <p class="site-footer__tagline">Entrenamiento, nutrición y seguimiento en una plataforma diseñada para tu evolución.</p>
        </div>

        <nav class="site-footer__col" aria-label="Enlaces del sitio">
            <span class="site-footer__label">Explorar</span>
            <ul class="site-footer__links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="#programas">Planes</a></li>
                <li><a href="#instituciones">Instituciones</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>
        </nav>

        <div class="site-footer__col">
            <span class="site-footer__label">Acceso</span>
            <ul class="site-footer__links">
                <li><a href="../views/auth/login.php">Iniciar sesión</a></li>
                <li><a href="../views/auth/register.php">Crear cuenta</a></li>
                <li><a href="solicitud.php">Enviar solicitud</a></li>
            </ul>
        </div>

        <div class="site-footer__col site-footer__col--connect">
            <span class="site-footer__label">Conecta</span>
            <a href="<?php echo htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" class="site-footer__phone" target="_blank" rel="noopener noreferrer">321 364 2994</a>
            <div class="site-footer__social">
                <a href="<?php echo htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" class="site-footer__social-btn" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.37 5.07L2 22l4.93-1.29A9.96 9.96 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2z" stroke="currentColor" stroke-width="1.4"/><path d="M9.2 9.4c.2 1.1 1.4 2.3 2.6 2.6.5.1.9.1 1.3-.04l.7-.7c.2-.2.5-.3.7-.2l1.3.7c.3.2.7 0 .9-.3.5-.7 1-1.4 1.2-2.2.1-.3-.1-.6-.4-.7l-1.3-.6c-.2-.1-.5 0-.7.2l-.6 1c-.2.2-.5.3-.8.2-1-.3-1.9-1-2.4-1.7-.2-.3 0-.6.3-.8l.7-.5c.2-.1.3-.4.1-.6l-.7-1.1c-.2-.2-.5-.3-.7-.2l-1 .6c-.5.3-.7.8-.6 1.3.2.9.8 1.9 1.8 2.6z" stroke="currentColor" stroke-width="0.9" stroke-linecap="round"/></svg>
                </a>
                <a href="https://www.tiktok.com/@figuefit" class="site-footer__social-btn" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 4v8.2a3.2 3.2 0 11-3.2-3.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M14 4c1.1 1.4 2.5 2.4 4.5 2.7V13c-1.5-.1-3-.6-4.5-1.3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                </a>
                <a href="https://www.instagram.com/figuefit" class="site-footer__social-btn" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5" stroke="currentColor" stroke-width="1.4"/><circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="site-footer__bar">
        <div class="container site-footer__bar-inner">
            <p>© <?php echo date('Y'); ?> FigueFit. Todos los derechos reservados.</p>
            <span class="site-footer__bar-dot" aria-hidden="true"></span>
            <p class="site-footer__bar-tag">Rompe tus límites</p>
        </div>
    </div>
</footer>

<a href="<?php echo htmlspecialchars($whatsappUrl, ENT_QUOTES, 'UTF-8'); ?>"
   class="whatsapp-float"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chatear por WhatsApp al 321 364 2994"
   title="WhatsApp 321 364 2994">
    <span class="whatsapp-float__ring" aria-hidden="true"></span>
    <span class="whatsapp-float__icon" aria-hidden="true">
        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 3C9.373 3 4 8.373 4 15c0 2.47.64 4.79 1.76 6.8L4 29l7.35-1.95A11.93 11.93 0 0016 27c6.627 0 12-5.373 12-12S22.627 3 16 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            <path d="M12.5 14.2c.28 1.55 1.96 3.24 3.68 3.66.64.15 1.2.14 1.72-.07.47-.18.85-.44 1.2-.79.34-.32.36-.85.06-1.2l-.74-.79c-.3-.32-.29-.85.03-1.15.38-.37.95-.9 1.08-1.47.1-.4-.14-.78-.54-.9-.98-.29-2.4-.99-2.98-2.4-.19-.44-.04-.95.36-1.19l.88-.59c.27-.18.35-.56.18-.84l-.9-1.4c-.2-.3-.62-.4-.92-.22l-1.27.81c-.58.38-.88 1.08-.76 1.79.22 1.27 1.08 2.7 2.35 3.64z" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
</a>

<script>
(function () {
    const SCROLL_OFFSET = 130;
    const nav = document.getElementById('categoryNav');
    const indicator = document.getElementById('dockIndicator');
    const track = nav ? nav.querySelector('.section-dock-track') : null;
    const links = document.querySelectorAll('.section-dock-link');
    const targets = Array.from(links)
        .map((link) => document.querySelector(link.dataset.target))
        .filter(Boolean);

    let isNavigating = false;
    let scrollFrame = null;

    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    function moveIndicator(activeLink) {
        if (!indicator || !track || !activeLink) return;
        const linkRect = activeLink.getBoundingClientRect();
        const trackRect = track.getBoundingClientRect();
        indicator.style.width = linkRect.width + 'px';
        indicator.style.transform = 'translateX(' + (linkRect.left - trackRect.left) + 'px)';
    }

    function setActiveLink(activeLink) {
        links.forEach((l) => l.classList.remove('is-active'));
        if (activeLink) {
            activeLink.classList.add('is-active');
            moveIndicator(activeLink);
        }
    }

    function smoothScrollTo(targetY, duration) {
        const startY = window.pageYOffset;
        const distance = targetY - startY;
        if (Math.abs(distance) < 2) return Promise.resolve();

        return new Promise((resolve) => {
            const startTime = performance.now();

            function frame(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                window.scrollTo(0, startY + distance * easeOutCubic(progress));
                if (progress < 1) {
                    scrollFrame = requestAnimationFrame(frame);
                    return;
                }
                resolve();
            }

            if (scrollFrame) cancelAnimationFrame(scrollFrame);
            scrollFrame = requestAnimationFrame(frame);
        });
    }

    async function navigateToSection(target, link) {
        if (!target || isNavigating) return;
        isNavigating = true;
        setActiveLink(link);

        const endY = target.getBoundingClientRect().top + window.pageYOffset - SCROLL_OFFSET;
        const duration = Math.min(950, Math.max(520, Math.abs(endY - window.pageYOffset) * 0.35));

        await smoothScrollTo(endY, duration);
        history.replaceState(null, '', link.dataset.target);
        isNavigating = false;
    }

    links.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            navigateToSection(document.querySelector(link.dataset.target), link);
        });
    });

    const observer = new IntersectionObserver(
        (entries) => {
            if (isNavigating) return;
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const active = Array.from(links).find((l) => l.dataset.target === '#' + entry.target.id);
                if (active) setActiveLink(active);
            });
        },
        { rootMargin: `-${SCROLL_OFFSET}px 0px -50% 0px`, threshold: 0.2 }
    );

    targets.forEach((section) => observer.observe(section));

    window.addEventListener('resize', () => {
        const active = nav.querySelector('.section-dock-link.is-active');
        if (active) moveIndicator(active);
    });

    if (links.length) {
        requestAnimationFrame(() => setActiveLink(links[0]));
    }
})();

(function () {
    const cards = document.querySelectorAll('[data-animate]');
    if (!cards.length) return;

    const reveal = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    reveal.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -6% 0px' }
    );

    cards.forEach((card) => reveal.observe(card));
})();
</script>

</body>
</html>
