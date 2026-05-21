<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa plan
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos
require_once __DIR__ . '/../../models/institucion/institucionModel.php'; // Importa institución

class ClienteInsPlanController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $planModel; // Modelo plan
    private $accesoModel; // Modelo acceso
    private $institucionModel; // Modelo institución

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->planModel = new PlanModel(); // Instancia plan
        $this->accesoModel = new AccesoModel(); // Instancia acceso
        $this->institucionModel = new InstitucionModel(); // Instancia institución
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $plan = $this->planModel->obtenerPlanActivoCliente($clienteId); // Obtiene plan activo
        $accesos = $this->accesoModel->obtenerPorCliente($clienteId); // Obtiene accesos
        $institucion = $this->institucionModel->obtenerPorCliente($clienteId); // Obtiene institución

        require_once __DIR__ . '/../../views/clienteIns/plan.php'; // Carga vista
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteInsModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda cliente en sesión

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

$controller = new ClienteInsPlanController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>