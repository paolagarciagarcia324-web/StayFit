<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/cliente/clienteModel.php';
require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';

$db = (new Database())->conectar();
$clienteModel = new ClienteModel();
$solicitudModel = new SolicitudIngresoModel();

$cliente = $db->query('SELECT id_cliente FROM clientes ORDER BY id_cliente DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$solicitud = $solicitudModel->obtenerPorId(2);

if (!$cliente || !$solicitud) {
    echo "SKIP: falta cliente o solicitud\n";
    exit(0);
}

try {
    $id = $clienteModel->crearPlanClienteDesdeSolicitud((int) $cliente['id_cliente'], $solicitud);
    echo $id ? "OK planes_cliente ID: {$id}\n" : "FALLO crear plan\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
