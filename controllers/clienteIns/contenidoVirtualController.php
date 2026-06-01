<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';

class ClienteInsContenidoVirtualController
{
    private $videoModel;
    private $progresoVideoModel;
    private $planModel;
    private $programaVirtualModel;

    public function __construct()
    {
        session_start();
        $this->validarClienteInstitucional();

        $this->videoModel = new VideoModel();
        $this->progresoVideoModel = new ProgresoVideoModel();
        $this->planModel = new PlanModel();
        $this->programaVirtualModel = new ProgramaVirtualModel();
    }

    public function index()
    {
        $clienteId = (int) ($_SESSION['cliente_id'] ?? 0);
        $videos = $this->videoModel->obtenerPorCliente($clienteId);
        $avanceVirtual = $this->progresoVideoModel->obtenerAvanceCliente($clienteId);
        $planActivo = $this->planModel->obtenerPlanActivoCliente($clienteId);
        $programaVirtual = null;

        if (!empty($planActivo['id_plan'])) {
            $programaVirtual = $this->programaVirtualModel->obtenerPorPlan($planActivo['id_plan']);
        }

        $planEntrenamiento = null;
        $rutinas = [];

        require_once __DIR__ . '/../../views/clienteIns/entrenamiento.php';
    }

    public function marcarVisto()
    {
        $clienteId = (int) ($_SESSION['cliente_id'] ?? 0);
        $videoId = (int) ($_GET['video_id'] ?? 0);

        if ($videoId > 0 && $clienteId) {
            $this->progresoVideoModel->marcarVisto($clienteId, $videoId);
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? 'contenidoVirtualController.php';
        header('Location: ' . $ref);
        exit;
    }

    private function validarClienteInstitucional()
    {
        require_once __DIR__ . '/../../config/roles.php';

        if (!esClienteInstitucional()) {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new ClienteInsContenidoVirtualController();
$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
