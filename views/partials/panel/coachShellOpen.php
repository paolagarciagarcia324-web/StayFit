<?php

$tituloPagina = $tituloPagina ?? 'Panel Coach | FigueFit';
$vistaActiva = $vistaActiva ?? '';
$nombreCoach = $_SESSION['nombre'] ?? 'Coach';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="../../public/panel.css?v=18">
</head>
<body class="fp-panel">

<div class="fp-layout coach-wrapper">

    <?php require __DIR__ . '/sidebarCoach.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Coach</strong>
                <p class="fp-topbar-name">Hola, <?= htmlspecialchars((string) $nombreCoach, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">
