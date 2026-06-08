<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$clientes = $clientes ?? []; // Clientes asignados
$planes = $planes ?? []; // Planes de entrenamiento
$rutinas = $rutinas ?? []; // Rutinas creadas
$ejercicios = $ejercicios ?? []; // Ejercicios
$materiales = $materiales ?? []; // Materiales
$rutina = $rutina ?? null; // Rutina seleccionada
$tituloPagina = 'Entrenamientos Coach | FigueFit';
$vistaActiva = 'entrenamiento';

require __DIR__ . '/../partials/panel/coachShellOpen.php';

?>

        <section class="fp-hero hero page-header">
            <h1><span>Entrenamientos</span></h1>
            <p>Crea planes, rutinas, ejercicios y material de apoyo para tus clientas.</p>
        </section>

        <section class="grid">

            <div>
                <div class="card">
                    <h3>Crear plan</h3>

                    <form action="../../controllers/coach/entrenamientoController.php?accion=crearPlan" method="POST">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($cliente['nombre'] ?? 'Cliente') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre del plan</label>
                        <input type="text" name="nombre" required>

                        <label>Objetivo</label>
                        <textarea name="objetivo" required></textarea>

                        <label>Nivel</label>
                        <select name="nivel" required>
                            <option value="basico">Básico</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>

                        <label>Duración</label>
                        <input type="text" name="duracion" placeholder="Ej: 8 semanas" required>

                        <button type="submit">Crear plan</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Crear rutina</h3>

                    <form action="../../controllers/coach/rutinaController.php?accion=guardar" method="POST">
                        <label>Cliente</label>
                        <select name="cliente_id" required>
                            <option value="">Seleccione cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= e($cliente['id'] ?? '') ?>"><?= e($cliente['nombre'] ?? 'Cliente') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre</label>
                        <input type="text" name="nombre" required>

                        <label>Descripción</label>
                        <textarea name="descripcion" required></textarea>

                        <label>Nivel</label>
                        <select name="nivel" required>
                            <option value="basico">Básico</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>

                        <button type="submit">Crear rutina</button>
                    </form>
                </div>

                <div class="card">
                    <h3>Crear ejercicio</h3>

                    <form action="../../controllers/coach/ejercicioController.php?accion=guardar" method="POST">
                        <label>Rutina</label>
                        <select name="rutina_id" required>
                            <option value="">Seleccione rutina</option>
                            <?php foreach ($rutinas as $item): ?>
                                <option value="<?= e($item['id'] ?? '') ?>"><?= e($item['nombre'] ?? 'Rutina') ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label>Nombre</label>
                        <input type="text" name="nombre" required>

                        <label>Descripción</label>
                        <textarea name="descripcion" required></textarea>

                        <label>Series</label>
                        <input type="number" name="series" required>

                        <label>Repeticiones</label>
                        <input type="text" name="repeticiones" required>

                        <label>Descanso</label>
                        <input type="text" name="descanso" placeholder="Ej: 45 segundos" required>

                        <button type="submit">Guardar ejercicio</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="card">
                    <h3>Rutinas registradas</h3>

                    <?php if (empty($rutinas)): ?>
                        <div class="empty">No tienes rutinas registradas.</div>
                    <?php endif; ?>

                    <?php foreach ($rutinas as $item): ?>
                        <div class="box">
                            <strong><?= e($item['nombre'] ?? 'Rutina') ?></strong>
                            <p><?= e($item['descripcion'] ?? '') ?></p>
                            <span class="badge"><?= e($item['estado'] ?? 'activa') ?></span>
                            <br>
                            <a class="btn" href="../../controllers/coach/rutinaController.php?accion=detalle&id=<?= e($item['id'] ?? '') ?>">Ver detalle</a>
                            <a class="btn btn-green" href="../../controllers/coach/rutinaController.php?accion=cambiarEstado&id=<?= e($item['id'] ?? '') ?>&estado=finalizada">Finalizar</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <h3>Ejercicios</h3>

                    <?php if (empty($ejercicios)): ?>
                        <div class="empty">No hay ejercicios registrados.</div>
                    <?php endif; ?>

                    <?php foreach ($ejercicios as $ejercicio): ?>
                        <div class="box">
                            <strong><?= e($ejercicio['nombre'] ?? 'Ejercicio') ?></strong>
                            <p><?= e($ejercicio['descripcion'] ?? '') ?></p>
                            <p><strong>Series:</strong> <?= e($ejercicio['series'] ?? '') ?></p>
                            <p><strong>Repeticiones:</strong> <?= e($ejercicio['repeticiones'] ?? '') ?></p>
                            <span class="badge"><?= e($ejercicio['estado'] ?? 'activo') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($rutina): ?>
                    <div class="card">
                        <h3>Detalle de rutina</h3>
                        <p><strong><?= e($rutina['nombre'] ?? '') ?></strong></p>
                        <p><?= e($rutina['descripcion'] ?? '') ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </section>

<?php require __DIR__ . '/../partials/panel/coachShellClose.php'; ?>