<?php

$planesPublicos = $planesPublicos ?? [];
$totalPlanesPublicos = $totalPlanesPublicos ?? count($planesPublicos);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes | FigueFit</title>
    <link rel="stylesheet" href="style.css?v=33">
</head>
<body class="page-home page-planes">

<header class="header">
    <div class="container header-content">
        <a href="index.php" class="logo">
            <img src="../img/Logo.png" alt="Logo FigueFit">
            <span>Figue<span>Fit</span></span>
        </a>

        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon"></label>

        <nav class="navbar">
            <a href="index.php#inicio">Inicio</a>
            <a href="index.php#servicios">Servicios</a>
            <a href="planPublico.php" class="is-current">Planes</a>
            <a href="index.php#instituciones">Instituciones</a>
            <a href="index.php#contacto">Contacto</a>
        </nav>

        <div class="header-actions">
            <a class="login-link" href="../views/auth/login.php">Iniciar sesión</a>
            <a class="register-link" href="../views/auth/register.php">Crear cuenta</a>
        </div>
    </div>
</header>

<section class="plans plans-premium plans-page" id="programas">
    <div class="plans-glow plans-glow--left" aria-hidden="true"></div>
    <div class="plans-glow plans-glow--right" aria-hidden="true"></div>

    <div class="container">
        <header class="plans-header">
            <div class="plans-meta">
                <span class="plans-num">03</span>
                <span class="plans-kicker">Membresías FigueFit</span>
            </div>
            <h1>Planes y programas</h1>
            <p class="plans-lead">
                <?php if ($totalPlanesPublicos > 0): ?>
                    <?= (int) $totalPlanesPublicos ?> plan<?= $totalPlanesPublicos === 1 ? '' : 'es' ?> activo<?= $totalPlanesPublicos === 1 ? '' : 's' ?> disponible<?= $totalPlanesPublicos === 1 ? '' : 's' ?>. Elige el tuyo y empieza tu proceso con FigueFit.
                <?php else: ?>
                    Por ahora no hay planes activos. Vuelve pronto o contáctanos para más información.
                <?php endif; ?>
            </p>
        </header>

        <?php require __DIR__ . '/../partials/public/planesCatalogo.php'; ?>

        <div class="plans-back">
            <a href="index.php#programas" class="plans-back-link">
                <span class="plans-back-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M5 12l6 6M5 12l6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Volver al inicio</span>
            </a>
        </div>
    </div>
</section>

<script>
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
