<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$instituciones = $instituciones ?? []; // Lista de instituciones
$clientesInstitucionales = $clientesInstitucionales ?? []; // Clientes institucionales
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Instituciones | StayFit</title> <!-- Título -->
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
        select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 12px;
        }

        button,
        .btn {
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
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
        <a href="../../controller/admin/dashboardController.php">Dashboard</a>
        <a href="../../controller/admin/clienteController.php">Clientes</a>
        <a href="../../controller/admin/planController.php">Planes</a>
        <a class="active" href="../../controller/admin/institucionController.php">Instituciones</a>
        <a href="../../controller/admin/asignacionController.php">Asignaciones</a>
        <a href="../../controller/admin/pagoController.php">Pagos</a>
        <?php require_once __DIR__ . '/../partials/cerrarSesion.php'; ?>

    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Instituciones</h1>
            <p>Administra convenios, instituciones y clientes institucionales vinculados a StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Registrar institución</h3>

                <form action="../../controller/admin/institucionController.php?accion=guardar" method="POST">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>

                    <label>NIT o identificación</label>
                    <input type="text" name="nit" required>

                    <label>Teléfono</label>
                    <input type="text" name="telefono" required>

                    <label>Correo</label>
                    <input type="email" name="correo" required>

                    <label>Dirección</label>
                    <input type="text" name="direccion" required>

                    <button type="submit">Guardar institución</button>
                </form>
            </div>

            <div class="card">
                <h3>Listado de instituciones</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Institución</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($instituciones)): ?>
                            <tr>
                                <td colspan="4">No hay instituciones registradas.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($instituciones as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['nombre'] ?? '') ?></strong><br>
                                    <small><?= e($item['nit'] ?? '') ?></small>
                                </td>

                                <td>
                                    <?= e($item['correo'] ?? '') ?><br>
                                    <small><?= e($item['telefono'] ?? '') ?></small>
                                </td>

                                <td>
                                    <span class="badge <?= (($item['estado'] ?? '') === 'activo') ? '' : 'off' ?>">
                                        <?= e($item['estado'] ?? 'sin estado') ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (($item['estado'] ?? '') === 'activo'): ?>
                                        <a class="btn" href="../../controller/admin/institucionController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=inactivo">Inactivar</a>
                                    <?php else: ?>
                                        <a class="btn btn-green" href="../../controller/admin/institucionController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=activo">Activar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>

        <section class="card" style="margin-top: 24px;">
            <h3>Clientes institucionales vinculados</h3>

            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Institución</th>
                        <th>Cargo / relación</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($clientesInstitucionales)): ?>
                        <tr>
                            <td colspan="4">No hay clientes institucionales vinculados.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($clientesInstitucionales as $cliente): ?>
                        <tr>
                            <td><?= e($cliente['cliente'] ?? 'Sin cliente') ?></td>
                            <td><?= e($cliente['institucion'] ?? 'Sin institución') ?></td>
                            <td><?= e($cliente['cargo'] ?? 'No definido') ?></td>
                            <td><span class="badge"><?= e($cliente['estado'] ?? 'activo') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>
</body>
</html>