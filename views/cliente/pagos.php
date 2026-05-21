<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$pagos = $pagos ?? []; // Historial de pagos
$planes = $planes ?? []; // Planes disponibles si llegan desde controlador

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Pagos | StayFit</title> <!-- Título -->

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
            grid-template-columns: 360px 1fr;
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

        label {
            font-weight: 700;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 13px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        button:hover {
            background: #b92b70;
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
            display: inline-block;
            background: #3EB489;
            color: #FFFFFF;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .badge.pendiente {
            background: #D63384;
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
        <a href="../../controller/cliente/dashboardController.php">Dashboard</a>
        <a href="../../controller/cliente/perfilController.php">Perfil</a>
        <a href="../../controller/cliente/planController.php">Mi plan</a>
        <a href="../../controller/cliente/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/cliente/nutricionController.php">Nutrición</a>
        <a href="../../controller/cliente/progresoController.php">Progreso</a>
        <a href="../../controller/cliente/calendarioController.php">Calendario</a>
        <a class="active" href="../../controller/cliente/pagoController.php">Pagos</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Pagos</h1>
            <p>Consulta tu historial, envía comprobantes y mantén activo tu acceso a StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Enviar nuevo pago</h3>

                <form action="../../controller/cliente/pagoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                    <label>Plan</label>

                    <?php if (!empty($planes)): ?>
                        <select name="plan_id" required>
                            <option value="">Seleccione un plan</option>
                            <?php foreach ($planes as $plan): ?>
                                <option value="<?= e($plan['id'] ?? '') ?>">
                                    <?= e($plan['nombre'] ?? 'Plan') ?> - $<?= e($plan['precio'] ?? '0') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="number" name="plan_id" placeholder="ID del plan" required>
                    <?php endif; ?>

                    <label>Monto pagado</label>
                    <input type="number" name="monto" min="0" required>

                    <label>Tipo de cuenta</label>
                    <select name="tipo_cuenta" required>
                        <option value="">Seleccione una opción</option>
                        <option value="ahorros">Ahorros</option>
                        <option value="corriente">Corriente</option>
                        <option value="nequi">Nequi</option>
                        <option value="daviplata">Daviplata</option>
                    </select>

                    <label>Número de cuenta</label>
                    <input type="text" name="numero_cuenta" required>

                    <label>Comprobante</label>
                    <input type="file" name="comprobante" accept="image/*,.pdf" required>

                    <button type="submit">Enviar comprobante</button>
                </form>
            </div>

            <div class="card">
                <h3>Historial de pagos</h3>

                <?php if (empty($pagos)): ?>
                    <div class="empty">No tienes pagos registrados todavía.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= e($pago['plan'] ?? $pago['plan_id'] ?? 'Plan') ?></td>
                                    <td>$<?= e($pago['monto'] ?? '0') ?></td>
                                    <td>
                                        <span class="badge <?= (($pago['estado'] ?? '') === 'pendiente') ? 'pendiente' : '' ?>">
                                            <?= e($pago['estado'] ?? 'pendiente') ?>
                                        </span>
                                    </td>
                                    <td><?= e($pago['fecha'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </section>

    </main>
</div>
</body>
</html>