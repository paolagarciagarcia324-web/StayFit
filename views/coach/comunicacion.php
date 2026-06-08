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

        <section class="fp-hero hero page-header">
            <h1><span>Comunicación</span></h1>
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