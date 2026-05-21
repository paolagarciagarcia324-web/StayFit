<?php

session_start(); // Inicia sesión

require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php'; // Importa solicitudes
require_once __DIR__ . '/../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../models/pago/comprobanteModel.php'; // Importa comprobantes
require_once __DIR__ . '/../models/plan/planModel.php'; // Importa planes

$solicitudModel = new SolicitudIngresoModel(); // Instancia solicitudes
$pagoModel = new PagoModel(); // Instancia pagos
$comprobanteModel = new ComprobanteModel(); // Instancia comprobantes
$planModel = new PlanModel(); // Instancia planes

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Valida si no es envío
    $planes = $planModel->obtenerActivos(); // Obtiene planes activos
    require_once __DIR__ . '/../views/public/solicitud.php'; // Carga formulario
    exit; // Detiene ejecución
}

$datos = [
    'nombre' => trim($_POST['nombre'] ?? ''), // Nombre del interesado
    'edad' => $_POST['edad'] ?? '', // Edad
    'identificacion' => trim($_POST['identificacion'] ?? ''), // Documento
    'celular' => trim($_POST['celular'] ?? ''), // Celular
    'plan_id' => $_POST['plan_id'] ?? null, // Plan seleccionado
    'modalidad' => $_POST['modalidad'] ?? '', // Modalidad elegida
    'tipo_cuenta' => trim($_POST['tipo_cuenta'] ?? ''), // Tipo de cuenta
    'numero_cuenta' => trim($_POST['numero_cuenta'] ?? ''), // Número de cuenta
    'estado' => 'pendiente' // Estado inicial
];

if (empty($datos['nombre']) || empty($datos['identificacion']) || empty($datos['celular']) || empty($datos['plan_id'])) { // Valida campos
    $_SESSION['alert'] = 'Debe completar los datos obligatorios'; // Guarda alerta
    header('Location: solicitud.php'); // Redirige al formulario
    exit; // Detiene ejecución
}

$solicitudId = $solicitudModel->crear($datos); // Crea solicitud pendiente

$pago = [
    'solicitud_id' => $solicitudId, // Relaciona solicitud
    'plan_id' => $datos['plan_id'], // Plan comprado
    'tipo_cuenta' => $datos['tipo_cuenta'], // Tipo de cuenta
    'numero_cuenta' => $datos['numero_cuenta'], // Número de cuenta
    'estado' => 'pendiente' // Pago pendiente
];

$pagoId = $pagoModel->crearDesdeSolicitud($pago); // Crea pago pendiente

if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === 0) { // Valida comprobante
    $comprobante = [
        'pago_id' => $pagoId, // Relaciona pago
        'nombre_archivo' => $_FILES['comprobante']['name'], // Nombre archivo
        'ruta_archivo' => $_FILES['comprobante']['tmp_name'], // Ruta temporal
        'tipo_archivo' => $_FILES['comprobante']['type'] // Tipo archivo
    ];

    $comprobanteModel->crear($comprobante); // Guarda comprobante
}

$solicitudModel->registrarTrazabilidad(null, 'Solicitud pública enviada'); // Registra trazabilidad

$_SESSION['alert'] = 'Solicitud enviada correctamente. El administrador validará el pago.'; // Mensaje final

header('Location: solicitud.php'); // Redirige al formulario
exit; // Detiene ejecución

?>