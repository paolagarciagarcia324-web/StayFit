<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('nombreClienteCoach')) {
    function nombreClienteCoach(array $cliente): string
    {
        $nombre = trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
        if ($nombre === '' && !empty($cliente['nombre'])) {
            $nombre = trim((string) $cliente['nombre']);
        }

        return $nombre !== '' ? $nombre : 'Clienta';
    }
}

$clientes = $clientes ?? [];
$planesNutricionales = $planesNutricionales ?? [];
$comidas = $comidas ?? [];
$flash = $flash ?? null;

$tituloPagina = 'Nutrición Coach | FigueFit';
$vistaActiva = 'nutricion';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <span class="fp-hero-tag">Alimentación</span>
            <h1><span>Nutrición</span></h1>
            <p>Crea planes nutricionales y comidas personalizadas para tus clientas.</p>
        </section>

        <?php if (!empty($flash['mensaje'])): ?>
            <div class="<?= ($flash['tipo'] ?? '') === 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= e($flash['mensaje']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($clientes)): ?>
            <div class="fp-comunicacion-alert">
                <p>No tienes clientas asignadas. El administrador debe asignarte clientes desde <strong>Asignaciones</strong> antes de crear planes.</p>
            </div>
        <?php endif; ?>

        <div class="fp-perfil-grid">
            <article class="fp-card card fp-perfil-card">
                <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                    <h3>Crear plan nutricional</h3>
                    <p>Define el plan base para una clienta. Solo puede haber un plan activo por clienta.</p>
                </div>
                <div class="fp-perfil-card-body">
                    <form class="fp-form-premium" action="../../controllers/coach/nutricionController.php?accion=crearPlan" method="POST">
                        <div class="fp-field fp-field--full">
                            <label for="nutri-cliente">Clienta</label>
                            <select id="nutri-cliente" name="cliente_id" required <?= empty($clientes) ? 'disabled' : '' ?>>
                                <option value="">Seleccione clienta</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= e($cliente['id'] ?? $cliente['id_cliente'] ?? '') ?>">
                                        <?= e(nombreClienteCoach($cliente)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fp-field fp-field--full">
                            <label for="nutri-nombre">Nombre del plan</label>
                            <input type="text" id="nutri-nombre" name="nombre" placeholder="Ej: Plan definición marzo" required>
                        </div>
                        <div class="fp-field fp-field--full">
                            <label for="nutri-desc">Descripción / recomendaciones</label>
                            <textarea id="nutri-desc" name="descripcion" rows="3" placeholder="Hábitos, restricciones, notas generales"></textarea>
                        </div>
                        <div class="fp-field fp-field--full">
                            <label for="nutri-obj">Objetivo nutricional</label>
                            <textarea id="nutri-obj" name="objetivo" rows="2" placeholder="Ej: reducir grasa, ganar masa muscular" required></textarea>
                        </div>
                        <button type="submit" class="fp-form-submit fp-progreso-submit" <?= empty($clientes) ? 'disabled' : '' ?>>Crear plan</button>
                    </form>
                </div>
            </article>

            <article class="fp-card card fp-perfil-card">
                <div class="fp-perfil-card-head fp-perfil-card-head--mint">
                    <h3>Agregar comida</h3>
                    <p>Registra comidas dentro de un plan ya creado.</p>
                </div>
                <div class="fp-perfil-card-body">
                    <form class="fp-form-premium" action="../../controllers/coach/comidaController.php?accion=guardar" method="POST">
                        <div class="fp-field fp-field--full">
                            <label for="nutri-plan">Plan nutricional</label>
                            <select id="nutri-plan" name="plan_nutricional_id" required <?= empty($planesNutricionales) ? 'disabled' : '' ?>>
                                <option value="">Seleccione plan</option>
                                <?php foreach ($planesNutricionales as $plan): ?>
                                    <option value="<?= e($plan['id'] ?? $plan['id_plan_nutricional'] ?? '') ?>">
                                        <?= e($plan['nombre'] ?? 'Plan') ?><?= !empty($plan['cliente']) ? ' · ' . e($plan['cliente']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($planesNutricionales)): ?>
                                <span class="fp-field-hint">Primero crea un plan nutricional arriba.</span>
                            <?php endif; ?>
                        </div>
                        <div class="fp-form-grid">
                            <div class="fp-field">
                                <label for="nutri-comida-nombre">Nombre</label>
                                <input type="text" id="nutri-comida-nombre" name="nombre" placeholder="Ej: Desayuno proteico" required>
                            </div>
                            <div class="fp-field">
                                <label for="nutri-comida-tipo">Tipo</label>
                                <select id="nutri-comida-tipo" name="tipo_comida">
                                    <option value="DESAYUNO">Desayuno</option>
                                    <option value="ALMUERZO">Almuerzo</option>
                                    <option value="CENA">Cena</option>
                                    <option value="SNACK">Snack</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="fp-field fp-field--full">
                            <label for="nutri-comida-desc">Descripción</label>
                            <textarea id="nutri-comida-desc" name="descripcion" rows="2" placeholder="Alimentos y porciones sugeridas"></textarea>
                        </div>
                        <div class="fp-form-grid">
                            <div class="fp-field">
                                <label for="nutri-comida-hora">Hora sugerida</label>
                                <input type="time" id="nutri-comida-hora" name="hora">
                            </div>
                            <div class="fp-field">
                                <label for="nutri-comida-cal">Calorías aprox.</label>
                                <input type="number" id="nutri-comida-cal" name="calorias" min="0" step="1" placeholder="Opcional">
                            </div>
                        </div>
                        <button type="submit" class="fp-form-submit fp-perfil-submit-mint" <?= empty($planesNutricionales) ? 'disabled' : '' ?>>Guardar comida</button>
                    </form>
                </div>
            </article>
        </div>

        <div class="fp-perfil-grid" style="margin-top: 22px;">
            <article class="fp-card card">
                <div class="fp-plan-card-head">
                    <h3>Planes nutricionales</h3>
                    <p><?= e((string) count($planesNutricionales)) ?> plan(es) creado(s)</p>
                </div>
                <div class="fp-plan-card-body">
                    <?php if (empty($planesNutricionales)): ?>
                        <div class="fp-pagos-empty">
                            <strong>Sin planes</strong>
                            <p>Crea tu primer plan con el formulario de la izquierda.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($planesNutricionales as $plan): ?>
                            <div class="fp-timeline-item item">
                                <strong><?= e($plan['nombre'] ?? 'Plan nutricional') ?></strong>
                                <?php if (!empty($plan['cliente'])): ?>
                                    <p>Clienta: <?= e($plan['cliente']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($plan['descripcion'])): ?>
                                    <p><?= e($plan['descripcion']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($plan['objetivo'])): ?>
                                    <p><strong>Objetivo:</strong> <?= e($plan['objetivo']) ?></p>
                                <?php endif; ?>
                                <span class="fp-badge fp-badge-ok"><?= e($plan['estado'] ?? 'activo') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>

            <article class="fp-card card">
                <div class="fp-plan-card-head">
                    <h3>Comidas registradas</h3>
                    <p><?= e((string) count($comidas)) ?> comida(s)</p>
                </div>
                <div class="fp-plan-card-body">
                    <?php if (empty($comidas)): ?>
                        <div class="fp-pagos-empty">
                            <strong>Sin comidas</strong>
                            <p>Agrega comidas a un plan existente.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comidas as $comida): ?>
                            <div class="fp-timeline-item item">
                                <strong><?= e($comida['nombre'] ?? $comida['tiempo_comida'] ?? 'Comida') ?></strong>
                                <?php if (!empty($comida['plan_nutricional'])): ?>
                                    <p>Plan: <?= e($comida['plan_nutricional']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($comida['descripcion'])): ?>
                                    <p><?= e($comida['descripcion']) ?></p>
                                <?php endif; ?>
                                <p>
                                    <?php if (!empty($comida['hora'])): ?>
                                        <span class="fp-tag-inline">Hora: <?= e($comida['hora']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($comida['calorias'])): ?>
                                        <span class="fp-tag-inline"><?= e($comida['calorias']) ?> kcal</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
        </div>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>
