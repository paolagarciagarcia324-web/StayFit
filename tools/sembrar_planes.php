<?php

require_once __DIR__ . '/../models/plan/planModel.php';

$planModel = new PlanModel();
echo 'Planes antes: ' . $planModel->contar() . "\n";
$planModel->asegurarPlanesBase();
echo 'Planes después: ' . $planModel->contar() . "\n";
