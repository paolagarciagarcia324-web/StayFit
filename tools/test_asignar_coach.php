<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/cliente/clienteModel.php';

$db = (new Database())->conectar();
$coach = $db->query('SELECT id_coach FROM coach LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$cliente = $db->query('SELECT id_cliente FROM cliente LIMIT 1')->fetch(PDO::FETCH_ASSOC);

$coachId = (int) ($coach['id_coach'] ?? 0);
$clienteId = (int) ($cliente['id_cliente'] ?? 0);

echo "Cliente ID: {$clienteId}, Coach ID: {$coachId}\n";

$model = new ClienteModel();
$ok = $model->asignarCoach($clienteId, $coachId);
echo $ok ? "Asignación OK\n" : "Asignación FALLO\n";

$asig = $model->obtenerAsignaciones();
echo 'Historial: ' . count($asig) . " filas\n";
print_r($asig);
