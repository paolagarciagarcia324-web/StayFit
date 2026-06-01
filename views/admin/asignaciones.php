<?php

if (!function_exists('e')) { // Evita repetir la función
    function e($valor) { // Limpia texto para imprimir
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Lista de clientes
$coaches = $coaches ?? []; // Lista de coaches
$programas = $programas ?? []; // Lista de programas virtuales
$asignaciones = $asignaciones ?? []; // Lista de asignaciones
$totalPlanes = $totalPlanes ?? 0;
$flash = $flash ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Asignaciones | StayFit</title> <!-- Título -->
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
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 22px;
            margin-bottom: 28px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #D63384;
        }

        label {
            font-weight: 600;
            font-size: 14px;
        }

        select,
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        button {
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 12px 18px;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 700;
        }

        button:hover {
            background: #b92b70;
        }

        .btn-secondary {
            background: #3EB489;
        }

        .table-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            color: #2D2D2D;
            padding: 14px;
            border-bottom: 2px solid #f0f0f0;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            background: #3EB489;
            color: #FFFFFF;
            font-size: 13px;
        }

        .alert {
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-weight: 600;
        }

        .alert-success {
            background: #e8f8f1;
            color: #1d6b4f;
            border: 1px solid #3EB489;
        }

        .alert-error {
            background: #fde8f0;
            color: #8b2252;
            border: 1px solid #D63384;
        }

        .alert-warning {
            background: #fff8e6;
            color: #7a5a00;
            border: 1px solid #e6b800;
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
        <a href="dashboardController.php">Dashboard</a>
        <a href="clienteController.php">Clientes</a>
        <a href="coachController.php">Coaches</a>
        <a class="active" href="asignacionController.php">Asignaciones</a>
        <a href="planController.php">Planes</a>
        <a href="pagoController.php">Pagos</a>
        <a href="solicitudController.php">Solicitudes</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Asignaciones</h1>
            <p>Asigna coaches o contenido virtual según la modalidad del plan de cada cliente.</p>
        </section>

        <?php if ($totalPlanes === 0): ?>
            <div class="alert-warning">
                No hay planes en el catálogo. Ve a <a href="planController.php" style="color:#7a5a00;font-weight:700;">Planes</a> y crea al menos uno antes de asignar coaches.
            </div>
        <?php endif; ?>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="grid">

            <div class="card">
                <h3>Asignar Coach</h3>

                <form action="../../controller/admin/asignacionController.php?accion=asignarCoach" method="POST">
                    <label>Cliente</label>
                    <select name="cliente_id" required>
                        <option value="">Seleccione cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= e($cliente['id'] ?? '') ?>">
                                <?= e(trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''))) ?: 'Cliente sin nombre' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Coach</label>
                    <select name="coach_id" required>
                        <option value="">Seleccione coach</option>
                        <?php foreach ($coaches as $coach): ?>
                            <option value="<?= e($coach['id'] ?? '') ?>">
                                <?= e($coach['nombre'] ?? 'Coach sin nombre') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Asignar coach</button>
                </form>
            </div>

            <div class="card">
                <h3>Asignar Contenido Virtual</h3>

                <form action="../../controller/admin/asignacionController.php?accion=asignarContenidoVirtual" method="POST">
                    <label>Cliente</label>
                    <select name="cliente_id" required>
                        <option value="">Seleccione cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= e($cliente['id'] ?? '') ?>">
                                <?= e(trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''))) ?: 'Cliente sin nombre' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Programa virtual</label>
                    <select name="programa_virtual_id" required>
                        <option value="">Seleccione programa</option>
                        <?php foreach ($programas as $programa): ?>
                            <option value="<?= e($programa['id'] ?? '') ?>">
                                <?= e($programa['nombre'] ?? 'Programa sin nombre') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button class="btn-secondary" type="submit">Asignar videos</button>
                </form>
            </div>

        </section>

        <section class="table-card">
            <h3>Historial de asignaciones</h3>

            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Modalidad</th>
                        <th>Coach</th>
                        <th>Contenido virtual</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($asignaciones)): ?>
                        <tr>
                            <td colspan="5">No hay asignaciones registradas.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($asignaciones as $item): ?>
                        <tr>
                            <td><?= e($item['cliente'] ?? 'Sin cliente') ?></td>
                            <td><?= e($item['modalidad'] ?? 'No definida') ?></td>
                            <td><?= e(trim($item['coach'] ?? '') !== '' && ($item['coach'] ?? '') !== 'Sin coach' ? $item['coach'] : 'Sin coach') ?></td>
                            <td><?= e($item['programa_virtual'] ?? 'No asignado') ?></td>
                            <td><span class="badge"><?= e($item['estado'] ?? 'ACTIVO') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>
</body>
</html>