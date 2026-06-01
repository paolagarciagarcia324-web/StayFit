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

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Comunicación Coach | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .coach-wrapper {
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

        @media (max-width: 1000px) {
            .coach-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .message.coach,
            .message.cliente {
                margin-left: 0;
                margin-right: 0;
            }
        }
    </style>
</head>

<body>
<div class="coach-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/coach/dashboardController.php">Dashboard</a>
        <a href="../../controller/coach/clientesController.php">Clientes</a>
        <a href="../../controller/coach/agendaController.php">Agenda</a>
        <a href="../../controller/coach/entrenamientoController.php">Entrenamientos</a>
        <a href="../../controller/coach/nutricionController.php">Nutrición</a>
        <a href="../../controller/coach/progresoController.php">Progreso</a>
        <a class="active" href="../../controller/coach/comunicacionController.php">Comunicación</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

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

                <form action="../../controller/coach/comunicacionController.php?accion=enviar" method="POST">
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

    </main>
</div>
</body>
</html>