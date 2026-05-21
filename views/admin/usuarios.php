<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$usuarios = $usuarios ?? []; // Lista de usuarios
$roles = $roles ?? []; // Lista de roles

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Usuarios | StayFit</title> <!-- Título -->
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
            padding: 9px 13px;
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
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
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
        <a class="active" href="../../controller/admin/usuarioController.php">Usuarios</a>
        <a href="../../controller/admin/clienteController.php">Clientes</a>
        <a href="../../controller/admin/coachController.php">Coaches</a>
        <a href="../../controller/admin/notificacionController.php">Notificaciones</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Usuarios</h1>
            <p>Administra credenciales, roles y estados de acceso al sistema StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Crear usuario</h3>

                <form action="../../controller/admin/usuarioController.php?accion=guardar" method="POST">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>

                    <label>Correo</label>
                    <input type="email" name="correo" required>

                    <label>Contraseña</label>
                    <input type="password" name="password" required>

                    <label>Rol</label>
                    <select name="rol_id" required>
                        <option value="">Seleccione rol</option>

                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= e($rol['id'] ?? '') ?>">
                                <?= e($rol['nombre'] ?? 'Rol') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Guardar usuario</button>
                </form>
            </div>

            <div class="card">
                <h3>Listado de usuarios</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="5">No hay usuarios registrados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($usuarios as $item): ?>
                            <tr>
                                <td><?= e($item['nombre'] ?? '') ?></td>
                                <td><?= e($item['correo'] ?? '') ?></td>
                                <td><?= e($item['rol'] ?? $item['rol_nombre'] ?? 'Sin rol') ?></td>

                                <td>
                                    <span class="badge <?= (($item['estado'] ?? '') === 'activo') ? '' : 'off' ?>">
                                        <?= e($item['estado'] ?? 'sin estado') ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (($item['estado'] ?? '') === 'activo'): ?>
                                        <a class="btn" href="../../controller/admin/usuarioController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=inactivo">Inactivar</a>
                                    <?php else: ?>
                                        <a class="btn btn-green" href="../../controller/admin/usuarioController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=activo">Activar</a>
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