<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$chat = $chat ?? null; // Chat activo
$mensajes = $mensajes ?? []; // Mensajes del cliente institucional

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Comunicación Institucional | StayFit</title> <!-- Título -->

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

        @media (max-width: 900px) {
            .cliente-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .chat-form {
                flex-direction: column;
            }

            button {
                padding: 14px;
            }
        }
    </style>
</head>

<body>
<div class="cliente-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controllers/clienteIns/dashboardController.php">Dashboard</a>
        <a href="../../controllers/clienteIns/perfilController.php">Perfil</a>
        <a href="../../controllers/clienteIns/institucionController.php">Institución</a>
        <a href="../../controllers/clienteIns/planController.php">Mi plan</a>
        <a href="../../controllers/clienteIns/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controllers/clienteIns/nutricionController.php">Nutrición</a>
        <a href="../../controllers/clienteIns/progresoController.php">Progreso</a>
        <a href="../../controllers/clienteIns/sesionGrupalController.php">Sesiones grupales</a>
        <a href="../../controllers/clienteIns/calendarioController.php">Calendario</a>
        <a class="active" href="../../controllers/clienteIns/comunicacionController.php">Comunicación</a>
        <a href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Comunicación</h1>
            <p>Comunícate con tu coach para resolver dudas sobre entrenamiento, nutrición o actividades institucionales.</p>
        </section>

        <section class="chat-card">

            <div class="chat-header">
                <h3>Canal institucional</h3>
                <span><?= e($chat['estado'] ?? 'Canal activo') ?></span>
            </div>

            <div class="messages">
                <?php if (empty($mensajes)): ?>
                    <div class="empty">Aún no tienes mensajes. Escribe a tu coach para iniciar el seguimiento.</div>
                <?php endif; ?>

                <?php foreach ($mensajes as $mensaje): ?>
                    <?php $tipo = ($mensaje['emisor'] ?? '') === 'cliente_institucional' ? 'cliente' : 'coach'; ?>

                    <div class="message <?= e($tipo) ?>">
                        <?= e($mensaje['mensaje'] ?? '') ?>
                        <small><?= e($mensaje['fecha'] ?? '') ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="chat-form" action="../../controllers/clienteIns/comunicacionController.php?accion=enviar" method="POST">
                <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
                <button type="submit">Enviar</button>
            </form>

        </section>

    </main>
</div>
</body>
</html>