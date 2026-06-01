<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/nutricion/planNutricionalModel.php'; // Importa plan nutricional
require_once __DIR__ . '/../../models/nutricion/comidaModel.php'; // Importa comidas

class ClienteNutricionController
{
    private $clienteModel; // Modelo cliente
    private $planNutricionalModel; // Modelo plan nutricional
    private $comidaModel; // Modelo comidas

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCliente(); // Valida acceso cliente

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->planNutricionalModel = new PlanNutricionalModel(); // Instancia nutrición
        $this->comidaModel = new ComidaModel(); // Instancia comidas
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $planNutricional = $this->planNutricionalModel->obtenerPorCliente($clienteId); // Obtiene plan
        $comidas = $this->comidaModel->obtenerPorCliente($clienteId); // Obtiene comidas

        require_once __DIR__ . '/../../views/cliente/nutricion.php'; // Carga vista
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

$controller = new ClienteNutricionController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>