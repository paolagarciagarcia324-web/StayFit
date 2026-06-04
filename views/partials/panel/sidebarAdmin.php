<?php

$vistaActiva = $vistaActiva ?? '';

$links = [
    'dashboard'       => ['Dashboard',           '../../controllers/admin/dashboardController.php'],
    'solicitudes'     => ['Solicitudes',         '../../controllers/admin/solicitudController.php'],
    'pagos'           => ['Pagos',               '../../controllers/admin/pagoController.php'],
    'clientes'        => ['Clientes',            '../../controllers/admin/clienteController.php'],
    'coaches'         => ['Coaches',             '../../controllers/admin/coachController.php'],
    'asignaciones'    => ['Asignaciones',        '../../controllers/admin/asignacionController.php'],
    'planes'          => ['Planes',              '../../controllers/admin/planController.php'],
    'contenidoVirtual'=> ['Contenido virtual',   '../../controllers/admin/contenidoVirtualController.php'],
    'instituciones'   => ['Instituciones',       '../../controllers/admin/institucionController.php'],
    'notificaciones'  => ['Notificaciones',      '../../controllers/admin/notificacionController.php'],
    'usuarios'        => ['Usuarios',            '../../controllers/admin/usuarioController.php'],
    'reportes'        => ['Reportes',            '../../controllers/admin/reporteController.php'],
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
