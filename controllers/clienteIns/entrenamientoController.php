<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/entrenamiento/planEntrenamientoModel.php'; // Importa plan entrenamiento
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';

class ClienteInsEntrenamientoController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $planEntrenamientoModel; // Modelo entrenamiento
    private $rutinaModel; // Modelo rutina
    private $videoModel;
    private $progresoVideoModel;
    private $planModel;
    private $programaVirtualModel;

    public function __construct()
    {
        session_start();
        $this->validarClienteInstitucional();

        $this->clienteInsModel = new ClienteInsModel();
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

        require_once __DIR__ . '/../../views/clienteIns/entrenamiento.php';
    }

    public function marcarRutina()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'rutina_id' => $_POST['rutina_id'], // ID rutina
                'estado' => $_POST['estado'], // Estado
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->rutinaModel->registrarCumplimiento($datos); // Guarda cumplimiento

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Rutina institucional actualizada'); // Registra historial
        }

        header('Location: entrenamientoController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteInsModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda cliente

        return $cliente['id']; // Retorna ID
    }

    private function validarClienteInstitucional()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'clienteins' && $rol !== 'cliente_institucional') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteInsEntrenamientoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>