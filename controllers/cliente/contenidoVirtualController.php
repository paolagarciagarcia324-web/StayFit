<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';

class ClienteContenidoVirtualController
{
    private $clienteModel;
    private $videoModel;
    private $progresoVideoModel;
    private $planModel;
    private $programaVirtualModel;

    public function __construct()
    {
        session_start();
        $this->validarCliente();

        $this->clienteModel = new ClienteModel();
        $this->videoModel = new VideoModel();
        $this->progresoVideoModel = new ProgresoVideoModel();
        $this->planModel = new PlanModel();
        $this->programaVirtualModel = new ProgramaVirtualModel();
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId();
        $videos = $this->videoModel->obtenerPorCliente($clienteId);
        $avanceVirtual = $this->progresoVideoModel->obtenerAvanceCliente($clienteId);
        $planActivo = $this->planModel->obtenerPlanActivoCliente($clienteId);
        $programaVirtual = null;

        if (!empty($planActivo['id_plan'])) {
            $programaVirtual = $this->programaVirtualModel->obtenerPorPlan($planActivo['id_plan']);
        }

        $planEntrenamiento = null;
        $rutinas = [];

        require_once __DIR__ . '/../../views/cliente/entrenamiento.php';
    }

    public function marcarVisto()
    {
        $clienteId = $this->obtenerClienteId();
        $videoId = (int) ($_GET['video_id'] ?? 0);

        if ($videoId > 0 && $clienteId) {
            $this->progresoVideoModel->marcarVisto($clienteId, $videoId);
            $this->progresoVideoModel->registrarTrazabilidad(
                $_SESSION['usuario_id'] ?? null,
                'Video completado (ID ' . $videoId . ')'
            );
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? 'entrenamientoController.php';

        header('Location: ' . $ref);
        exit;
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) {
            return (int) $_SESSION['cliente_id'];
        }

        $cliente = $this->clienteModel->obtenerPorUsuario($_SESSION['usuario_id'] ?? 0);
        $_SESSION['cliente_id'] = $cliente['id'] ?? $cliente['id_cliente'] ?? null;

        return (int) $_SESSION['cliente_id'];
    }

    private function validarCliente()
    {
        if (strtolower($_SESSION['rol'] ?? '') !== 'cliente') {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new ClienteContenidoVirtualController();
$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
