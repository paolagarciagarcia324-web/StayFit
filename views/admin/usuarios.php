<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('usuarioEstadoBadge')) {
    function usuarioEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'activo' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => 'Activo',
            ],
            'inactivo', 'suspendido' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => ucfirst($estado),
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => $estado !== '' ? ucfirst($estado) : 'Sin estado',
            ],
        };
    }
}

if (!function_exists('usuarioEsActivo')) {
    function usuarioEsActivo(?string $estado): bool
    {
        return strtolower(trim((string) $estado)) === 'activo';
    }
}

if (!function_exists('usuarioNombreMostrar')) {
    function usuarioNombreMostrar(array $item): string
    {
        $nombre = trim(($item['nombre'] ?? '') . ' ' . ($item['apellido'] ?? ''));

        return $nombre !== '' ? $nombre : 'Sin nombre';
    }
}

if (!function_exists('usuarioRolBadge')) {
    function usuarioRolBadge(?string $rol): array
    {
        $rolLower = strtolower(trim((string) $rol));

        if (str_contains($rolLower, 'admin')) {
            return ['class' => 'fp-badge fp-badge-alert', 'label' => 'Administrador'];
        }

        if (str_contains($rolLower, 'coach')) {
            return ['class' => 'fp-badge fp-badge-warn', 'label' => 'Coach'];
        }

        if (str_contains($rolLower, 'cliente')) {
            return ['class' => 'fp-badge', 'label' => 'Cliente'];
        }

        return [
            'class' => 'fp-tag-inline',
            'label' => $rol !== '' && $rol !== null ? ucfirst($rol) : 'Sin rol',
        ];
    }
}

if (!function_exists('usuarioEsAdmin')) {
    function usuarioEsAdmin(?string $rol): bool
    {
        $rolLower = strtolower(trim((string) $rol));

        return str_contains($rolLower, 'admin');
    }
}

$usuarios = $usuarios ?? [];
$roles = $roles ?? [];
$flash = $flash ?? null;
$adminSesionId = (int) ($_SESSION['usuario_id'] ?? 0);

$totalUsuarios = count($usuarios);
$totalActivos = count(array_filter($usuarios, fn($u) => usuarioEsActivo($u['estado'] ?? '')));
$totalAdmins = count(array_filter($usuarios, fn($u) => usuarioEsAdmin($u['rol'] ?? $u['rol_nombre'] ?? '')));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | FigueFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=12">
</head>
<body class="fp-panel">
<div class="admin-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarAdmin.php'; ?>

    <main class="content">

        <section class="page-header">
            <span class="fp-hero-tag">Control de accesos</span>
            <h1>Usuarios</h1>
            <p>Administra credenciales, roles y estados de acceso al sistema FigueFit.</p>
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
                            <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M5 20c0-3.3 2.7-5.5 7-5.5s7 2.2 7 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalUsuarios) ?></p>
                <p class="fp-stat-premium-label">Usuarios registrados</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--mint">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalActivos) ?></p>
                <p class="fp-stat-premium-label">Cuentas activas</p>
            </article>

            <article class="fp-stat-premium fp-stat-premium--warn">
                <div class="fp-stat-premium-head">
                    <div class="fp-stat-premium-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3l7 4v5c0 4-3 7-7 9-4-2-7-5-7-9V7l7-4z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <p class="fp-stat-premium-value"><?= e((string) $totalAdmins) ?></p>
                <p class="fp-stat-premium-label">Administradores</p>
            </article>
        </section>

        <section class="card fp-panel-unified">
            <div class="fp-panel-unified-head">
                <h3>Gestión de usuarios</h3>
            </div>

            <div class="fp-panel-form-block">
                <form class="fp-form-premium" action="../../controllers/admin/usuarioController.php?accion=guardar" method="POST" autocomplete="off">
                    <div class="fp-form-grid">
                        <div class="fp-field">
                            <label for="usr_nombre">Nombre</label>
                            <input type="text" id="usr_nombre" name="nombre" placeholder="Nombre completo" required autocomplete="name">
                        </div>

                        <div class="fp-field">
                            <label for="usr_correo">Correo</label>
                            <input type="email" id="usr_correo" name="correo" placeholder="usuario@correo.com" required autocomplete="off">
                        </div>

                        <div class="fp-field">
                            <label for="usr_password">Contraseña</label>
                            <input type="password" id="usr_password" name="password" minlength="6" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
                        </div>

                        <div class="fp-field">
                            <label for="usr_rol">Rol</label>
                            <select id="usr_rol" name="rol_id" required>
                                <option value="">Seleccione rol</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= e($rol['id'] ?? $rol['id_rol'] ?? '') ?>">
                                        <?= e($rol['nombre'] ?? 'Rol') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="fp-form-submit" style="max-width:220px;">Guardar usuario</button>
                </form>
            </div>

            <div class="fp-panel-list-block">
                <h4>Listado de usuarios</h4>

                <div class="fp-table-wrap">
                    <table class="fp-table-premium fp-table-fluid">
                        <thead>
                            <tr>
                                <th class="col-cliente">Usuario</th>
                                <th class="col-contacto">Correo</th>
                                <th style="width:16%;">Rol</th>
                                <th class="col-estado">Estado</th>
                                <th class="col-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr class="fp-empty-row">
                                    <td colspan="5">No hay usuarios registrados todavía.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($usuarios as $item): ?>
                                <?php
                                $estadoBadge = usuarioEstadoBadge($item['estado'] ?? '');
                                $rolBadge = usuarioRolBadge($item['rol'] ?? $item['rol_nombre'] ?? '');
                                $userId = (int) ($item['id'] ?? $item['id_usuario'] ?? 0);
                                $activo = usuarioEsActivo($item['estado'] ?? '');
                                $esYo = $userId === $adminSesionId;
                                ?>
                                <tr>
                                    <td>
                                        <div class="fp-cell-stack">
                                            <strong><?= e(usuarioNombreMostrar($item)) ?></strong>
                                            <?php if ($esYo): ?>
                                                <span class="fp-cell-highlight">Tu sesión actual</span>
                                            <?php elseif (!empty($item['telefono'])): ?>
                                                <span><?= e($item['telefono']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="fp-cell-highlight" style="font-size:13px;"><?= e($item['correo'] ?? '—') ?></span>
                                    </td>

                                    <td>
                                        <span class="<?= e($rolBadge['class']) ?>"><?= e($rolBadge['label']) ?></span>
                                    </td>

                                    <td>
                                        <span class="<?= e($estadoBadge['class']) ?>"><?= e($estadoBadge['label']) ?></span>
                                    </td>

                                    <td>
                                        <div class="fp-row-actions">
                                            <?php if ($activo): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline"
                                                   href="../../controllers/admin/usuarioController.php?accion=cambiarEstado&id=<?= e($userId) ?>&estado=inactivo"
                                                   style="border-color:rgba(255,47,160,0.35)!important;color:var(--fp-fuchsia)!important;">
                                                    Inactivar
                                                </a>
                                            <?php else: ?>
                                                <a class="btn fp-btn-sm btn-green"
                                                   href="../../controllers/admin/usuarioController.php?accion=cambiarEstado&id=<?= e($userId) ?>&estado=activo">
                                                    Activar
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!$esYo): ?>
                                                <a class="btn fp-btn-sm fp-btn-outline"
                                                   href="../../controllers/admin/usuarioController.php?accion=eliminar&id=<?= e($userId) ?>"
                                                   onclick="return confirm('¿Eliminar este usuario de forma permanente?');"
                                                   style="border-color:rgba(255,47,160,0.5)!important;color:#ff6bb5!important;">
                                                    Eliminar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>
</div>
</body>
</html>
