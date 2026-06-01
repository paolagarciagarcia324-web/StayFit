<?php

require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos

class ClienteInsAccesoController
{
    private $accesoModel; // Modelo de accesos

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso institucional

        $this->accesoModel = new AccesoModel(); // Instancia accesos
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $accesos = $this->accesoModel->obtenerPorCliente($clienteId); // Obtiene accesos

        require_once __DIR__ . '/../../views/clienteIns/plan.php'; // Carga vista
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

$controller = new ClienteInsAccesoController(); // Crea controlador
$controller->index(); // Ejecuta vista

?>