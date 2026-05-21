<?php

require_once __DIR__ . '/../../models/agenda/agendaModel.php'; // Importa agenda
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class ClienteCalendarioController
{
    private $agendaModel; // Modelo de agenda
    private $sesionModel; // Modelo de sesiones

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarCliente(); // Valida acceso del cliente

        $this->agendaModel = new AgendaModel(); // Instancia agenda
        $this->sesionModel = new SesionModel(); // Instancia sesiones
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $agenda = $this->agendaModel->obtenerPorCliente($clienteId); // Obtiene agenda
        $sesiones = $this->sesionModel->obtenerPorCliente($clienteId); // Obtiene sesiones

        require_once __DIR__ . '/../../views/cliente/calendario.php'; // Carga vista
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

$controller = new ClienteCalendarioController(); // Crea controlador
$controller->index(); // Ejecuta calendario

?>
