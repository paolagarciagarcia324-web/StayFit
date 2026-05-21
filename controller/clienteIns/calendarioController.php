<?php

require_once __DIR__ . '/../../models/agenda/agendaModel.php'; // Importa agenda
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class ClienteInsCalendarioController
{
    private $agendaModel; // Modelo agenda
    private $sesionModel; // Modelo sesiones

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->agendaModel = new AgendaModel(); // Instancia agenda
        $this->sesionModel = new SesionModel(); // Instancia sesiones
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $agenda = $this->agendaModel->obtenerPorCliente($clienteId); // Obtiene agenda
        $sesiones = $this->sesionModel->obtenerPorCliente($clienteId); // Obtiene sesiones
        $sesionesGrupales = $this->sesionModel->obtenerGrupalesPorCliente($clienteId); // Obtiene grupales

        require_once __DIR__ . '/../../views/clienteIns/calendario.php'; // Carga vista
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

$controller = new ClienteInsCalendarioController(); // Crea controlador
$controller->index(); // Ejecuta calendario

?>