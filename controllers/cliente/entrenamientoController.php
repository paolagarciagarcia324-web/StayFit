<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/entrenamiento/planEntrenamientoModel.php'; // Importa plan
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';

class ClienteEntrenamientoController
{
    private $clienteModel; // Modelo cliente
    private $planEntrenamientoModel; // Modelo entrenamiento
    private $rutinaModel; // Modelo rutinas
    private $videoModel;
    private $progresoVideoModel;
    private $planModel;
    private $programaVirtualModel;

    public function __construct()
    {
        session_start();
        $this->validarCliente();

        $this->clienteModel = new ClienteModel();
        $this->planEntrenamientoModel = new PlanEntrenamientoModel();
        $this->rutinaModel = new RutinaModel();
        $this->videoModel = new VideoModel();
        $this->progresoVideoModel = new ProgresoVideoModel();
        $this->planModel = new PlanModel();
        $this->programaVirtualModel = new ProgramaVirtualModel();
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId();

        $planEntrenamiento = $this->planEntrenamientoModel->obtenerPorCliente($clienteId);
        $rutinas = $this->rutinaModel->obtenerPorCliente($clienteId);
        $videos = $this->videoModel->obtenerPorCliente($clienteId);
        $avanceVirtual = $this->progresoVideoModel->obtenerAvanceCliente($clienteId);

        $planActivo = $this->planModel->obtenerPlanActivoCliente($clienteId);
        $programaVirtual = null;

        if (!empty($planActivo['id_plan'])) {
            $programaVirtual = $this->programaVirtualModel->obtenerPorPlan($planActivo['id_plan']);
        }

        if (!$programaVirtual && !empty($videos[0]['programa_descripcion'])) {
            $programaVirtual = [
                'nombre' => $videos[0]['programa_nombre'] ?? 'Programa virtual',
                'descripcion' => $videos[0]['programa_descripcion'] ?? '',
            ];
        }

        require_once __DIR__ . '/../../views/cliente/entrenamiento.php';
    }

    public function marcarRutina()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'rutina_id' => $_POST['rutina_id'], // ID rutina
                'estado' => $_POST['estado'], // Estado rutina
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->rutinaModel->registrarCumplimiento($datos); // Guarda cumplimiento

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Rutina actualizada por cliente'); // Registra historial
        }

        header('Location: entrenamientoController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda cliente

        return $cliente['id']; // Retorna ID
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'cliente') { // Valida cliente
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteEntrenamientoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
