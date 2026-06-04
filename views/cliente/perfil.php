<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$cliente = $cliente ?? []; // Datos del cliente
$usuario = $usuario ?? []; // Datos del usuario
$datosFisicos = $datosFisicos ?? []; // Datos físicos
$cuenta = $cuenta ?? []; // Datos de cuenta (cuentaController)

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Perfil | StayFit</title>
    <link rel="stylesheet" href="../../public/panel.css?v=1"> <!-- Título -->

    <style>
input,
        textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ddd;
            border-radius: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        button {
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 13px 18px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        

        .info-box {
            background: #fff7fb;
            border-left: 5px solid #D63384;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 14px;
        }

        .info-box strong {
            color: #D63384;
        }
    </style>
</head>

<body class="fp-panel">
<div class="cliente-wrapper">

    <?php require __DIR__ . '/../partials/panel/sidebarCliente.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Mi perfil</h1>
            <p>Actualiza tus datos personales, físicos y de acceso para mantener tu información al día.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Datos personales</h3>

                <form action="../../controllers/cliente/perfilController.php?accion=actualizar" method="POST">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?= e($usuario['nombre'] ?? $cliente['nombre'] ?? '') ?>" required>

                    <label>Correo</label>
                    <input type="email" name="correo" value="<?= e($usuario['correo'] ?? '') ?>" required>

                    <label>Identificación</label>
                    <input type="text" name="identificacion" value="<?= e($cliente['identificacion'] ?? '') ?>" required>

                    <label>Edad</label>
                    <input type="number" name="edad" value="<?= e($cliente['edad'] ?? '') ?>" required>

                    <label>Celular</label>
                    <input type="text" name="celular" value="<?= e($cliente['celular'] ?? '') ?>" required>

                    <button type="submit">Actualizar perfil</button>
                </form>
            </div>

            <div class="card">
                <h3>Datos físicos</h3>

                <form action="../../controllers/cliente/datosFisicosController.php?accion=actualizar" method="POST">
                    <label>Peso (kg)</label>
                    <input type="number" step="0.1" name="peso" value="<?= e($datosFisicos['peso'] ?? '') ?>" required>

                    <label>Estatura (cm)</label>
                    <input type="number" step="0.1" name="estatura" value="<?= e($datosFisicos['estatura'] ?? '') ?>" required>

                    <label>Objetivo</label>
                    <textarea name="objetivo" placeholder="Ej: tonificar, bajar grasa, ganar resistencia" required><?= e($datosFisicos['objetivo'] ?? '') ?></textarea>

                    <label>Restricciones</label>
                    <textarea name="restricciones" placeholder="Lesiones, alergias o limitaciones"><?= e($datosFisicos['restricciones'] ?? '') ?></textarea>

                    <label>Observaciones</label>
                    <textarea name="observaciones" placeholder="Comentarios adicionales para tu coach"><?= e($datosFisicos['observaciones'] ?? '') ?></textarea>

                    <button class="btn-green" type="submit">Guardar datos físicos</button>
                </form>
            </div>

        </section>

        <section class="grid" style="margin-top: 24px;">

            <div class="card">
                <h3>Cambiar contraseña</h3>

                <form action="../../controllers/cliente/cuentaController.php?accion=cambiarPassword" method="POST">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password" minlength="6" required>

                    <button type="submit">Actualizar contraseña</button>
                </form>
            </div>

            <div class="card">
                <h3>Resumen de cuenta</h3>

                <div class="info-box">
                    <strong>Correo:</strong>
                    <?= e($usuario['correo'] ?? $cuenta['correo'] ?? '') ?>
                </div>

                <div class="info-box">
                    <strong>Estado:</strong>
                    <?= e($cliente['estado'] ?? $usuario['estado'] ?? 'activo') ?>
                </div>

                <div class="info-box">
                    <strong>Objetivo actual:</strong>
                    <?= e($datosFisicos['objetivo'] ?? 'Aún no registrado') ?>
                </div>
            </div>

        </section>

    </main>
</div>
</body>
</html>
