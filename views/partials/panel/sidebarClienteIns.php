<?php

$vistaActiva = $vistaActiva ?? '';

$links = [
    'dashboard'       => ['Dashboard',          '../../controllers/clienteIns/dashboardController.php'],
    'perfil'          => ['Perfil',             '../../controllers/clienteIns/perfilController.php'],
    'institucion'     => ['Institución',        '../../controllers/clienteIns/institucionController.php'],
    'plan'            => ['Mi plan',            '../../controllers/clienteIns/planController.php'],
    'entrenamiento'   => ['Entrenamiento',      '../../controllers/clienteIns/entrenamientoController.php'],
    'nutricion'       => ['Nutrición',          '../../controllers/clienteIns/nutricionController.php'],
    'progreso'        => ['Progreso',           '../../controllers/clienteIns/progresoController.php'],
    'sesionesGrupales'=> ['Sesiones grupales',  '../../controllers/clienteIns/sesionGrupalController.php'],
    'calendario'      => ['Calendario',         '../../controllers/clienteIns/calendarioController.php'],
    'comunicacion'    => ['Comunicación',       '../../controllers/clienteIns/comunicacionController.php'],
    'notificaciones'  => ['Notificaciones',     '../../controllers/clienteIns/notificacionController.php'],
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
