<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$tituloPagina = $tituloPagina ?? 'Panel Cliente | FigueFit';
$vistaActiva = $vistaActiva ?? '';
$contenido = $contenido ?? '';
$nombreUsuario = $_SESSION['nombre'] ?? 'Cliente';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?></title>
    <link rel="stylesheet" href="../../public/panel.css?v=1">
</head>
<body class="fp-panel">

<div class="fp-layout layout-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <section class="fp-main-area main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente individual</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreUsuario) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">
            <?= $contenido ?>
        </main>
    </section>

</div>

</body>
</html>
