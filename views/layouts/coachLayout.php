<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$tituloPagina = $tituloPagina ?? 'Panel Coach | StayFit'; // Título por defecto
$vistaActiva = $vistaActiva ?? ''; // Vista activa
$contenido = $contenido ?? ''; // Contenido principal
$nombreUsuario = $_SESSION['nombre'] ?? 'Coach'; // Nombre en sesión

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

        <a class="<?= $vistaActiva === 'dashboard' ? 'active' : '' ?>" href="../../controller/coach/dashboardController.php">Dashboard</a>
        <a class="<?= $vistaActiva === 'clientes' ? 'active' : '' ?>" href="../../controller/coach/clientesController.php">Clientes</a>
        <a class="<?= $vistaActiva === 'agenda' ? 'active' : '' ?>" href="../../controller/coach/agendaController.php">Agenda</a>
        <a class="<?= $vistaActiva === 'entrenamiento' ? 'active' : '' ?>" href="../../controller/coach/entrenamientoController.php">Entrenamientos</a>
        <a class="<?= $vistaActiva === 'nutricion' ? 'active' : '' ?>" href="../../controller/coach/nutricionController.php">Nutrición</a>
        <a class="<?= $vistaActiva === 'progreso' ? 'active' : '' ?>" href="../../controller/coach/progresoController.php">Progreso</a>
        <a class="<?= $vistaActiva === 'seguimientoVirtual' ? 'active' : '' ?>" href="../../controller/coach/seguimientoVirtualController.php">Seguimiento virtual</a>
        <a class="<?= $vistaActiva === 'comunicacion' ? 'active' : '' ?>" href="../../controller/coach/comunicacionController.php">Comunicación</a>
        <a class="<?= $vistaActiva === 'notificaciones' ? 'active' : '' ?>" href="../../controller/coach/notificacionController.php">Notificaciones</a>
        <a class="<?= $vistaActiva === 'reportes' ? 'active' : '' ?>" href="../../controller/coach/reporteController.php">Reportes</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>
    </aside>

    <section class="main-area">

        <header class="topbar">
            <div>
                <strong>Coach StayFit</strong>
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