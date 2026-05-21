<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clienteInstitucional = $clienteInstitucional ?? []; // Datos del cliente institucional
$institucion = $institucion ?? []; // Datos de institución
$convenio = $convenio ?? []; // Datos de convenio

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Institución | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .cliente-wrapper {
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

        .page-header {
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #D63384;
        }

        .info {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .info strong {
            color: #D63384;
            display: block;
            margin-bottom: 6px;
        }

        .badge {
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 13px;
        }

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }

        @media (max-width: 900px) {
            .cliente-wrapper {
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
<div class="cliente-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/clienteIns/dashboardController.php">Dashboard</a>
        <a href="../../controller/clienteIns/perfilController.php">Perfil</a>
        <a class="active" href="../../controller/clienteIns/institucionController.php">Institución</a>
        <a href="../../controller/clienteIns/planController.php">Mi plan</a>
        <a href="../../controller/clienteIns/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/clienteIns/nutricionController.php">Nutrición</a>
        <a href="../../controller/clienteIns/progresoController.php">Progreso</a>
        <a href="../../controller/clienteIns/sesionGrupalController.php">Sesiones grupales</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Mi institución</h1>
            <p>Consulta tu vínculo institucional, convenio activo y beneficios disponibles dentro de StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Información de la institución</h3>

                <?php if (empty($institucion)): ?>
                    <div class="empty">No tienes una institución vinculada actualmente.</div>
                <?php else: ?>
                    <div class="info">
                        <strong>Nombre</strong>
                        <?= e($institucion['nombre'] ?? 'Sin nombre') ?>
                    </div>

                    <div class="info">
                        <strong>NIT / Identificación</strong>
                        <?= e($institucion['nit'] ?? 'No registrado') ?>
                    </div>

                    <div class="info">
                        <strong>Correo</strong>
                        <?= e($institucion['correo'] ?? 'No registrado') ?>
                    </div>

                    <div class="info">
                        <strong>Teléfono</strong>
                        <?= e($institucion['telefono'] ?? 'No registrado') ?>
                    </div>

                    <span class="badge"><?= e($institucion['estado'] ?? 'activo') ?></span>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Convenio institucional</h3>

                <?php if (empty($convenio)): ?>
                    <div class="empty">No hay información de convenio registrada.</div>
                <?php else: ?>
                    <div class="info">
                        <strong>Tipo de convenio</strong>
                        <?= e($convenio['tipo'] ?? 'No definido') ?>
                    </div>

                    <div class="info">
                        <strong>Fecha de inicio</strong>
                        <?= e($convenio['fecha_inicio'] ?? 'No registrada') ?>
                    </div>

                    <div class="info">
                        <strong>Fecha de vencimiento</strong>
                        <?= e($convenio['fecha_fin'] ?? 'No registrada') ?>
                    </div>

                    <div class="info">
                        <strong>Beneficios</strong>
                        <?= e($convenio['beneficios'] ?? 'No registrados') ?>
                    </div>

                    <span class="badge"><?= e($convenio['estado'] ?? 'activo') ?></span>
                <?php endif; ?>
            </div>

        </section>

        <section class="card" style="margin-top: 24px;">
            <h3>Datos del cliente institucional</h3>

            <div class="info">
                <strong>Cliente</strong>
                <?= e($clienteInstitucional['nombre'] ?? $_SESSION['nombre'] ?? 'Cliente institucional') ?>
            </div>

            <div class="info">
                <strong>Estado de vinculación</strong>
                <?= e($clienteInstitucional['estado'] ?? 'activo') ?>
            </div>
        </section>

    </main>
</div>
</body>
</html>
