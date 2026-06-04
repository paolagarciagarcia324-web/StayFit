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
    <title>Institución | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
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

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

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
