<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planes = $planes ?? []; // Lista de planes
$programas = $programas ?? []; // Lista de programas
$programasVirtuales = $programasVirtuales ?? []; // Programas virtuales

$totalActivos = 0; // Contador de activos

foreach ($planes as $planItem) { // Recorre planes
    if (($planItem['estado'] ?? '') === 'activo') { // Valida activos
        $totalActivos++; // Suma activo
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Planes | StayFit</title> <!-- Título -->
    <link rel="stylesheet" href="../../public/style.css"> <!-- Estilos -->

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
            grid-template-columns: 380px 1fr;
            gap: 22px;
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
            min-height: 80px;
        }

        button,
        .btn {
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 10px 15px;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
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
            background: #3EB489;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
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
        <a class="active" href="../../controller/admin/planController.php">Planes</a>
        <a href="../../controller/admin/pagoController.php">Pagos</a>
        <a href="../../controller/admin/asignacionController.php">Asignaciones</a>
        <a href="../../controller/admin/solicitudController.php">Solicitudes</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Planes y programas</h1>
            <p>Configura los planes visibles en el welcome público, sus precios, modalidad y accesos.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Crear plan</h3>

                <form action="../../controller/admin/planController.php?accion=guardarPlan" method="POST">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>

                    <label>Descripción</label>
                    <textarea name="descripcion" required></textarea>

                    <label>Precio</label>
                    <input type="number" name="precio" min="0" required>

                    <label>Duración en días</label>
                    <input type="number" name="duracion" min="1" required>

                    <label>Modalidad</label>
                    <select name="modalidad" required>
                        <option value="presencial">Presencial</option>
                        <option value="virtual">Virtual</option>
                        <option value="mixta">Mixta</option>
                    </select>

                    <label>Programa virtual</label>
                    <select name="programa_virtual_id">
                        <option value="">No aplica</option>
                        <?php foreach ($programasVirtuales as $programa): ?>
                            <option value="<?= e($programa['id'] ?? '') ?>">
                                <?= e($programa['nombre'] ?? 'Programa virtual') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label><input type="checkbox" name="incluye_entrenamiento"> Incluye entrenamiento</label>
                    <label><input type="checkbox" name="incluye_nutricion"> Incluye nutrición</label>
                    <label><input type="checkbox" name="requiere_coach"> Requiere coach</label>

                    <br><br>
                    <button type="submit">Guardar plan</button>
                </form>
            </div>

            <div class="card">
                <h3>Planes registrados</h3>
                <p><strong><?= e($totalActivos) ?></strong> planes activos disponibles para venta.</p>

                <table>
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Modalidad</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($planes)): ?>
                            <tr>
                                <td colspan="5">No hay planes registrados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($planes as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['nombre'] ?? '') ?></strong><br>
                                    <small><?= e($item['descripcion'] ?? '') ?></small>
                                </td>

                                <td><?= e($item['modalidad'] ?? 'No definida') ?></td>
                                <td>$<?= e($item['precio'] ?? '0') ?></td>

                                <td>
                                    <span class="badge <?= (($item['estado'] ?? '') === 'activo') ? '' : 'off' ?>">
                                        <?= e($item['estado'] ?? 'sin estado') ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (($item['estado'] ?? '') === 'activo'): ?>
                                        <a class="btn" href="../../controller/admin/planController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=inactivo">Inactivar</a>
                                    <?php else: ?>
                                        <a class="btn btn-green" href="../../controller/admin/planController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=activo">Activar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>

    </main>
</div>
</body>
</html>