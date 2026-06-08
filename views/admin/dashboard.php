<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$datos = $datos ?? [];

$clientesActivos       = $datos['clientesActivos'] ?? 0;
$solicitudesPendientes = $datos['solicitudesPendientes'] ?? 0;
$pagosPendientes       = $datos['pagosPendientes'] ?? 0;
$planesVirtuales       = $datos['planesVirtuales'] ?? 0;
$accesosVencidos       = $datos['accesosVencidos'] ?? 0;

$vistaActiva = 'dashboard';
$nombreUsuario = $_SESSION['nombre'] ?? 'Administrador';

$iniciales = '';
$partesNombre = preg_split('/\s+/', trim($nombreUsuario));
if (!empty($partesNombre[0])) {
    $iniciales .= strtoupper(substr($partesNombre[0], 0, 1));
}
if (isset($partesNombre[1])) {
    $iniciales .= strtoupper(substr($partesNombre[1], 0, 1));
}
if ($iniciales === '') {
    $iniciales = 'A';
}

$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaHoy = $dias[(int) date('w')] . ', ' . date('j') . ' de ' . $meses[(int) date('n')] . ' ' . date('Y');

require_once __DIR__ . '/../partials/panel/dashIcons.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1">
    <link rel="stylesheet" href="../../public/dashboard-admin.css?v=2">
</head>
<body class="fp-panel fp-dashboard-premium">

<div class="fp-layout admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div class="fp-dash-topbar-inner">
                <div class="fp-dash-greeting">
                    <div class="fp-dash-avatar" aria-hidden="true"><?= e($iniciales) ?></div>
                    <div>
                        <strong class="fp-topbar-role">Administrador</strong>
                        <p class="fp-topbar-name">Hola, <?= e($nombreUsuario) ?></p>
                    </div>
                </div>
            </div>
        </header>

        <main class="fp-content content">

            <section class="fp-dash-hero">
                <div class="fp-dash-hero-glow fp-dash-hero-glow--1"></div>
                <div class="fp-dash-hero-glow fp-dash-hero-glow--2"></div>
                <div class="fp-dash-hero-grid"></div>

                <div class="fp-dash-hero-body">
                    <div>
                        <span class="fp-dash-hero-tag">Panel de control</span>
                        <h1>Panel <em>Administrativo</em></h1>
                        <p class="fp-dash-hero-desc">
                            Control general de FigueFit: clientes, pagos, accesos, modalidad virtual y trazabilidad operativa en un solo lugar.
                        </p>
                    </div>
                    <div class="fp-dash-hero-meta">
                        <time datetime="<?= date('Y-m-d') ?>"><?= e($fechaHoy) ?></time>
                        <strong>Sistema operativo</strong>
                    </div>
                </div>
            </section>

            <section class="fp-dash-kpis">
                <article class="fp-dash-kpi">
                    <div class="fp-dash-kpi-head">
                        <span class="fp-dash-kpi-icon" aria-hidden="true"><?= dashIcon('users') ?></span>
                    </div>
                    <span class="fp-dash-kpi-label">Clientes activos</span>
                    <p class="fp-dash-kpi-value"><?= e($clientesActivos) ?></p>
                    <p class="fp-dash-kpi-foot">Clientes con acceso habilitado</p>
                </article>

                <article class="fp-dash-kpi fp-dash-kpi--amber">
                    <div class="fp-dash-kpi-head">
                        <span class="fp-dash-kpi-icon" aria-hidden="true"><?= dashIcon('clipboard') ?></span>
                    </div>
                    <span class="fp-dash-kpi-label">Solicitudes pendientes</span>
                    <p class="fp-dash-kpi-value"><?= e($solicitudesPendientes) ?></p>
                    <p class="fp-dash-kpi-foot">Personas esperando validación</p>
                </article>

                <article class="fp-dash-kpi fp-dash-kpi--purple">
                    <div class="fp-dash-kpi-head">
                        <span class="fp-dash-kpi-icon" aria-hidden="true"><?= dashIcon('card') ?></span>
                    </div>
                    <span class="fp-dash-kpi-label">Pagos por validar</span>
                    <p class="fp-dash-kpi-value"><?= e($pagosPendientes) ?></p>
                    <p class="fp-dash-kpi-foot">Comprobantes pendientes</p>
                </article>

                <article class="fp-dash-kpi fp-dash-kpi--mint">
                    <div class="fp-dash-kpi-head">
                        <span class="fp-dash-kpi-icon" aria-hidden="true"><?= dashIcon('play') ?></span>
                    </div>
                    <span class="fp-dash-kpi-label">Planes virtuales</span>
                    <p class="fp-dash-kpi-value"><?= e($planesVirtuales) ?></p>
                    <p class="fp-dash-kpi-foot">Contenido pregrabado activo</p>
                </article>

                <article class="fp-dash-kpi fp-dash-kpi--alert">
                    <div class="fp-dash-kpi-head">
                        <span class="fp-dash-kpi-icon" aria-hidden="true"><?= dashIcon('alert') ?></span>
                    </div>
                    <span class="fp-dash-kpi-label">Accesos vencidos</span>
                    <p class="fp-dash-kpi-value"><?= e($accesosVencidos) ?></p>
                    <p class="fp-dash-kpi-foot">Requieren revisión inmediata</p>
                </article>
            </section>

            <section class="fp-dash-grid">
                <div class="fp-dash-panel">
                    <div class="fp-dash-panel-head">
                        <span class="fp-dash-panel-bar"></span>
                        <h3>Trazabilidad operativa</h3>
                    </div>

                    <div class="fp-dash-steps">
                        <div class="fp-dash-step fp-dash-step--done">
                            <div class="fp-dash-step-num">1</div>
                            <div class="fp-dash-step-body">
                                <strong>Solicitud recibida</strong>
                                <p>El usuario interesado llena el formulario público y queda pendiente de revisión.</p>
                                <span class="fp-dash-badge fp-dash-badge--mint">Pendiente</span>
                            </div>
                        </div>

                        <div class="fp-dash-step fp-dash-step--active">
                            <div class="fp-dash-step-num">2</div>
                            <div class="fp-dash-step-body">
                                <strong>Validación de pago</strong>
                                <p>El administrador revisa el comprobante y aprueba o rechaza la solicitud.</p>
                                <span class="fp-dash-badge fp-dash-badge--pink">Clave</span>
                            </div>
                        </div>

                        <div class="fp-dash-step">
                            <div class="fp-dash-step-num">3</div>
                            <div class="fp-dash-step-body">
                                <strong>Activación del cliente</strong>
                                <p>Al aprobarse el pago, se habilita el plan, coach o contenido virtual.</p>
                                <span class="fp-dash-badge fp-dash-badge--mint">Activo</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fp-dash-panel">
                    <div class="fp-dash-panel-head">
                        <span class="fp-dash-panel-bar"></span>
                        <h3>Acciones rápidas</h3>
                    </div>

                    <div class="fp-dash-actions">
                        <a class="fp-dash-action" href="../../controllers/admin/solicitudController.php">
                            <span class="fp-dash-action-icon" aria-hidden="true"><?= dashIcon('inbox') ?></span>
                            <span class="fp-dash-action-text">Revisar solicitudes pendientes</span>
                            <span class="fp-dash-action-arrow" aria-hidden="true"><?= dashIcon('arrow') ?></span>
                        </a>
                        <a class="fp-dash-action" href="../../controllers/admin/pagoController.php">
                            <span class="fp-dash-action-icon" aria-hidden="true"><?= dashIcon('check') ?></span>
                            <span class="fp-dash-action-text">Validar pagos</span>
                            <span class="fp-dash-action-arrow" aria-hidden="true"><?= dashIcon('arrow') ?></span>
                        </a>
                        <a class="fp-dash-action" href="../../controllers/admin/clienteController.php">
                            <span class="fp-dash-action-icon" aria-hidden="true"><?= dashIcon('user') ?></span>
                            <span class="fp-dash-action-text">Gestionar clientes</span>
                            <span class="fp-dash-action-arrow" aria-hidden="true"><?= dashIcon('arrow') ?></span>
                        </a>
                        <a class="fp-dash-action" href="../../controllers/admin/asignacionController.php">
                            <span class="fp-dash-action-icon" aria-hidden="true"><?= dashIcon('link') ?></span>
                            <span class="fp-dash-action-text">Asignar coach o videos</span>
                            <span class="fp-dash-action-arrow" aria-hidden="true"><?= dashIcon('arrow') ?></span>
                        </a>
                        <a class="fp-dash-action" href="../../controllers/admin/planController.php">
                            <span class="fp-dash-action-icon" aria-hidden="true"><?= dashIcon('package') ?></span>
                            <span class="fp-dash-action-text">Administrar planes</span>
                            <span class="fp-dash-action-arrow" aria-hidden="true"><?= dashIcon('arrow') ?></span>
                        </a>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

</body>
</html>
