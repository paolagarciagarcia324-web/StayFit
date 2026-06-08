<?php

if (!function_exists('e')) {
    function e($valor) {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('institucionEstadoBadge')) {
    function institucionEstadoBadge(?string $estado): array
    {
        $estado = strtolower(trim((string) $estado));

        return match ($estado) {
            'activa', 'activo' => [
                'class' => 'fp-badge fp-badge-ok',
                'label' => 'Activo',
            ],
            'inactiva', 'inactivo' => [
                'class' => 'fp-badge fp-badge-alert',
                'label' => 'Inactivo',
            ],
            default => [
                'class' => 'fp-badge fp-badge-pending',
                'label' => $estado !== '' ? ucfirst($estado) : 'Sin estado',
            ],
        };
    }
}

if (!function_exists('institucionFormatearFecha')) {
    function institucionFormatearFecha(?string $fecha): string
    {
        if ($fecha === null || trim($fecha) === '') {
            return 'No registrada';
        }

        try {
            $dt = new DateTime($fecha);

            return $dt->format('d/m/Y');
        } catch (Exception $e) {
            return $fecha;
        }
    }
}

$clienteInstitucional = $clienteInstitucional ?? [];
$institucion = $institucion ?? [];
$convenio = $convenio ?? [];

$nombreCliente = trim((string) ($clienteInstitucional['nombre_completo'] ?? $clienteInstitucional['nombre'] ?? $_SESSION['nombre'] ?? 'Cliente institucional'));
$institucionNombre = trim((string) ($institucion['nombre'] ?? $clienteInstitucional['institucion'] ?? ''));
$planConvenio = trim((string) ($convenio['tipo'] ?? $convenio['plan_nombre'] ?? ''));
$tieneInstitucion = $institucionNombre !== '' || !empty($institucion);
$tieneConvenio = !empty($convenio) && ($planConvenio !== '' || !empty($convenio['fecha_inicio']));

$estadoInstitucion = institucionEstadoBadge($institucion['estado'] ?? 'activo');
$estadoConvenio = institucionEstadoBadge($convenio['estado'] ?? 'activo');
$estadoCliente = institucionEstadoBadge($clienteInstitucional['estado'] ?? 'activo');

$inicialesInst = '';
foreach (preg_split('/\s+/', $institucionNombre) as $parte) {
    if ($parte !== '') {
        $inicialesInst .= mb_strtoupper(mb_substr($parte, 0, 1));
    }
    if (mb_strlen($inicialesInst) >= 2) {
        break;
    }
}
$inicialesInst = $inicialesInst !== '' ? $inicialesInst : 'IN';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Institución | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
.info {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .info strong {
            color: #D63384;
            display: block;
            margin-bottom: 6px;
        }

        

        .empty {
            color: #777;
            background: #f4f4f4;
            padding: 18px;
            border-radius: 16px;
        }
    </style>
</head>
<body class="fp-panel">

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <div class="fp-main-area">
        <header class="fp-topbar topbar">
            <div>
                <strong class="fp-topbar-role">Cliente institucional</strong>
                <p class="fp-topbar-name">Hola, <?= e($nombreCliente) ?></p>
            </div>
            <a class="logout" href="../../controllers/auth/logouthController.php">Cerrar sesión</a>
        </header>

        <main class="fp-content content">

            <section class="fp-hero hero page-header">
                <span class="fp-hero-tag">Convenio corporativo</span>
                <h1>Mi <span>institución</span></h1>
                <p>Consulta tu vínculo institucional, el convenio activo y los beneficios disponibles en FigueFit.</p>
            </section>

            <section class="fp-stats-premium">
                <article class="fp-stat-premium fp-stat-premium--fuchsia">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon fp-coach-avatar" aria-hidden="true"><?= e($inicialesInst) ?></div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:17px;line-height:1.35;">
                        <?= e($institucionNombre !== '' ? $institucionNombre : 'Sin institución') ?>
                    </p>
                    <p class="fp-stat-premium-label">Institución vinculada</p>
                </article>

                <article class="fp-stat-premium fp-stat-premium--mint">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:16px;line-height:1.35;">
                        <?= e($planConvenio !== '' ? $planConvenio : 'Sin plan') ?>
                    </p>
                    <p class="fp-stat-premium-label">Plan de convenio</p>
                </article>

                <article class="fp-stat-premium">
                    <div class="fp-stat-premium-head">
                        <div class="fp-stat-premium-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </div>
                    </div>
                    <p class="fp-stat-premium-value" style="font-size:15px;">
                        <?php if ($tieneConvenio): ?>
                            <span class="<?= e($estadoConvenio['class']) ?>"><?= e($estadoConvenio['label']) ?></span>
                        <?php else: ?>
                            <span class="fp-badge fp-badge-pending">Pendiente</span>
                        <?php endif; ?>
                    </p>
                    <p class="fp-stat-premium-label">Estado del convenio</p>
                </article>
            </section>

            <div class="fp-perfil-grid">
                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--fuchsia">
                        <h3>Información de la institución</h3>
                        <p>Datos de contacto y registro de la organización a la que perteneces.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <?php if (!$tieneInstitucion): ?>
                            <div class="fp-progreso-empty">
                                <div class="fp-progreso-empty-icon" aria-hidden="true">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                        <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M8 7h8M8 11h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <strong>Sin institución vinculada</strong>
                                <p>No tienes una organización asociada a tu cuenta en este momento.</p>
                            </div>
                        <?php else: ?>
                            <div class="fp-perfil-resumen">
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 7h8M8 11h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Nombre</strong>
                                        <span><?= e($institucion['nombre'] ?? $institucionNombre) ?></span>
                                    </div>
                                </div>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M7 7h10v10H7z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M9 11h6M9 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>NIT / Identificación</strong>
                                        <span><?= e($institucion['nit'] ?? 'No registrado') ?></span>
                                    </div>
                                </div>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M4 8l8 5 8-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Correo</strong>
                                        <span><?= e($institucion['correo'] ?? 'No registrado') ?></span>
                                    </div>
                                </div>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 4h12v14H6z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M9 21h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Teléfono</strong>
                                        <span><?= e($institucion['telefono'] ?? 'No registrado') ?></span>
                                    </div>
                                </div>
                            </div>
                            <p style="margin-top:18px;">
                                <span class="<?= e($estadoInstitucion['class']) ?>"><?= e($estadoInstitucion['label']) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="fp-card card fp-perfil-card">
                    <div class="fp-perfil-card-head fp-perfil-card-head--mint">
                        <h3>Convenio institucional</h3>
                        <p>Plan activo, vigencia y beneficios incluidos en tu membresía corporativa.</p>
                    </div>
                    <div class="fp-perfil-card-body">
                        <?php if (!$tieneConvenio): ?>
                            <div class="fp-progreso-empty">
                                <div class="fp-progreso-empty-icon" aria-hidden="true">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                                        <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <strong>Sin convenio registrado</strong>
                                <p>Aún no hay un plan de convenio asociado a tu cuenta institucional.</p>
                            </div>
                        <?php else: ?>
                            <div class="fp-perfil-resumen">
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 10h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Tipo de convenio</strong>
                                        <span><?= e($convenio['tipo'] ?? 'No definido') ?></span>
                                    </div>
                                </div>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 3v4M16 3v4M4 9h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Fecha de inicio</strong>
                                        <span><?= e(institucionFormatearFecha($convenio['fecha_inicio'] ?? null)) ?></span>
                                    </div>
                                </div>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M8 3v4M16 3v4M4 9h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M9 14l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Fecha de vencimiento</strong>
                                        <span><?= e(institucionFormatearFecha($convenio['fecha_fin'] ?? null)) ?></span>
                                    </div>
                                </div>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 2l3 6 6 .9-4.5 4.2 1 6-5.5-3.2-5.5 3.2 1-6L3 8.9 9 8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Beneficios</strong>
                                        <span><?= e($convenio['beneficios'] ?? 'No registrados') ?></span>
                                    </div>
                                </div>
                            </div>
                            <p style="margin-top:18px;">
                                <span class="<?= e($estadoConvenio['class']) ?>"><?= e($estadoConvenio['label']) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            </div>

            <article class="fp-card card fp-perfil-card" style="margin-top:24px;">
                <div class="fp-perfil-card-head fp-perfil-card-head--neutral">
                    <h3>Tu vinculación</h3>
                    <p>Resumen de tu cuenta como cliente institucional en la plataforma.</p>
                </div>
                <div class="fp-perfil-card-body">
                    <div class="fp-perfil-grid" style="margin:0;">
                        <div class="fp-perfil-resumen" style="grid-column:1/-1;">
                            <div class="fp-perfil-resumen-item">
                                <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M5 20c1.5-3 4-4.5 7-4.5s5.5 1.5 7 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Cliente</strong>
                                    <span><?= e($nombreCliente) ?></span>
                                </div>
                            </div>
                            <div class="fp-perfil-resumen-item">
                                <span class="fp-perfil-resumen-icon fp-perfil-resumen-icon--mint" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </span>
                                <div>
                                    <strong>Estado de vinculación</strong>
                                    <span><span class="<?= e($estadoCliente['class']) ?>"><?= e($estadoCliente['label']) ?></span></span>
                                </div>
                            </div>
                            <?php if (!empty($clienteInstitucional['cargo'])): ?>
                                <div class="fp-perfil-resumen-item">
                                    <span class="fp-perfil-resumen-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M8 7h8v12H8z" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M6 7h12M9 3h6v4H9z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <strong>Cargo</strong>
                                        <span><?= e($clienteInstitucional['cargo']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="fp-row-actions" style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
                        <a class="btn fp-btn-sm fp-btn-outline-mint" href="../../controllers/clienteIns/planController.php">Ver mi plan</a>
                        <a class="btn fp-btn-sm fp-btn-outline" href="../../controllers/clienteIns/perfilController.php">Editar perfil</a>
                    </div>
                </div>
            </article>

        </main>
    </div>
</div>
</body>
</html>
