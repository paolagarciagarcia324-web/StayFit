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
    <title>Perfil | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .cliente-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            background: #2D2D2D;
            color: #FFFFFF;
            padding: 28px 20px;
        }

        .sidebar h2 {
            color: #D63384;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #D63384;
        }

        .content {
            flex: 1;
            padding: 34px;
        }

        .page-header {
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            border-radius: 24px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 28px rgba(45, 45, 45, 0.08);
        }

        .card h3 {
            margin-top: 0;
            color: #D63384;
        }

        label {
            font-weight: 700;
            font-size: 14px;
        }

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

        .btn-green {
            background: #3EB489;
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

        @media (max-width: 900px) {
            .cliente-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="cliente-wrapper">

    <aside class="sidebar">
        <h2>StayFit</h2>
        <a href="../../controller/cliente/dashboardController.php">Dashboard</a>
        <a class="active" href="../../controller/cliente/perfilController.php">Perfil</a>
        <a href="../../controller/cliente/planController.php">Mi plan</a>
        <a href="../../controller/cliente/entrenamientoController.php">Entrenamiento</a>
        <a href="../../controller/cliente/nutricionController.php">Nutrición</a>
        <a href="../../controller/cliente/progresoController.php">Progreso</a>
        <a href="../../controller/cliente/calendarioController.php">Calendario</a>
        <a href="../../controller/cliente/pagoController.php">Pagos</a>
        <a href="../../controller/auth/logouthController.php">Cerrar sesión</a>
    </aside>

    <main class="content">

        <section class="page-header">
            <h1>Mi perfil</h1>
            <p>Actualiza tus datos personales, físicos y de acceso para mantener tu información al día.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Datos personales</h3>

                <form action="../../controller/cliente/perfilController.php?accion=actualizar" method="POST">
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

                <form action="../../controller/cliente/datosFisicosController.php?accion=actualizar" method="POST">
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

                <form action="../../controller/cliente/cuentaController.php?accion=cambiarPassword" method="POST">
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
