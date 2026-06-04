<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$datos = $datos ?? []; // Datos del dashboard

$clientesActivos = $datos['clientesActivos'] ?? 0; // Clientes activos
$solicitudesPendientes = $datos['solicitudesPendientes'] ?? 0; // Solicitudes pendientes
$pagosPendientes = $datos['pagosPendientes'] ?? 0; // Pagos pendientes
$planesVirtuales = $datos['planesVirtuales'] ?? 0; // Planes virtuales
$accesosVencidos = $datos['accesosVencidos'] ?? 0; // Accesos vencidos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Dashboard Admin | StayFit</title> <!-- Título -->
    <link rel="stylesheet" href="../../public/style.css"> <!-- Estilos generales -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            background: #2D2D2D;
            color: #FFFFFF;
            padding: 28px 20px;
        }

        .sidebar h2 {
            color: #D63384;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #D63384;
        }

        .content {
            flex: 1;
            padding: 34px;
        }

        .hero {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            border-radius: 24px;
            padding: 34px;
            margin-bottom: 28px;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: 34px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .stat-card small {
            color: #777;
            font-weight: 600;
        }

        .stat-card h2 {
            margin: 10px 0;
            color: #D63384;
            font-size: 34px;
        }

        .stat-card p {
            margin: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 22px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            color: #D63384;
            margin-top: 0;
        }

        .timeline-item {
            padding: 14px 0;
            border-bottom: 1px solid #eee;
        }

        .timeline-item strong {
            color: #2D2D2D;
        }

        .badge {
            background: #3EB489;
            color: #FFFFFF;
            padding: 7px 12px;
            border-radius: 18px;
            font-size: 13px;
        }

        .badge-alert {
            background: #D63384;
        }

        @media (max-width: 900px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a class="active" href="../../controllers/admin/dashboardController.php">Dashboard</a>
        <a href="../../controllers/admin/solicitudController.php">Solicitudes</a>
        <a href="../../controllers/admin/pagoController.php">Pagos</a>
        <a href="../../controllers/admin/clienteController.php">Clientes</a>
        <a href="../../controllers/admin/coachController.php">Coaches</a>
        <a href="../../controllers/admin/asignacionController.php">Asignaciones</a>
        <a href="../../controllers/admin/planController.php">Planes</a>
        <a href="../../controllers/admin/contenidoVirtualController.php">Contenido virtual</a>
        <a href="../../controllers/admin/institucionController.php">Instituciones</a>
        <a href="../../controllers/admin/notificacionController.php">Notificaciones</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

    <main class="content">

        <section class="hero">
            <h1>Panel Administrativo</h1>
            <p>Control general de StayFit: clientes, pagos, accesos, modalidad virtual y trazabilidad.</p>
        </section>

        <section class="stats">
            <div class="stat-card">
                <small>Clientes activos</small>
                <h2><?= e($clientesActivos) ?></h2>
                <p>Clientas con acceso habilitado.</p>
            </div>

            <div class="stat-card">
                <small>Solicitudes pendientes</small>
                <h2><?= e($solicitudesPendientes) ?></h2>
                <p>Personas esperando validación.</p>
            </div>

            <div class="stat-card">
                <small>Pagos por validar</small>
                <h2><?= e($pagosPendientes) ?></h2>
                <p>Comprobantes pendientes.</p>
            </div>

            <div class="stat-card">
                <small>Planes virtuales activos</small>
                <h2><?= e($planesVirtuales) ?></h2>
                <p>Clientes con contenido pregrabado.</p>
            </div>

            <div class="stat-card">
                <small>Accesos vencidos</small>
                <h2><?= e($accesosVencidos) ?></h2>
                <p>Clientes que requieren revisión.</p>
            </div>
        </section>

        <section class="grid">
            <div class="card">
                <h3>Trazabilidad operativa</h3>

                <div class="timeline-item">
                    <strong>Solicitud recibida</strong>
                    <p>El usuario interesado llena el formulario público y queda pendiente.</p>
                    <span class="badge">Pendiente</span>
                </div>

                <div class="timeline-item">
                    <strong>Validación de pago</strong>
                    <p>El administrador revisa el comprobante y aprueba o rechaza.</p>
                    <span class="badge badge-alert">Clave</span>
                </div>

                <div class="timeline-item">
                    <strong>Activación del cliente</strong>
                    <p>Al aprobarse el pago, se habilita el plan, coach o contenido virtual.</p>
                    <span class="badge">Activo</span>
                </div>
            </div>

            <div class="card">
                <h3>Acciones rápidas</h3>
                <p><a href="../../controllers/admin/solicitudController.php">Revisar solicitudes pendientes</a></p>
                <p><a href="../../controllers/admin/pagoController.php">Validar pagos</a></p>
                <p><a href="../../controllers/admin/clienteController.php">Gestionar clientes</a></p>
                <p><a href="../../controllers/admin/asignacionController.php">Asignar coach o videos</a></p>
                <p><a href="../../controllers/admin/planController.php">Administrar planes</a></p>
            </div>
        </section>

    </main>
</div>
</body>
</html>