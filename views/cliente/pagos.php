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
    <title>Pagos | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
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

        

        

        

        

        .badge.pendiente {
            background: #D63384;
        }

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Pagos</h1>
            <p>Consulta tu historial, envía comprobantes y mantén activo tu acceso a StayFit.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Enviar nuevo pago</h3>

                <form action="../../controllers/cliente/pagoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
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