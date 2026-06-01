<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/plan/planModel.php';
require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';
require_once __DIR__ . '/../models/pago/pagoModel.php';

$planModel = new PlanModel();
if ($planModel->contar() === 0) {
    $planModel->asegurarPlanesBase();
}

$plan = $planModel->obtenerActivos()[0];
$planId = (int) ($plan['id_plan'] ?? 0);

$tmp = sys_get_temp_dir() . '/test_comprobante.txt';
file_put_contents($tmp, 'comprobante prueba');

$solicitudModel = new SolicitudIngresoModel();
$pagoModel = new PagoModel();

$url = 'public/uploads/comprobantes/test_manual.txt';
@mkdir(dirname(__DIR__) . '/' . dirname($url), 0777, true);
copy($tmp, dirname(__DIR__) . '/' . $url);

$id = $solicitudModel->crear([
    'nombre' => 'Usuario Prueba',
    'edad' => 25,
    'identificacion' => '999888777',
    'celular' => '3001234567',
    'plan_interes' => $plan['nombre'],
    'plan_id' => $planId,
    'modalidad' => 'mixta',
    'tipo_cuenta' => 'nequi',
    'numero_cuenta' => '3001112233',
    'url_comprobante' => $url,
    'estado' => 'pendiente',
]);

$pagoModel->crearDesdeSolicitud([
    'solicitud_id' => $id,
    'monto' => $plan['precio'],
    'url_comprobante' => $url,
]);

echo "Solicitud creada ID: {$id}\n";
echo 'Total solicitudes: ' . count($solicitudModel->obtenerTodas()) . "\n";
