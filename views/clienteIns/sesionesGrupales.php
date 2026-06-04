<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$sesionesGrupales = $sesionesGrupales ?? []; // Sesiones grupales asignadas

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Sesiones Grupales | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.session-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
        }

        .session-card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
            border-top: 6px solid #D63384;
        }

        .session-card h3 {
            margin-top: 0;
            color: #D63384;
        }

        .session-info {
            background: #fff7fb;
            border-radius: 16px;
            padding: 14px;
            margin: 12px 0;
        }

        

        

        

        .empty {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 28px;
            color: #777;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Sesiones grupales</h1>
            <p>Consulta talleres, clases o actividades grupales asociadas a tu institución.</p>
        </section>

        <section class="session-grid">

            <?php if (empty($sesionesGrupales)): ?>
                <div class="empty">No tienes sesiones grupales programadas actualmente.</div>
            <?php endif; ?>

            <?php foreach ($sesionesGrupales as $sesion): ?>
                <article class="session-card">
                    <h3><?= e($sesion['titulo'] ?? 'Sesión grupal StayFit') ?></h3>

                    <p><?= e($sesion['descripcion'] ?? 'Actividad grupal institucional.') ?></p>

                    <div class="session-info">
                        <strong>Fecha:</strong>
                        <?= e($sesion['fecha'] ?? 'No registrada') ?>
                    </div>

                    <div class="session-info">
                        <strong>Hora:</strong>
                        <?= e($sesion['hora'] ?? 'No registrada') ?>
                    </div>

                    <div class="session-info">
                        <strong>Modalidad:</strong>
                        <?= e($sesion['modalidad'] ?? 'No definida') ?>
                    </div>

                    <span class="badge"><?= e($sesion['estado'] ?? 'programada') ?></span>

                    <br>

                    <a class="btn btn-green" href="../../controllers/clienteIns/sesionGrupalController.php?accion=confirmarAsistencia&id=<?= e($sesion['id'] ?? '') ?>">
                        Confirmar asistencia
                    </a>
                </article>
            <?php endforeach; ?>

        </section>

    </main>
</div>
</body>
</html>