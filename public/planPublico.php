<?php

session_start(); // Inicia sesión

require_once __DIR__ . '/../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../models/plan/programaModel.php'; // Importa programas

$planModel = new PlanModel(); // Instancia planes
$programaModel = new ProgramaModel(); // Instancia programas

$planes = $planModel->obtenerActivos(); // Obtiene planes disponibles
$programas = $programaModel->obtenerActivos(); // Obtiene programas disponibles

require_once __DIR__ . '/../views/public/planes.php'; // Carga vista pública de planes

?>