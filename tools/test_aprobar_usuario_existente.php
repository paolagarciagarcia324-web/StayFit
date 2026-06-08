<?php

require_once __DIR__ . '/../models/solicitud/solicitudIngresoModel.php';
require_once __DIR__ . '/../models/usuario/usuarioModel.php';
require_once __DIR__ . '/../models/cliente/clienteModel.php';
require_once __DIR__ . '/../config/helpers.php';

$solicitudModel = new SolicitudIngresoModel();
$usuarioModel = new UsuarioModel();
$clienteModel = new ClienteModel();

$solicitud = $solicitudModel->obtenerPorId(2);
if (!$solicitud) {
    echo "SKIP: sin solicitud\n";
    exit(0);
}

$correo = $solicitud['correo'] ?? 'test@test.com';
$existente = $usuarioModel->obtenerPorCorreo($correo);
$partes = dividirNombreCompleto($solicitud['nombre'] ?? '');

if ($existente) {
    $usuarioModel->activarDesdeSolicitud((int) ($existente['id'] ?? $existente['id_usuario']), [
        'nombre' => $partes['nombre'],
        'apellido' => $partes['apellido'],
        'telefono' => $solicitud['celular'] ?? null,
        'documento_identidad' => $solicitud['identificacion'] ?? null,
    ]);
    echo "OK activarDesdeSolicitud\n";
} else {
    echo "SKIP: usuario no existe\n";
}

$clienteModel->crearDesdeSolicitud([
    'usuario_id' => (int) ($existente['id'] ?? 0),
    'edad' => $solicitud['edad'] ?? null,
    'tipo_cliente' => 'individual',
    'fecha_nacimiento' => edadAFechaNacimiento($solicitud['edad'] ?? null),
]);

$cliente = $clienteModel->obtenerPorUsuario((int) ($existente['id'] ?? 0));
echo $cliente ? "OK cliente id={$cliente['id']}\n" : "FAIL cliente\n";

$planId = $clienteModel->crearPlanClienteDesdeSolicitud((int) $cliente['id'], $solicitud);
echo $planId ? "OK plan_cliente={$planId}\n" : "FAIL plan\n";
