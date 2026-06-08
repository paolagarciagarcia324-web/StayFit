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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenido virtual | StayFit Admin</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1">
<style>
        textarea { min-height: 90px; }
        .thumb { max-width: 120px; max-height: 70px; border-radius: 8px; object-fit: cover; }
        .tipo-enlace { background: var(--fp-fuchsia); }
        .campo-url, .campo-archivo { display: none; }
    </style>
</head>
<body class="fp-panel">
<div class="admin-wrapper">
    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">
        <section class="page-header">
            <h1>Biblioteca de contenido virtual</h1>
            <p>Sube videos, fotos o enlaces con descripción para cada plan virtual. Las clientas lo verán en su sección de entrenamiento.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h3>1. Seleccionar plan virtual</h3>
            <form method="GET" action="../../controllers/admin/contenidoVirtualController.php">
                <label>Plan</label>
                <select name="plan_id" onchange="this.form.submit()">
                    <option value="">— Elija un plan —</option>
                    <?php foreach ($planes as $p): ?>
                        <option value="<?= e($p['id_plan']) ?>" <?= $planId === (int) $p['id_plan'] ? 'selected' : '' ?>>
                            <?= e($p['nombre']) ?> (<?= e($p['modalidad'] ?? '') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </section>

        <?php if ($planId > 0 && $plan): ?>

        <section class="card">
            <h3>2. Programa del plan: <?= e($plan['nombre']) ?></h3>
            <form action="../../controllers/admin/contenidoVirtualController.php?accion=guardarPrograma" method="POST">
                <input type="hidden" name="plan_id" value="<?= e($planId) ?>">
                <label>Nombre del programa</label>
                <input type="text" name="nombre" required value="<?= e($programa['nombre'] ?? 'Programa ' . ($plan['nombre'] ?? '')) ?>">
                <label>Descripción del programa (visible para la clienta)</label>
                <textarea name="descripcion"><?= e($programa['descripcion'] ?? '') ?></textarea>
                <label>Nivel</label>
                <input type="text" name="nivel" value="<?= e($programa['nivel'] ?? 'General') ?>">
                <button type="submit" class="btn btn-green">Guardar programa</button>
            </form>
        </section>

        <?php if ($programa): ?>
        <div class="grid-2">
            <section class="card">
                <h3>3. Añadir material</h3>
                <form action="../../controllers/admin/contenidoVirtualController.php?accion=guardarMaterial" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="plan_id" value="<?= e($planId) ?>">
                    <label>Título de la lección</label>
                    <input type="text" name="titulo" required placeholder="Ej: Calentamiento día 1">
                    <label>Descripción / instrucciones</label>
                    <textarea name="descripcion" placeholder="Qué debe hacer la clienta, repeticiones, tips..."></textarea>
                    <label>Categoría (opcional)</label>
                    <select name="categoria_id">
                        <option value="">Sin categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= e($cat['id']) ?>"><?= e($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Orden</label>
                    <input type="number" name="orden" value="<?= count($materiales) + 1 ?>" min="1">
                    <label>Duración (minutos, opcional)</label>
                    <input type="number" name="duracion" min="1" placeholder="15">
                    <label>Tipo de contenido</label>
                    <select name="tipo_media" id="tipo_media" onchange="toggleCamposMaterial()">
                        <option value="VIDEO">Video (archivo)</option>
                        <option value="IMAGEN">Imagen / foto</option>
                        <option value="ENLACE">Enlace externo (YouTube, Vimeo)</option>
                    </select>
                    <div id="campo_archivo" class="campo-archivo">
                        <label>Subir archivo</label>
                        <input type="file" name="archivo" accept="video/*,image/*,.pdf">
                        <small>MP4, WEBM, JPG, PNG (máx. según servidor PHP)</small>
                    </div>
                    <div id="campo_url" class="campo-url">
                        <label>URL externa</label>
                        <input type="url" name="url_video" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <button type="submit" class="btn btn-green">Publicar material</button>
                </form>
            </section>

            <section class="card">
                <h3>Nueva categoría</h3>
                <form action="../../controllers/admin/contenidoVirtualController.php?accion=guardarCategoria" method="POST">
                    <input type="hidden" name="plan_id" value="<?= e($planId) ?>">
                    <input type="text" name="nombre" placeholder="Nombre categoría" required>
                    <textarea name="descripcion" placeholder="Descripción opcional"></textarea>
                    <button type="submit" class="btn">Crear categoría</button>
                </form>
            </section>
        </div>

        <section class="card">
            <h3>4. Material publicado (<?= count($materiales) ?>)</h3>
            <?php if (empty($materiales)): ?>
                <p>No hay material aún. Añade la primera lección arriba.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Preview</th>
                        <th>Título / descripción</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materiales as $m): ?>
                        <?php
                        $urlVer = urlPublicaMaterialVirtual($m['url_video'] ?? '', (int) ($m['id'] ?? 0));
                        $embed = embedUrlVideo($m['url_video'] ?? '');
                        ?>
                        <tr>
                            <td><?= e($m['orden'] ?? '') ?></td>
                            <td>
                                <?php if (($m['tipo_media'] ?? '') === 'IMAGEN' && $urlVer): ?>
                                    <img class="thumb" src="<?= e($urlVer) ?>" alt="">
                                <?php elseif ($embed): ?>
                                    <span class="badge tipo-enlace">Enlace</span>
                                <?php elseif ($urlVer): ?>
                                    <a class="btn btn-muted" href="<?= e($urlVer) ?>" target="_blank">Ver</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($m['titulo'] ?? '') ?></strong><br>
                                <small><?= e(mb_strimwidth($m['descripcion'] ?? '', 0, 120, '...')) ?></small>
                            </td>
                            <td><span class="badge"><?= e($m['tipo_media'] ?? 'VIDEO') ?></span></td>
                            <td>
                                <span class="badge <?= !empty($m['activo']) ? '' : 'off' ?>">
                                    <?= !empty($m['activo']) ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($urlVer): ?>
                                    <a class="btn" href="<?= e($urlVer) ?>" target="_blank">Abrir</a>
                                <?php endif; ?>
                                <?php if (!empty($m['activo'])): ?>
                                    <a class="btn btn-muted" href="../../controllers/admin/contenidoVirtualController.php?accion=cambiarEstadoMaterial&plan_id=<?= e($planId) ?>&id=<?= e($m['id']) ?>&estado=inactivo">Ocultar</a>
                                <?php else: ?>
                                    <a class="btn btn-green" href="../../controllers/admin/contenidoVirtualController.php?accion=cambiarEstadoMaterial&plan_id=<?= e($planId) ?>&id=<?= e($m['id']) ?>&estado=activo">Activar</a>
                                <?php endif; ?>
                                <a class="btn btn-muted" href="../../controllers/admin/contenidoVirtualController.php?accion=eliminarMaterial&plan_id=<?= e($planId) ?>&id=<?= e($m['id']) ?>" onclick="return confirm('¿Eliminar este material?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
        <?php else: ?>
            <section class="card">
                <p>Guarda el programa virtual arriba para poder añadir material.</p>
            </section>
        <?php endif; ?>

        <?php endif; ?>
    </main>
</div>
<script>
function toggleCamposMaterial() {
    var tipo = document.getElementById('tipo_media').value;
    document.getElementById('campo_archivo').style.display = (tipo === 'ENLACE') ? 'none' : 'block';
    document.getElementById('campo_url').style.display = (tipo === 'ENLACE') ? 'block' : 'none';
}
toggleCamposMaterial();
</script>
</body>
</html>
