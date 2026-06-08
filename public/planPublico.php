<?php

session_start();

require_once __DIR__ . '/../models/plan/planModel.php';

$planModel = new PlanModel();
$planesPublicos = $planModel->obtenerActivos();
usort($planesPublicos, static function ($a, $b) {
    return (int) ($b['id_plan'] ?? 0) <=> (int) ($a['id_plan'] ?? 0);
});
$totalPlanesPublicos = count($planesPublicos);

require_once __DIR__ . '/../views/public/planes.php';
