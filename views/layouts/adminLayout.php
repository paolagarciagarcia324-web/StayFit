<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$tituloPagina = $tituloPagina ?? 'Panel Administrador | StayFit'; // Título por defecto
$vistaActiva = $vistaActiva ?? ''; // Vista activa del menú
$contenido = $contenido ?? ''; // Contenido de la vista
$nombreUsuario = $_SESSION['nombre'] ?? 'Administrador'; // Usuario en sesión

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title><?= e($tituloPagina) ?></title> <!-- Título dinámico -->

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            background: #2D2D2D;
            color: #FFFFFF;
            padding: 28px 20px;
            flex-shrink: 0;
        }

        .sidebar h2 {
            color: #D63384;
            margin: 0 0 30px;
            font-size: 28px;
        }

        .sidebar a {
            display: block;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #D63384;
        }

        .main-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #FFFFFF;
            padding: 18px 34px;
            box-shadow: 0 6px 20px rgba(45, 45, 45, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar strong {
            color: #D63384;
        }

        .content {
            padding: 34px;
            flex: 1;
        }

        .logout {
            background: #D63384;
            color: #FFFFFF;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 12px;
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .layout-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .topbar {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .content {
                padding: 24px;
            }
        }
    </style>
</head>

<body>

<div class="layout-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>

        <a class="<?= $vistaActiva === 'dashboard' ? 'active' : '' ?>" href="../../controller/admin/dashboardController.php">Dashboard</a>
        <a class="<?= $vistaActiva === 'usuarios' ? 'active' : '' ?>" href="../../controller/admin/usuarioController.php">Usuarios</a>
        <a class="<?= $vistaActiva === 'clientes' ? 'active' : '' ?>" href="../../controller/admin/clienteController.php">Clientes</a>
        <a class="<?= $vistaActiva === 'coaches' ? 'active' : '' ?>" href="../../controller/admin/coachController.php">Coaches</a>
        <a class="<?= $vistaActiva === 'planes' ? 'active' : '' ?>" href="../../controller/admin/planController.php">Planes</a>
        <a class="<?= $vistaActiva === 'solicitudes' ? 'active' : '' ?>" href="../../controller/admin/solicitudController.php">Solicitudes</a>
        <a class="<?= $vistaActiva === 'pagos' ? 'active' : '' ?>" href="../../controller/admin/pagoController.php">Pagos</a>
        <a class="<?= $vistaActiva === 'asignaciones' ? 'active' : '' ?>" href="../../controller/admin/asignacionController.php">Asignaciones</a>
        <a class="<?= $vistaActiva === 'instituciones' ? 'active' : '' ?>" href="../../controller/admin/institucionController.php">Instituciones</a>
        <a class="<?= $vistaActiva === 'notificaciones' ? 'active' : '' ?>" href="../../controller/admin/notificacionController.php">Notificaciones</a>
        <a class="<?= $vistaActiva === 'reportes' ? 'active' : '' ?>" href="../../controller/admin/reporteController.php">Reportes</a>
    </aside>

    <section class="main-area">

        <header class="topbar">
            <div>
                <strong>Administrador</strong>
                <p style="margin: 4px 0 0;">Hola, <?= e($nombreUsuario) ?></p>
            </div>

            <a class="logout" href="../../controller/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="content">
            <?= $contenido ?>
        </main>

    </section>

</div>

</body>
</html>