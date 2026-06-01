<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/plan/planModel.php';
require_once __DIR__ . '/../models/contenidoVirtual/programaVirtualModel.php';
require_once __DIR__ . '/../models/contenidoVirtual/videoModel.php';

$planModel = new PlanModel();
$programaModel = new ProgramaVirtualModel();
$videoModel = new VideoModel();

$planes = $planModel->obtenerPlanesVirtuales();

if (empty($planes)) {
    die("No hay planes virtuales activos.\n");
}

$plan = $planes[0];
$planId = (int) $plan['id_plan'];

$programa = $programaModel->obtenerOcrearPorPlan($planId, $plan['nombre']);

$programaModel->actualizar([
    'id' => $programa['id'],
    'nombre' => 'Programa demo ' . $plan['nombre'],
    'descripcion' => 'Bienvenida al plan virtual. Completa cada lección en orden y marca como completado al terminar.',
    'nivel' => 'General',
    'activo' => 1,
]);

$existentes = $videoModel->obtenerPorPrograma($programa['id'], false);

if (count($existentes) > 0) {
    echo "El programa ya tiene " . count($existentes) . " materiales. No se sembró de nuevo.\n";
    exit;
}

$lecciones = [
    [
        'titulo' => 'Bienvenida al programa',
        'descripcion' => 'Conoce la estructura del plan, cómo usar la plataforma y la frecuencia recomendada de entrenamiento.',
        'url_video' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
        'tipo_media' => 'ENLACE',
        'orden' => 1,
    ],
    [
        'titulo' => 'Calentamiento y movilidad',
        'descripcion' => '10 minutos de activación: círculos de cadera, rotaciones de hombro y sentadillas sin peso.',
        'url_video' => 'https://www.youtube.com/watch?v=ZXsQAXx_ao0',
        'tipo_media' => 'ENLACE',
        'orden' => 2,
    ],
    [
        'titulo' => 'Rutina base — semana 1',
        'descripcion' => '3 series de 12 repeticiones: sentadilla, puente de glúteo y plancha. Descansa 45 s entre series.',
        'url_video' => 'https://www.youtube.com/watch?v=ml6cT4AZdqI',
        'tipo_media' => 'ENLACE',
        'orden' => 3,
    ],
];

foreach ($lecciones as $leccion) {
    $videoModel->crear([
        'programa_virtual_id' => $programa['id'],
        'titulo' => $leccion['titulo'],
        'descripcion' => $leccion['descripcion'],
        'url_video' => $leccion['url_video'],
        'tipo_media' => $leccion['tipo_media'],
        'orden' => $leccion['orden'],
        'activo' => 1,
    ]);
    echo "Creado: {$leccion['titulo']}\n";
}

echo "Semilla completada para plan ID {$planId} ({$plan['nombre']}).\n";
