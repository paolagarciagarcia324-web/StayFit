<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/nutricion/planNutricionalModel.php'; // Importa plan nutricional
require_once __DIR__ . '/../../models/nutricion/comidaModel.php'; // Importa comidas

class ClienteInsNutricionController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $planNutricionalModel; // Modelo plan nutricional
    private $comidaModel; // Modelo comidas

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->planNutricionalModel = new PlanNutricionalModel(); // Instancia nutrición
        $this->comidaModel = new ComidaModel(); // Instancia comidas
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $planNutricional = $this->planNutricionalModel->obtenerPorCliente($clienteId); // Obtiene plan
        $comidas = $this->comidaModel->obtenerPorCliente($clienteId); // Obtiene comidas

        require_once __DIR__ . '/../../views/clienteIns/nutricion.php'; // Carga vista
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

$controller = new ClienteInsNutricionController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>