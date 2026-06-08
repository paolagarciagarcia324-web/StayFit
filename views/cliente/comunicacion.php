<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('fpFormatearFechaChat')) {
    function fpFormatearFechaChat(?string $fecha): string
    {
        if (!$fecha) {
            return '';
        }

        $ts = strtotime($fecha);
        if ($ts === false) {
            return (string) $fecha;
        }

        $hoy = date('Y-m-d');
        $diaMsg = date('Y-m-d', $ts);

        if ($diaMsg === $hoy) {
            return 'Hoy · ' . date('H:i', $ts);
        }

        $ayer = date('Y-m-d', strtotime('-1 day'));
        if ($diaMsg === $ayer) {
            return 'Ayer · ' . date('H:i', $ts);
        }

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        return date('d', $ts) . ' ' . $meses[(int) date('n', $ts) - 1] . ' · ' . date('H:i', $ts);
    }
}

if (!function_exists('fpInicialesNombre')) {
    function fpInicialesNombre(string $nombre): string
    {
        $iniciales = '';
        foreach (preg_split('/\s+/', trim($nombre)) as $parte) {
            if ($parte !== '') {
                $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
            }
            if (mb_strlen($iniciales) >= 2) {
                break;
            }
        }

        return $iniciales !== '' ? $iniciales : 'FF';
    }
}

$chat = $chat ?? null;
$mensajes = $mensajes ?? [];
$sinCoach = $sinCoach ?? false;
$coach = $coach ?? null;
$flash = $flash ?? null;

$nombreTopbar = $_SESSION['nombre'] ?? 'Cliente';

$coachNombre = trim((string) ($coach['nombre_completo'] ?? ''));
if ($coachNombre === '') {
    $coachNombre = trim(($coach['nombre'] ?? '') . ' ' . ($coach['apellido'] ?? ''));
}
if ($coachNombre === '') {
    $coachNombre = 'Tu coach';
}

$coachEspecialidad = trim((string) ($coach['especialidad'] ?? ''));
$coachIniciales = fpInicialesNombre($coachNombre);

$totalMensajes = count($mensajes);
$mensajesCoach = 0;
$mensajesCliente = 0;

foreach ($mensajes as $msg) {
    if (($msg['emisor'] ?? '') === 'coach') {
        $mensajesCoach++;
    } elseif (($msg['emisor'] ?? '') === 'cliente') {
        $mensajesCliente++;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicación | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=18">
</head>
<body class="fp-panel">

<div class="fp-layout cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente individual</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreTopbar) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Seguimiento directo</span>
                <h1><span>Comunicación</span></h1>
                <p>Habla con tu coach, resuelve dudas y mantén seguimiento de tu proceso en tiempo real.</p>
            </section>

            <?php if (!empty($flash['mensaje'])): ?>
                <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                    <?= e($flash['mensaje']) ?>
                </div>
            <?php endif; ?>

            <section class="fp-stats-premium">
                <article class="fp-stat-premium fp-stat-premium--fuchsia">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M21 15a4 4 0 01-4 4H8l-5 3V7a4 4 0 014-4h10a4 4 0 014 4v8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e((string) $totalMensajes) ?></p>
                    <p class="fp-stat-premium-label">Mensajes en el chat</p>
                </article>

                <article class="fp-stat-premium fp-stat-premium--mint">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon fp-coach-avatar" aria-hidden="true"><?= e($coachIniciales) ?></div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:15px;line-height:1.35;"><?= e($sinCoach ? 'Sin asignar' : $coachNombre) ?></p>
                    <p class="fp-stat-premium-label"><?= e($sinCoach ? 'Coach pendiente' : ($coachEspecialidad !== '' ? $coachEspecialidad : 'Coach asignado')) ?></p>
                </article>

                <article class="fp-stat-premium">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 12h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <?php if (!$sinCoach): ?>
                                    <path d="M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                <?php endif; ?>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value"><?= e($sinCoach ? 'Inactivo' : 'Activo') ?></p>
                    <p class="fp-stat-premium-label"><?= e($sinCoach ? 'Esperando asignación' : $mensajesCoach . ' del coach · ' . $mensajesCliente . ' tuyos') ?></p>
                </article>
            </section>

            <?php if ($sinCoach): ?>
                <div class="fp-comunicacion-alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 9v4M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                    <p>Aún no tienes coach asignado. El administrador debe asignarte uno en <strong>Asignaciones</strong> para poder chatear.</p>
                </div>
            <?php endif; ?>

            <section class="fp-chat-card fp-comunicacion-chat">
                <header class="fp-chat-header fp-comunicacion-header">
                    <div class="fp-comunicacion-coach">
                        <div class="fp-comunicacion-avatar" aria-hidden="true"><?= e($coachIniciales) ?></div>
                        <div>
                            <h3><?= e($sinCoach ? 'Chat con tu coach' : $coachNombre) ?></h3>
                            <span class="fp-comunicacion-status <?= $sinCoach ? 'fp-comunicacion-status--off' : 'fp-comunicacion-status--on' ?>">
                                <?= $sinCoach ? 'Sin coach asignado' : 'Canal activo' ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!$sinCoach && $coachEspecialidad !== ''): ?>
                        <span class="fp-tag-inline"><?= e($coachEspecialidad) ?></span>
                    <?php endif; ?>
                </header>

                <div class="fp-messages fp-comunicacion-messages" id="chat-messages">
                    <?php if (empty($mensajes)): ?>
                        <div class="fp-comunicacion-empty">
                            <div class="fp-comunicacion-empty-icon" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 15a4 4 0 01-4 4H8l-5 3V7a4 4 0 014-4h10a4 4 0 014 4v8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <strong>Inicia la conversación</strong>
                            <p>Escribe tu primer mensaje para contactar a tu coach.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($mensajes as $mensaje): ?>
                            <?php
                            $esCliente = ($mensaje['emisor'] ?? '') === 'cliente';
                            $clase = $esCliente ? 'fp-message--sent' : 'fp-message--received';
                            ?>
                            <article class="fp-message fp-comunicacion-bubble <?= e($clase) ?>">
                                <span class="fp-comunicacion-bubble-label"><?= $esCliente ? 'Tú' : 'Coach' ?></span>
                                <p class="fp-comunicacion-bubble-text"><?= e($mensaje['mensaje'] ?? '') ?></p>
                                <time datetime="<?= e($mensaje['fecha'] ?? '') ?>"><?= e(fpFormatearFechaChat($mensaje['fecha'] ?? '')) ?></time>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form class="fp-chat-form fp-comunicacion-form" action="../../controllers/cliente/comunicacionController.php?accion=enviar" method="POST">
                    <div class="fp-comunicacion-input-wrap">
                        <textarea name="mensaje" rows="1" placeholder="<?= $sinCoach ? 'Chat no disponible sin coach asignado' : 'Escribe tu mensaje…' ?>" <?= $sinCoach ? 'disabled' : '' ?> required></textarea>
                    </div>
                    <button type="submit" class="fp-comunicacion-send" <?= $sinCoach ? 'disabled' : '' ?>>
                        <span>Enviar</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </section>

        </main>
    </div>
</div>

<script>
(function () {
    var box = document.getElementById('chat-messages');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }
})();
</script>
</body>
</html>
