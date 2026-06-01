<?php

session_start();

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/plan/planModel.php';
require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';
require_once __DIR__ . '/../models/pago/pagoModel.php';
require_once __DIR__ . '/../models/pago/comprobanteModel.php';
require_once __DIR__ . '/../models/comunicacion/notificacionModel.php';

$planModel = new PlanModel();

if ($planModel->contar() === 0) {
    $planModel->asegurarPlanesBase();
}

$planes = $planModel->obtenerActivos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $edad = trim($_POST['edad'] ?? '');
    $identificacion = trim($_POST['identificacion'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $modalidad = trim($_POST['modalidad'] ?? '');
    $tipoCuenta = trim($_POST['tipo_cuenta'] ?? '');
    $numeroCuenta = trim($_POST['numero_cuenta'] ?? '');

    $faltantes = [];

    if ($nombre === '') {
        $faltantes[] = 'nombre completo';
    }
    if ($edad === '') {
        $faltantes[] = 'edad';
    }
    if ($identificacion === '') {
        $faltantes[] = 'identificación';
    }
    if ($celular === '') {
        $faltantes[] = 'celular';
    }
    if ($planId < 1) {
        $faltantes[] = 'plan';
    }
    if ($tipoCuenta === '') {
        $faltantes[] = 'tipo de cuenta';
    }
    if ($numeroCuenta === '') {
        $faltantes[] = 'número de cuenta';
    }

    if ($faltantes !== []) {
        $_SESSION['alert_tipo'] = 'error';
        $_SESSION['alert'] = 'Completa: ' . implode(', ', $faltantes) . '.';
        header('Location: solicitud.php');
        exit;
    }

    if (empty($_FILES['comprobante']['name'])) {
        $_SESSION['alert_tipo'] = 'error';
        $_SESSION['alert'] = 'Debes adjuntar el comprobante de pago.';
        header('Location: solicitud.php');
        exit;
    }

    $plan = $planModel->obtenerPorId($planId);

    if (!$plan) {
        $_SESSION['alert_tipo'] = 'error';
        $_SESSION['alert'] = 'El plan seleccionado no existe o no está disponible.';
        header('Location: solicitud.php');
        exit;
    }

    if ($modalidad === '') {
        $modalidad = strtolower($plan['modalidad'] ?? 'virtual');
    }

    $urlComprobante = guardarComprobanteIngreso($_FILES['comprobante']);

    if (!$urlComprobante) {
        $_SESSION['alert_tipo'] = 'error';
        $_SESSION['alert'] = 'No se pudo guardar el comprobante. Intenta con otro archivo (JPG, PNG o PDF).';
        header('Location: solicitud.php');
        exit;
    }

    try {
        $solicitudModel = new SolicitudIngresoModel();
        $pagoModel = new PagoModel();
        $notificacionModel = new NotificacionModel();

        $solicitudId = $solicitudModel->crear([
            'nombre' => $nombre,
            'edad' => $edad,
            'identificacion' => $identificacion,
            'celular' => $celular,
            'plan_id' => $planId,
            'plan_interes' => $plan['nombre'] ?? (string) $planId,
            'modalidad' => $modalidad,
            'tipo_cuenta' => $tipoCuenta,
            'numero_cuenta' => $numeroCuenta,
            'url_comprobante' => $urlComprobante,
            'estado' => 'pendiente',
        ]);

        $pagoModel->crearDesdeSolicitud([
            'solicitud_id' => $solicitudId,
            'monto' => $plan['precio'] ?? 0,
            'url_comprobante' => $urlComprobante,
        ]);

        try {
            $comprobanteModel = new ComprobanteModel();
            $comprobanteModel->registrarAdjunto([
                'nombre_archivo' => $_FILES['comprobante']['name'],
                'ruta_archivo' => $_FILES['comprobante']['tmp_name'],
                'tipo_archivo' => $_FILES['comprobante']['type'] ?? '',
            ]);
        } catch (Throwable $e) {
            // Tabla comprobantes opcional; el archivo ya quedó en solicitud y pago.
        }

        try {
            $notificacionModel->notificarAdministrador(
                'Nueva solicitud de ingreso',
                $nombre . ' envió una solicitud pendiente de validación.'
            );
        } catch (Throwable $e) {
        }

        $solicitudModel->registrarTrazabilidad(null, 'Solicitud pública enviada por ' . $nombre);

        $_SESSION['alert_tipo'] = 'success';
        $_SESSION['alert'] = 'Solicitud enviada correctamente. El administrador validará tu pago antes de activar tu usuario.';
    } catch (Throwable $e) {
        $_SESSION['alert_tipo'] = 'error';
        $_SESSION['alert'] = 'Error al registrar la solicitud: ' . $e->getMessage();
    }

    header('Location: solicitud.php');
    exit;
}

require_once __DIR__ . '/../views/public/solicitud.php';
