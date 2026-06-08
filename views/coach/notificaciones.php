<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$notificaciones = $notificaciones ?? []; // Lista de notificaciones
$tituloPagina = 'Notificaciones Coach | FigueFit';
$vistaActiva = 'notificaciones';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <h1><span>Notificaciones</span></h1>
            <p>Alertas sobre clientas, sesiones, mensajes, rutinas y seguimiento pendiente.</p>
        </section>

        <section class="notification-list">

            <?php if (empty($notificaciones)): ?>
                <div class="empty">No tienes notificaciones pendientes.</div>
            <?php endif; ?>

            <?php foreach ($notificaciones as $item): ?>
                <article class="notification-card <?= (($item['estado'] ?? '') === 'leida') ? 'leida' : '' ?>">
                    <h3><?= e($item['titulo'] ?? 'Notificación StayFit') ?></h3>
                    <p><?= e($item['mensaje'] ?? '') ?></p>
                    <small><?= e($item['fecha'] ?? '') ?></small>

                    <?php if (($item['estado'] ?? '') !== 'leida'): ?>
                        <br>
                        <a class="btn" href="../../controllers/coach/notificacionController.php?accion=marcarLeida&id=<?= e($item['id'] ?? '') ?>">
                            Marcar como leída
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>

        </section>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>