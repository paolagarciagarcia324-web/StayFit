<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/plan/planModel.php';
require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';
require_once __DIR__ . '/../models/pago/pagoModel.php';

$planModel = new PlanModel();
$planes = $planModel->obtenerActivos();
$planId = (int) ($planes[0]['id_plan'] ?? 0);

if ($planId < 1) {
    echo "SKIP: no hay planes\n";
    exit(0);
}

$solicitudModel = new SolicitudIngresoModel();
$pagoModel = new PagoModel();

try {
    $id = $solicitudModel->crear([
        'nombre' => 'Test Usuario',
        'edad' => '28',
        'identificacion' => 'TEST' . time(),
        'celular' => '3000000000',
        'correo' => 'test' . time() . '@figuefit.test',
        'plan_id' => $planId,
        'modalidad' => 'virtual',
        'tipo_cuenta' => 'nequi',
        'numero_cuenta' => '3001112233',
        'estado' => 'pendiente',
    ]);

    $pagoModel->crearDesdeSolicitud([
        'solicitud_id' => $id,
        'plan_id' => $planId,
        'monto' => $planes[0]['precio'] ?? 0,
        'url_comprobante' => '/uploads/test.pdf',
        'metodo_pago' => 'nequi',
        'numero_cuenta' => '3001112233',
    ]);

    echo "OK solicitud_id=$id\n";
} catch (Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
    exit(1);
}
