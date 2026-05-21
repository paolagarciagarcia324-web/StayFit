<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/institucion/institucionModel.php'; // Importa institución

class ClienteInsConvenioController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $institucionModel; // Modelo institución

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->institucionModel = new InstitucionModel(); // Instancia institución
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID cliente

        $convenio = $this->clienteInsModel->obtenerConvenio($clienteId); // Obtiene convenio
        $institucion = $this->institucionModel->obtenerPorCliente($clienteId); // Obtiene institución

        require_once __DIR__ . '/../../views/clienteIns/institucion.php'; // Carga vista
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

$controller = new ClienteInsConvenioController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>