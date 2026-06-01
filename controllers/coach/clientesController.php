<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso

class CoachClientesController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $planModel; // Modelo plan
    private $progresoModel; // Modelo progreso

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->planModel = new PlanModel(); // Instancia plan
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Obtiene clientes asignados

        require_once __DIR__ . '/../../views/coach/clientes.php'; // Carga vista
    }

    public function detalle()
    {
        $clienteId = $_GET['id'] ?? null; // Obtiene cliente

        if (!$clienteId) { // Valida cliente
            header('Location: clientesController.php'); // Redirige
            exit; // Detiene ejecución
        }

        $cliente = $this->clienteModel->obtenerPorId($clienteId); // Obtiene datos
        $plan = $this->planModel->obtenerPlanActivoCliente($clienteId); // Obtiene plan
        $progreso = $this->progresoModel->obtenerPorCliente($clienteId); // Obtiene progreso

        require_once __DIR__ . '/../../views/coach/clientes.php'; // Carga vista
    }

    private function obtenerCoachId()
    {
        if (isset($_SESSION['coach_id'])) { // Verifica sesión
            return $_SESSION['coach_id']; // Retorna coach
        }

        $coach = $this->coachModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca coach

        $_SESSION['coach_id'] = $coach['id']; // Guarda coach

        return $coach['id']; // Retorna ID
    }

    private function validarCoach()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachClientesController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>