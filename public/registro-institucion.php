<?php

require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/../controllers/auth/registerInstitucionController.php';
    exit;
}

require_once __DIR__ . '/../models/institucion/enlaceInstitucionalModel.php';

$basePath = rutaBaseProyecto();

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$enlaceModel = new EnlaceInstitucionalModel();
$enlace = ($token !== '') ? $enlaceModel->obtenerPorToken($token) : null;

$errorEnlace = null;
if ($token === '') {
    $errorEnlace = 'No se proporcionó un enlace de registro válido.';
} elseif (!$enlace) {
    $errorEnlace = 'Este enlace no está disponible. Puede estar desactivado o la institución no está activa.';
}

require __DIR__ . '/../views/auth/registerInstitucion.php';
