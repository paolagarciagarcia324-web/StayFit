<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$cliente = $cliente ?? []; // Datos cliente institucional
$usuario = $usuario ?? []; // Datos usuario
$datosFisicos = $datosFisicos ?? []; // Datos físicos

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Perfil Institucional | StayFit</title>
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

    <?php require __DIR__ . '/../partials/panel/sidebarClienteIns.php'; ?>

    <main class="content">

        <section class="page-header">
            <h1>Mi perfil institucional</h1>
            <p>Actualiza tus datos personales y físicos para mantener una trazabilidad clara de tu proceso.</p>
        </section>

        <section class="grid">

            <div class="card">
                <h3>Datos personales</h3>

                <form action="../../controllers/clienteIns/perfilController.php?accion=actualizar" method="POST">
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

                <form action="../../controllers/clienteIns/progresoController.php?accion=registrar" method="POST" enctype="multipart/form-data">
                    <label>Peso actual</label>
                    <input type="number" step="0.1" name="peso" value="<?= e($datosFisicos['peso'] ?? '') ?>" required>

                    <label>Medidas corporales</label>
                    <textarea name="medidas" placeholder="Ej: cintura, cadera, pierna, brazo"></textarea>

                    <label>Observación</label>
                    <textarea name="observacion" placeholder="Registra cómo te has sentido en el proceso"></textarea>

                    <label>Foto de progreso</label>
                    <input type="file" name="foto" accept="image/*">

                    <button class="btn-green" type="submit">Guardar progreso</button>
                </form>
            </div>

        </section>

        <section class="card" style="margin-top: 24px;">
            <h3>Resumen institucional</h3>

            <div class="info-box">
                <strong>Tipo de cliente:</strong>
                <?= e($cliente['tipo_cliente'] ?? 'institucional') ?>
            </div>

            <div class="info-box">
                <strong>Estado:</strong>
                <?= e($cliente['estado'] ?? 'activo') ?>
            </div>

            <div class="info-box">
                <strong>Objetivo actual:</strong>
                <?= e($datosFisicos['objetivo'] ?? 'Aún no registrado') ?>
            </div>
        </section>

    </main>
</div>
</body>
</html>