<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$chat = $chat ?? null;
$mensajes = $mensajes ?? [];
$sinCoach = $sinCoach ?? false;
$flash = $flash ?? null;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Comunicación | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.chat-card {
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
            overflow: hidden;
        }

        .chat-header {
            background: #2D2D2D;
            color: #FFFFFF;
            padding: 20px 24px;
        }

        .chat-header h3 {
            margin: 0 0 6px;
            color: #D63384;
        }

        .messages {
            padding: 24px;
            min-height: 360px;
            max-height: 460px;
            overflow-y: auto;
            background: #fbfbfb;
        }

        .message {
            max-width: 75%;
            padding: 14px 16px;
            border-radius: 18px;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .message.cliente {
            background: #D63384;
            color: #FFFFFF;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .message.coach {
            background: #FFFFFF;
            color: #2D2D2D;
            border: 1px solid #eee;
            border-bottom-left-radius: 4px;
        }

        .message small {
            display: block;
            margin-top: 6px;
            opacity: 0.8;
            font-size: 12px;
        }

        .alert-success {
            background: #e8f8f1;
            color: #1d6b4f;
            border-left: 5px solid #3EB489;
            padding: 14px;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .alert-error {
            background: #fff1f7;
            color: #8b2252;
            border-left: 5px solid #D63384;
            padding: 14px;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .alert-warning {
            background: #fff8e6;
            color: #7a5a00;
            border-left: 5px solid #e6b800;
            padding: 14px;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .chat-form {
            padding: 20px 24px;
            display: flex;
            gap: 12px;
            border-top: 1px solid #eee;
            background: #FFFFFF;
        }

        textarea {
            flex: 1;
            resize: none;
            min-height: 54px;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 16px;
            font-family: inherit;
        }

        button {
            background: #3EB489;
            color: #FFFFFF;
            border: none;
            padding: 0 22px;
            border-radius: 16px;
            font-weight: 800;
            cursor: pointer;
        }

        .empty {
            text-align: center;
            color: #777;
            padding: 60px 20px;
        }

        

            button {
                padding: 14px;
            }
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Comunicación</h1>
            <p>Habla con tu coach, resuelve dudas y mantén seguimiento de tu proceso.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <?php if ($sinCoach): ?>
            <div class="alert-warning">
                Aún no tienes coach asignado. El administrador debe asignarte uno en <strong>Asignaciones</strong> para poder chatear.
            </div>
        <?php endif; ?>

        <section class="chat-card">

            <div class="chat-header">
                <h3>Chat con tu coach</h3>
                <span><?= $sinCoach ? 'Sin coach asignado' : 'Canal activo' ?></span>
            </div>

            <div class="messages">
                <?php if (empty($mensajes)): ?>
                    <div class="empty">Aún no tienes mensajes. Escribe a tu coach para iniciar la conversación.</div>
                <?php endif; ?>

                <?php foreach ($mensajes as $mensaje): ?>
                    <?php $tipo = ($mensaje['emisor'] ?? '') === 'cliente' ? 'cliente' : 'coach'; ?>

                    <div class="message <?= e($tipo) ?>">
                        <?= e($mensaje['mensaje'] ?? '') ?>
                        <small><?= e($mensaje['fecha'] ?? '') ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="chat-form" action="../../controllers/cliente/comunicacionController.php?accion=enviar" method="POST">
                <textarea name="mensaje" placeholder="Escribe tu mensaje..." <?= $sinCoach ? 'disabled' : '' ?> required></textarea>
                <button type="submit" <?= $sinCoach ? 'disabled' : '' ?>>Enviar</button>
            </form>

        </section>

    </main>
</div>
</body>
</html>