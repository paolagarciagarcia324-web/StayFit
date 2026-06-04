<?php

$vistaActiva = $vistaActiva ?? '';

$links = [
    'dashboard'         => ['Dashboard',            '../../controllers/coach/dashboardController.php'],
    'clientes'          => ['Clientes',             '../../controllers/coach/clientesController.php'],
    'agenda'            => ['Agenda',               '../../controllers/coach/agendaController.php'],
    'entrenamiento'     => ['Entrenamientos',       '../../controllers/coach/entrenamientoController.php'],
    'nutricion'         => ['Nutrición',            '../../controllers/coach/nutricionController.php'],
    'progreso'          => ['Progreso',             '../../controllers/coach/progresoController.php'],
    'seguimientoVirtual'=> ['Seguimiento virtual',  '../../controllers/coach/seguimientoVirtualController.php'],
    'comunicacion'      => ['Comunicación',         '../../controllers/coach/comunicacionController.php'],
    'notificaciones'    => ['Notificaciones',       '../../controllers/coach/notificacionController.php'],
    'reportes'          => ['Reportes',             '../../controllers/coach/reporteController.php'],
    'contenidoVirtual'  => ['Contenido virtual',    '../../controllers/coach/contenidoVirtualController.php'],
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
