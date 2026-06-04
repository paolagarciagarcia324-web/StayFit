<?php

require_once __DIR__ . '/../../config/helpers.php';

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
            'aprobada', 'validada' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => $estado === 'validada' ? 'Validada' : 'Aprobada',
            ],
            'rechazada', 'cancelada' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => ucfirst($estado),
            ],
            'en_revision' => [
                'class' => 'fp-badge fp-badge-warn',
                'label' => 'En revisión',
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => 'Pendiente',
            ],
        };
    }
}

$solicitudes = $solicitudes ?? [];
$solicitud = $solicitud ?? null;
$flash = $flash ?? null;
$abrirModal = $abrirModal ?? false;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=3">
    <style>
        .sol-modal-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
            align-items: start;
        }

        .sol-modal-info-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .sol-modal-info-head h3 {
            margin: 0;
            font-size: 20px;
            color: var(--fp-white);
        }

        .sol-modal-dl {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 20px;
            margin: 0 0 22px;
        }

        .sol-modal-dl div {
            min-width: 0;
        }

        .sol-modal-dl dt {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--fp-text-muted);
        }

        .sol-modal-dl dd {
            margin: 0;
            font-size: 14px;
            color: var(--fp-text-soft);
            word-break: break-word;
        }

        .sol-modal-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .sol-modal-comprobante h4 {
            margin: 0 0 14px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--fp-text-muted);
        }

        .sol-modal-comprobante .comprobante-preview {
            margin-top: 0;
        }

        .sol-modal-comprobante .comprobante-img {
            max-height: 340px;
            width: 100%;
            object-fit: contain;
            background: rgba(0, 0, 0, 0.25);
        }

        .sol-modal-comprobante .comprobante-pdf {
            min-height: 340px;
            height: 340px;
        }

        .sol-reject-form label {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--fp-text-muted);
            text-transform: uppercase;
        }

        .sol-reject-form textarea {
            width: 100%;
            min-height: 80px;
            margin-bottom: 10px;
        }

        @media (max-width: 900px) {
            .sol-modal-layout {
                grid-template-columns: 1fr;
            }

            .sol-modal-dl {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Solicitudes de ingreso</h1>
            <p>Personas interesadas que enviaron sus datos y comprobante. Aún no son clientes activos.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h3>Listado de solicitudes</h3>

            <div class="fp-table-wrap">
                <table class="fp-table-premium">
                    <thead>
                        <tr>
                            <th>Solicitante</th>
                            <th>Plan</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                            <tr class="fp-empty-row">
                                <td colspan="5">No hay solicitudes registradas.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($solicitudes as $item): ?>
                            <?php
                            $estadoBadge = solicitudEstadoBadge($item['estado'] ?? 'pendiente');
                            $solicitudId = (int) ($item['id'] ?? 0);
                            $detalleUrl = '../../controllers/admin/solicitudController.php?accion=detalleFragment&id=' . $solicitudId;
                            ?>
                            <tr>
                                <td>
                                    <div class="fp-cell-stack">
                                        <strong><?= e($item['nombre'] ?? 'Sin nombre') ?></strong>
                                        <span>ID <?= e($item['identificacion'] ?? '—') ?></span>
                                        <?php if (!empty($item['celular'])): ?>
                                            <span class="fp-cell-highlight"><?= e($item['celular']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['correo'])): ?>
                                            <span><?= e($item['correo']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="fp-cell-stack">
                                        <strong><?= e($item['plan_interes'] ?? '—') ?></strong>
                                        <span class="fp-tag-inline"><?= e($item['modalidad'] ?? 'Sin modalidad') ?></span>
                                        <?php if (!empty($item['monto_pago'])): ?>
                                            <span>$<?= e(number_format((float) $item['monto_pago'], 0, ',', '.')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <?php if (!empty($item['url_comprobante'])): ?>
                                        <a class="fp-link-compact" target="_blank" rel="noopener"
                                           href="<?= e(urlPublicaComprobante($item['url_comprobante'], $solicitudId)) ?>">
                                            Ver comprobante
                                        </a>
                                    <?php else: ?>
                                        <span class="sin-comprobante">Sin archivo</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                </td>

                                <td>
                                    <div class="fp-row-actions">
                                        <button type="button"
                                                class="btn fp-btn-sm fp-btn-outline js-sol-detalle"
                                                data-solicitud-id="<?= e($solicitudId) ?>"
                                                data-detalle-url="<?= e($detalleUrl) ?>">
                                            Detalle
                                        </button>

                                        <?php if (($item['estado'] ?? '') === 'pendiente'): ?>
                                            <a class="btn fp-btn-sm fp-btn-outline-mint"
                                               href="../../controllers/admin/solicitudController.php?accion=marcarRevision&id=<?= e($item['id'] ?? '') ?>">
                                                Revisar
                                            </a>
                                        <?php endif; ?>

                                        <a class="btn fp-btn-sm btn-green"
                                           href="../../controllers/admin/validacionPagoController.php?accion=aprobar&solicitud_id=<?= e($item['id'] ?? '') ?>">
                                            Aprobar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<div class="fp-modal" id="solModal" aria-hidden="true" role="dialog" aria-labelledby="solModalTitle">
    <div class="fp-modal-backdrop" data-close-modal></div>
    <div class="fp-modal-dialog fp-modal-dialog--wide">
        <header class="fp-modal-header">
            <div>
                <span class="fp-modal-tag">Solicitud de ingreso</span>
                <h2 id="solModalTitle">Detalle del solicitante</h2>
            </div>
            <button type="button" class="fp-modal-close" data-close-modal aria-label="Cerrar">&times;</button>
        </header>
        <div class="fp-modal-body" id="solModalBody">
            <?php if ($solicitud): ?>
                <?php require __DIR__ . '/partials/solicitudDetalleContenido.php'; ?>
            <?php else: ?>
                <p class="fp-modal-loading">Cargando detalle…</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('solModal');
    var body = document.getElementById('solModalBody');
    if (!modal || !body) return;

    var fragmentBase = '../../controllers/admin/solicitudController.php?accion=detalleFragment&id=';

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('fp-modal-open');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('fp-modal-open');
        if (window.location.search.indexOf('accion=detalle') !== -1) {
            history.replaceState(null, '', '../../controllers/admin/solicitudController.php');
        }
    }

    function loadDetalle(id) {
        body.innerHTML = '<p class="fp-modal-loading">Cargando detalle…</p>';
        openModal();

        fetch(fragmentBase + encodeURIComponent(id), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('No se pudo cargar el detalle.');
                return res.text();
            })
            .then(function (html) {
                body.innerHTML = html;
            })
            .catch(function () {
                body.innerHTML = '<p class="fp-modal-error">No se pudo cargar el detalle. Intenta de nuevo.</p>';
            });
    }

    document.querySelectorAll('.js-sol-detalle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-solicitud-id');
            if (id) loadDetalle(id);
        });
    });

    modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    <?php if ($abrirModal && $solicitud): ?>
    openModal();
    <?php endif; ?>
})();
</script>

</body>
</html>
