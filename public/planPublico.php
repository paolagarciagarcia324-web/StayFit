<?php

session_start(); // Inicia sesión

require_once __DIR__ . '/../models/plan/planModel.php'; // Modelo de planes
require_once __DIR__ . '/../models/contenidoVirtual/programaVirtualModel.php'; // Modelo programas virtuales

$planModel = new PlanModel(); // Instancia planes
$programaModel = new ProgramaVirtualModel(); // Instancia programas

$planes = $planModel->obtenerActivos(); // Obtiene planes activos
$programas = $programaModel->obtenerActivos(); // Obtiene programas activos

require_once __DIR__ . '/../views/public/planes.php'; // Carga vista de planes

?>