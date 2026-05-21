<?php

session_start(); // Inicia sesión

require_once __DIR__ . '/../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../models/plan/programaModel.php'; // Importa programas

$planModel = new PlanModel(); // Instancia planes
$programaModel = new ProgramaModel(); // Instancia programas

$planes = $planModel->obtenerActivos(); // Obtiene planes activos
$programas = $programaModel->obtenerActivos(); // Obtiene programas activos

require_once __DIR__ . '/../views/public/welcome.php'; // Carga bienvenida pública

?>