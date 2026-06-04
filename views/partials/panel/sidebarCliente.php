<?php

$vistaActiva = $vistaActiva ?? '';

$links = [
    'dashboard'      => ['Dashboard',      '../../controllers/cliente/dashboardController.php'],
    'perfil'         => ['Perfil',         '../../controllers/cliente/perfilController.php'],
    'plan'           => ['Mi plan',        '../../controllers/cliente/planController.php'],
    'entrenamiento'  => ['Entrenamiento',  '../../controllers/cliente/entrenamientoController.php'],
    'nutricion'      => ['Nutrición',      '../../controllers/cliente/nutricionController.php'],
    'progreso'       => ['Progreso',       '../../controllers/cliente/progresoController.php'],
    'calendario'     => ['Calendario',     '../../controllers/cliente/calendarioController.php'],
    'pagos'          => ['Pagos',          '../../controllers/cliente/pagoController.php'],
    'comunicacion'   => ['Comunicación',   '../../controllers/cliente/comunicacionController.php'],
    'notificaciones' => ['Notificaciones', '../../controllers/cliente/notificacionController.php'],
];

?>
<aside class="fp-sidebar sidebar">
    <?php require __DIR__ . '/brand.php'; ?>
    <nav class="fp-nav">
        <?php foreach ($links as $key => [$label, $url]): ?>
            <a class="<?= $vistaActiva === $key ? 'active' : '' ?>" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
        <?php require_once __DIR__ . '/../cerrarSesion.php'; ?>
    </nav>
</aside>
