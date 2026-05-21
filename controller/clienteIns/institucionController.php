<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/institucion/institucionModel.php'; // Importa institución

class ClienteInsInstitucionController
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
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $clienteInstitucional = $this->clienteInsModel->obtenerPorId($clienteId); // Obtiene cliente
        $institucion = $this->institucionModel->obtenerPorCliente($clienteId); // Obtiene institución
        $convenio = $this->clienteInsModel->obtenerConvenio($clienteId); // Obtiene convenio

        require_once __DIR__ . '/../../views/clienteIns/institucion.php'; // Carga vista
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

$controller = new ClienteInsInstitucionController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>