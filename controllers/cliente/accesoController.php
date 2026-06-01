<?php

require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos

class ClienteAccesoController
{
    private $accesoModel; // Modelo de accesos

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarCliente(); // Valida acceso del cliente

        $this->accesoModel = new AccesoModel(); // Instancia el modelo
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $accesos = $this->accesoModel->obtenerPorCliente($clienteId); // Obtiene accesos

        require_once __DIR__ . '/../../views/cliente/plan.php'; // Carga vista del plan
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'cliente') { // Valida rol cliente
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteAccesoController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>
