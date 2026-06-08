<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$chats = $chats ?? [];
$mensajes = $mensajes ?? [];
$clientes = $clientes ?? [];
$flash = $flash ?? null;

$tituloPagina = 'Comunicación Coach | FigueFit';
$vistaActiva = 'comunicacion';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Comunicación Coach | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
select,
        textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        button {
            width: 100%;
            background: #3EB489;
            color: #FFFFFF;
            border: none;
            padding: 13px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .chat-item {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .chat-item strong {
            color: #D63384;
        }

        .message {
            background: #FFFFFF;
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .message.coach {
            background: #D63384;
            color: #FFFFFF;
            margin-left: 60px;
        }

        .message.cliente {
            background: #f6fffb;
            margin-right: 60px;
        }

        .empty {
            background: #f4f4f4;
            color: #777;
            padding: 18px;
            border-radius: 16px;
        }

        .alert-success {
            background: #e8f8f1;
            color: #1d6b4f;
            border: 1px solid #3EB489;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
        }

        .alert-error {
            background: #fde8f0;
            color: #8b2252;
            border: 1px solid #D63384;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
        }

        
        }
    </style>
</head>

<body class="fp-panel">
<div class="coach-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCoach.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Comunicación</h1>
            <p>Responde mensajes, acompaña el proceso y mantén comunicación clara con tus clientas.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="grid">

            <div class="card">
                <h3>Enviar mensaje</h3>

                <form action="../../controllers/coach/comunicacionController.php?accion=enviar" method="POST">
                    <label>Cliente</label>
                    <select name="cliente_id" required>
                        <option value="">Seleccione cliente</option>

                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= e($cliente['id'] ?? '') ?>">
                                <?= e($cliente['nombre'] ?? 'Cliente') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Mensaje</label>
                    <textarea name="mensaje" placeholder="Escribe una respuesta o recomendación..." required></textarea>

                    <button type="submit">Enviar mensaje</button>
                </form>

                <hr>

                <h3>Chats activos</h3>

                <?php if (empty($chats)): ?>
                    <div class="empty">No tienes chats activos.</div>
                <?php endif; ?>

                <?php foreach ($chats as $chat): ?>
                    <div class="chat-item">
                        <strong><?= e($chat['cliente'] ?? 'Cliente') ?></strong>
                        <p>Estado: <?= e($chat['estado'] ?? 'activo') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h3>Mensajes recientes</h3>

                <?php if (empty($mensajes)): ?>
                    <div class="empty">No hay mensajes registrados todavía.</div>
                <?php endif; ?>

                <?php foreach ($mensajes as $mensaje): ?>
                    <?php $tipo = ($mensaje['emisor'] ?? '') === 'coach' ? 'coach' : 'cliente'; ?>

                    <div class="message <?= e($tipo) ?>">
                        <strong><?= e($mensaje['emisor'] ?? 'usuario') ?></strong>
                        <p><?= e($mensaje['mensaje'] ?? '') ?></p>
                        <small><?= e($mensaje['fecha'] ?? '') ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>