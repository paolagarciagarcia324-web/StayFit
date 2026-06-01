<?php
require_once __DIR__ . '/../models/comunicacion/mensajeModel.php';
require_once __DIR__ . '/../models/cliente/clienteModel.php';

$cm = new ClienteModel();
$mm = new MensajeModel();

$clienteId = 7;
$coachId = $cm->obtenerIdCoachAsignado($clienteId);
echo "Cliente {$clienteId}, coach {$coachId}\n";

try {
    $ok = $mm->crear([
        'cliente_id' => $clienteId,
        'usuario_id' => $clienteId,
        'mensaje' => 'Hola coach, prueba',
    ]);
    echo $ok ? "Mensaje OK\n" : "Mensaje FALLO\n";
} catch (Throwable $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
