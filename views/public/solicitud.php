<?php

if (!function_exists('e')) { // Evita duplicar función
    function e($valor) { // Limpia salida HTML
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); // Retorna texto seguro
    }
}

$planes = $planes ?? []; // Planes disponibles
$planSeleccionado = $_GET['plan_id'] ?? ''; // Plan recibido por URL
$alert = $_SESSION['alert'] ?? null; // Alerta de sesión
unset($_SESSION['alert']); // Limpia alerta

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <!-- Codificación -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Solicitud | StayFit</title> <!-- Título -->

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f7f7;
            color: #2D2D2D;
        }

        .navbar {
            padding: 18px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FFFFFF;
            box-shadow: 0 6px 20px rgba(45, 45, 45, 0.06);
        }

        .brand {
            color: #D63384;
            font-size: 28px;
            font-weight: 900;
            text-decoration: none;
        }

        .nav a {
            color: #2D2D2D;
            text-decoration: none;
            margin-left: 18px;
            font-weight: 700;
        }

        .nav .btn {
            background: #D63384;
            color: #FFFFFF;
            padding: 10px 16px;
            border-radius: 14px;
        }

        .page {
            min-height: calc(100vh - 80px);
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
        }

        .info {
            background: linear-gradient(135deg, #D63384, #2D2D2D);
            color: #FFFFFF;
            padding: 70px 8%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info h1 {
            font-size: 42px;
            margin: 0 0 16px;
        }

        .info p {
            line-height: 1.7;
            font-size: 17px;
        }

        .form-area {
            padding: 55px 8%;
        }

        .form-card {
            background: #FFFFFF;
            padding: 32px;
            border-radius: 26px;
            box-shadow: 0 12px 32px rgba(45, 45, 45, 0.10);
        }

        .form-card h2 {
            color: #D63384;
            margin-top: 0;
        }

        label {
            font-weight: 700;
            font-size: 14px;
        }

        input,
        select {
            width: 100%;
            padding: 13px;
            margin: 8px 0 16px;
            border: 1px solid #ddd;
            border-radius: 14px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            background: #D63384;
            color: #FFFFFF;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-weight: 900;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #b92b70;
        }

        .alert {
            background: #fff1f7;
            border-left: 5px solid #D63384;
            padding: 14px;
            border-radius: 14px;
            margin-bottom: 18px;
        }

        .note {
            background: #f6fffb;
            border-left: 5px solid #3EB489;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
        }

        @media (max-width: 900px) {
            .navbar {
                flex-direction: column;
                gap: 16px;
            }

            .page {
                grid-template-columns: 1fr;
            }

            .info h1 {
                font-size: 34px;
            }

            .nav a {
                margin: 6px;
                display: inline-block;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="brand" href="index.php">StayFit</a>

    <nav class="nav">
        <a href="index.php">Inicio</a>
        <a href="planPublico.php">Planes</a>
        <a href="solicitud.php">Inscripción</a>
        <a class="btn" href="../views/auth/login.php">Ingresar</a>
    </nav>
</header>

<main class="page">

    <section class="info">
        <h1>Solicitud de ingreso</h1>
        <p>
            Completa tus datos y adjunta el comprobante de pago.
            Tu usuario solo será activado cuando el administrador valide el pago.
        </p>
    </section>

    <section class="form-area">
        <div class="form-card">
            <h2>Formulario de inscripción</h2>

            <div class="note">
                Después de enviar esta solicitud quedarás en estado <strong>pendiente</strong>.
            </div>

            <?php if ($alert): ?>
                <div class="alert">
                    <?= e($alert) ?>
                </div>
            <?php endif; ?>

            <form action="solicitud.php" method="POST" enctype="multipart/form-data">

                <label>Nombre completo</label>
                <input type="text" name="nombre" required>

                <label>Edad</label>
                <input type="number" name="edad" min="12" required>

                <label>Número de identificación</label>
                <input type="text" name="identificacion" required>

                <label>Celular</label>
                <input type="text" name="celular" required>

                <label>Plan seleccionado</label>
                <select name="plan_id" required>
                    <option value="">Seleccione un plan</option>

                    <?php foreach ($planes as $plan): ?>
                        <option value="<?= e($plan['id'] ?? '') ?>" <?= ($planSeleccionado == ($plan['id'] ?? '')) ? 'selected' : '' ?>>
                            <?= e($plan['nombre'] ?? 'Plan') ?> - $<?= e($plan['precio'] ?? '0') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Modalidad</label>
                <select name="modalidad" required>
                    <option value="">Seleccione modalidad</option>
                    <option value="presencial">Presencial</option>
                    <option value="virtual">Virtual</option>
                    <option value="mixta">Mixta</option>
                </select>

                <label>Tipo de cuenta bancaria</label>
                <select name="tipo_cuenta" required>
                    <option value="">Seleccione una opción</option>
                    <option value="ahorros">Ahorros</option>
                    <option value="corriente">Corriente</option>
                    <option value="nequi">Nequi</option>
                    <option value="daviplata">Daviplata</option>
                </select>

                <label>Número de cuenta</label>
                <input type="text" name="numero_cuenta" required>

                <label>Comprobante de pago</label>
                <input type="file" name="comprobante" accept="image/*,.pdf" required>

                <button type="submit">Enviar solicitud</button>
            </form>
        </div>
    </section>

</main>

</body>
</html>