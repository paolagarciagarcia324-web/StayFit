<?php

require_once __DIR__ . '/../../config/helpers.php';

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

$planes = $planes ?? [];
$plan = $plan ?? null;
$programa = $programa ?? null;
$materiales = $materiales ?? [];
$categorias = $categorias ?? [];
$flash = $flash ?? null;
$planId = (int) ($plan['id_plan'] ?? $_GET['plan_id'] ?? 0);
$ctrl = '../../controllers/coach/contenidoVirtualController.php';

$tituloPagina = 'Contenido virtual | FigueFit Coach';
$vistaActiva = 'contenidoVirtual';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <h1>Biblioteca <span>virtual</span></h1>
            <p>Sube videos, fotos o enlaces con descripción para los planes virtuales. Tus clientas lo verán en entrenamiento.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h3>Seleccionar plan virtual</h3>
            <form method="GET" action="<?= e($ctrl) ?>">
                <select name="plan_id" onchange="this.form.submit()">
                    <option value="">— Elija un plan —</option>
                    <?php foreach ($planes as $p): ?>
                        <option value="<?= e($p['id_plan']) ?>" <?= $planId === (int) $p['id_plan'] ? 'selected' : '' ?>>
                            <?= e($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </section>

        <?php if ($planId > 0 && $plan): ?>
        <section class="card">
            <h3>Programa: <?= e($plan['nombre']) ?></h3>
            <form action="<?= e($ctrl) ?>?accion=guardarPrograma" method="POST">
                <input type="hidden" name="plan_id" value="<?= e($planId) ?>">
                <label>Nombre</label>
                <input type="text" name="nombre" required value="<?= e($programa['nombre'] ?? 'Programa ' . $plan['nombre']) ?>">
                <label>Descripción para la clienta</label>
                <textarea name="descripcion"><?= e($programa['descripcion'] ?? '') ?></textarea>
                <button type="submit" class="btn btn-green">Guardar programa</button>
            </form>
        </section>

        <?php if ($programa): ?>
        <section class="card">
            <h3>Añadir material</h3>
            <form action="<?= e($ctrl) ?>?accion=guardarMaterial" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="plan_id" value="<?= e($planId) ?>">
                <label>Título</label>
                <input type="text" name="titulo" required>
                <label>Descripción / instrucciones</label>
                <textarea name="descripcion"></textarea>
                <label>Orden</label>
                <input type="number" name="orden" value="<?= count($materiales) + 1 ?>" min="1">
                <label>Tipo</label>
                <select name="tipo_media" id="tipo_media" onchange="toggleCamposMaterial()">
                    <option value="VIDEO">Video (archivo)</option>
                    <option value="IMAGEN">Imagen</option>
                    <option value="ENLACE">Enlace YouTube/Vimeo</option>
                </select>
                <div id="campo_archivo" class="campo-archivo">
                    <label>Archivo</label>
                    <input type="file" name="archivo" accept="video/*,image/*">
                </div>
                <div id="campo_url" class="campo-url">
                    <label>URL</label>
                    <input type="url" name="url_video">
                </div>
                <button type="submit" class="btn btn-green">Publicar</button>
            </form>
        </section>

        <section class="card">
            <h3>Material (<?= count($materiales) ?>)</h3>
            <?php if (empty($materiales)): ?>
                <p>Sin material publicado.</p>
            <?php else: ?>
            <table>
                <thead><tr><th>#</th><th>Título</th><th>Tipo</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($materiales as $m): ?>
                    <?php $urlVer = urlPublicaMaterialVirtual($m['url_video'] ?? '', (int) ($m['id'] ?? 0)); ?>
                    <tr>
                        <td><?= e($m['orden']) ?></td>
                        <td><strong><?= e($m['titulo']) ?></strong><br><small><?= e(mb_strimwidth($m['descripcion'] ?? '', 0, 80, '...')) ?></small></td>
                        <td><?= e($m['tipo_media'] ?? '') ?></td>
                        <td>
                            <?php if ($urlVer): ?><a class="btn" href="<?= e($urlVer) ?>" target="_blank">Ver</a><?php endif; ?>
                            <a class="btn btn-muted" href="<?= e($ctrl) ?>?accion=eliminarMaterial&plan_id=<?= e($planId) ?>&id=<?= e($m['id']) ?>" onclick="return confirm('¿Eliminar?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
        <?php endif; ?>
        <?php endif; ?>

<script>
function toggleCamposMaterial() {
    var t = document.getElementById('tipo_media').value;
    document.getElementById('campo_archivo').style.display = t === 'ENLACE' ? 'none' : 'block';
    document.getElementById('campo_url').style.display = t === 'ENLACE' ? 'block' : 'none';
}
toggleCamposMaterial();
</script>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>
