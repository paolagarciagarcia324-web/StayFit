<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa plan
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos

class ClientePlanController
{
    private $clienteModel; // Modelo cliente
    private $planModel; // Modelo plan
    private $accesoModel; // Modelo acceso

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCliente(); // Valida acceso cliente

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->planModel = new PlanModel(); // Instancia plan
        $this->accesoModel = new AccesoModel(); // Instancia acceso
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $plan = $this->planModel->obtenerPlanActivoCliente($clienteId); // Obtiene plan activo
        $coach = $this->clienteModel->obtenerCoachAsignado($clienteId);
        if (!$coach && $plan && !empty($plan['coach_nombre'])) {
            $coach = [
                'nombre_completo' => $plan['coach_nombre'],
                'correo' => $plan['coach_correo'] ?? '',
                'especialidad' => $plan['coach_especialidad'] ?? '',
            ];
        }
        $accesos = $this->accesoModel->obtenerPorCliente($clienteId); // Obtiene accesos

        require_once __DIR__ . '/../../views/cliente/plan.php'; // Carga vista
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

        if ($rol !== 'cliente') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClientePlanController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>
