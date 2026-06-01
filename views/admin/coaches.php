<?php

if (!function_exists('e')) { // Evita repetir la función
    function e($valor) { // Limpia texto para imprimir
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$coaches = $coaches ?? []; // Lista de coaches
$coach = $coach ?? null; // Detalle de coach
$clientes = $clientes ?? []; // Clientes asignados
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Coaches | StayFit</title> <!-- Título -->
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

        .page-header {
            background: linear-gradient(135deg, #2D2D2D, #D63384);
            color: #FFFFFF;
            border-radius: 22px;
            padding: 30px;
            margin-bottom: 28px;
        }

        .page-header h1 {
            margin: 0 0 8px;
            font-size: 32px;
        }

        .grid {
            display: grid;
            grid-template-columns: 360px 1fr;
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

        label {
            font-weight: 600;
            font-size: 14px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-family: inherit;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button,
        .btn {
            display: inline-block;
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 10px 15px;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-green {
            background: #3EB489;
        }

        .btn-dark {
            background: #2D2D2D;
        }

        .coach-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .coach-card {
            border: 1px solid #eee;
            border-radius: 18px;
            padding: 18px;
            background: #FFFFFF;
        }

        .coach-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: #D63384;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: #3EB489;
            color: #FFFFFF;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .badge.off {
            background: #D63384;
        }

        @media (max-width: 1000px) {
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
        <a href="dashboardController.php">Dashboard</a>
        <a href="clienteController.php">Clientes</a>
        <a class="active" href="coachController.php">Coaches</a>
        <a href="asignacionController.php">Asignaciones</a>
        <a href="planController.php">Planes</a>
        <a href="pagoController.php">Pagos</a>
        <a href="solicitudController.php">Solicitudes</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Coaches</h1>
            <p>Administra el equipo profesional que acompaña el entrenamiento, nutrición y progreso de las clientas.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Registrar nuevo coach</h3>

                <form action="../../controller/admin/coachController.php?accion=guardar" method="POST">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" required>

                    <label>Correo</label>
                    <input type="email" name="correo" required>

                    <label>Identificación</label>
                    <input type="text" name="identificacion" required>

                    <label>Celular</label>
                    <input type="text" name="celular" required>

                    <label>Contraseña de acceso</label>
                    <input type="password" name="contrasena" minlength="6" placeholder="Si se deja vacío, se usa la identificación">

                    <label>Especialidad</label>
                    <input type="text" name="especialidad" placeholder="Ej: Fuerza, pérdida de grasa, movilidad" required>

                    <label>Biografía profesional</label>
                    <textarea name="biografia" placeholder="Describe experiencia, enfoque y tipo de acompañamiento"></textarea>

                    <button type="submit">Guardar coach</button>
                </form>
            </div>

            <div class="card">
                <h3>Equipo de coaches</h3>

                <div class="coach-list">
                    <?php if (empty($coaches)): ?>
                        <p>No hay coaches registrados.</p>
                    <?php endif; ?>

                    <?php foreach ($coaches as $item): ?>
                        <article class="coach-card">
                            <div class="coach-avatar">
                                <?= strtoupper(substr(e($item['nombre'] ?? 'C'), 0, 1)) ?>
                            </div>

                            <span class="badge <?= (($item['estado'] ?? '') === 'activo') ? '' : 'off' ?>">
                                <?= e($item['estado'] ?? 'sin estado') ?>
                            </span>

                            <h3><?= e($item['nombre'] ?? 'Coach sin nombre') ?></h3>
                            <p><strong>Especialidad:</strong> <?= e($item['especialidad'] ?? 'No definida') ?></p>
                            <p><strong>Correo:</strong> <?= e($item['correo'] ?? 'Sin correo') ?></p>
                            <p><strong>Celular:</strong> <?= e($item['celular'] ?? 'Sin celular') ?></p>

                            <a class="btn btn-dark" href="../../controller/admin/coachController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">
                                Ver
                            </a>

                            <?php if (($item['estado'] ?? '') === 'activo'): ?>
                                <a class="btn" href="../../controller/admin/coachController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=inactivo">
                                    Inactivar
                                </a>
                            <?php else: ?>
                                <a class="btn btn-green" href="../../controller/admin/coachController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=activo">
                                    Activar
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

        </section>

        <?php if ($coach): ?>
            <section class="card" style="margin-top: 24px;">
                <h3>Detalle del coach</h3>
                <p><strong>Nombre:</strong> <?= e($coach['nombre'] ?? '') ?></p>
                <p><strong>Especialidad:</strong> <?= e($coach['especialidad'] ?? '') ?></p>
                <p><strong>Clientes asignados:</strong> <?= count($clientes) ?></p>
            </section>
        <?php endif; ?>

    </main>
</div>
</body>
</html>