<?php

require_once __DIR__ . '/../../../config/helpers.php';

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('solicitudEstadoBadge')) {
    function solicitudEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'aprobada', 'validada' => ['class' => 'fp-badge fp-badge-ok', 'label' => $estado === 'validada' ? 'Validada' : 'Aprobada'],
            'rechazada', 'cancelada' => ['class' => 'fp-badge fp-badge-alert', 'label' => ucfirst($estado)],
            'en_revision' => ['class' => 'fp-badge fp-badge-warn', 'label' => 'En revisión'],
            default => ['class' => 'fp-badge fp-badge-pending', 'label' => 'Pendiente'],
        };
    }
}

$solicitud = $solicitud ?? null;
if (!$solicitud) {
    return;
}

$estadoBadge = solicitudEstadoBadge($solicitud['estado'] ?? 'pendiente');
$solicitudId = (int) ($solicitud['id'] ?? 0);

?>
<div class="sol-modal-layout">
    <div class="sol-modal-info">
        <div class="sol-modal-info-head">
            <h3><?= e($solicitud['nombre'] ?? 'Solicitud') ?></h3>
            <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
        </div>

        <dl class="sol-modal-dl">
            <div><dt>Identificación</dt><dd><?= e($solicitud['identificacion'] ?? '—') ?></dd></div>
            <div><dt>Edad</dt><dd><?= e($solicitud['edad'] ?? '—') ?></dd></div>
            <div><dt>Celular</dt><dd><?= e($solicitud['celular'] ?? '—') ?></dd></div>
            <div><dt>Correo</dt><dd><?= e($solicitud['correo'] ?? '—') ?></dd></div>
            <div><dt>Plan</dt><dd><?= e($solicitud['plan_interes'] ?? '—') ?></dd></div>
            <div><dt>Modalidad</dt><dd><?= e($solicitud['modalidad'] ?? '—') ?></dd></div>
            <div><dt>Tipo cuenta</dt><dd><?= e($solicitud['tipo_cuenta'] ?? '—') ?></dd></div>
            <div><dt>Número cuenta</dt><dd><?= e($solicitud['numero_cuenta'] ?? '—') ?></dd></div>
            <div><dt>Monto</dt><dd>$<?= e(number_format((float) ($solicitud['monto_pago'] ?? 0), 0, ',', '.')) ?></dd></div>
        </dl>

        <div class="sol-modal-actions">
            <?php if (($solicitud['estado'] ?? '') === 'pendiente'): ?>
                <a class="btn fp-btn-sm fp-btn-outline-mint"
                   href="../../controllers/admin/solicitudController.php?accion=marcarRevision&id=<?= e($solicitudId) ?>">
                    Marcar en revisión
                </a>
            <?php endif; ?>
            <a class="btn fp-btn-sm btn-green"
               href="../../controllers/admin/validacionPagoController.php?accion=aprobar&solicitud_id=<?= e($solicitudId) ?>">
                Aprobar pago
            </a>
        </div>

        <form class="sol-reject-form" action="../../controllers/admin/solicitudController.php?accion=rechazar" method="POST">
            <input type="hidden" name="id" value="<?= e($solicitudId) ?>">
            <label for="observacion-<?= e($solicitudId) ?>">Rechazar solicitud</label>
            <textarea id="observacion-<?= e($solicitudId) ?>" name="observacion" placeholder="Motivo del rechazo"></textarea>
            <button class="btn fp-btn-sm fp-btn-outline" type="submit">Rechazar</button>
        </form>
    </div>

    <div class="sol-modal-comprobante">
        <h4>Comprobante de pago</h4>
        <?php
        $urlComprobante = $solicitud['url_comprobante'] ?? null;
        $solicitudIdComprobante = $solicitudId;
        require __DIR__ . '/comprobanteVista.php';
        ?>
    </div>
</div>
