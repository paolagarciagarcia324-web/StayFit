<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa el modelo de clientes
require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa el modelo de coaches
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php'; // Importa programas virtuales

class AsignacionController
{
    private $clienteModel; // Modelo de clientes
    private $coachModel; // Modelo de coaches
    private $programaVirtualModel; // Modelo de programas virtuales

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->clienteModel = new ClienteModel(); // Instancia clientes
        $this->coachModel = new CoachModel(); // Instancia coaches
        $this->programaVirtualModel = new ProgramaVirtualModel(); // Instancia programas virtuales
    }

    public function index()
    {
        $clientes = $this->clienteModel->obtenerClientesActivos(); // Obtiene clientes activos
        $coaches = $this->coachModel->obtenerActivos(); // Obtiene coaches activos
        $programas = $this->programaVirtualModel->obtenerActivos(); // Obtiene programas virtuales
        $asignaciones = $this->clienteModel->obtenerAsignaciones(); // Obtiene asignaciones actuales

        require_once __DIR__ . '/../../views/admin/asignaciones.php'; // Carga la vista
    }

    public function asignarCoach()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $clienteId = $_POST['cliente_id']; // ID del cliente
            $coachId = $_POST['coach_id']; // ID del coach

            $this->clienteModel->asignarCoach($clienteId, $coachId); // Asigna coach al cliente

            $this->registrarTrazabilidad('Coach asignado a cliente'); // Guarda trazabilidad
        }

        header('Location: asignacionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function asignarContenidoVirtual()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $clienteId = $_POST['cliente_id']; // ID del cliente
            $programaId = $_POST['programa_virtual_id']; // ID del programa virtual

            $this->programaVirtualModel->asignarCliente($clienteId, $programaId); // Asigna programa virtual

            $this->registrarTrazabilidad('Contenido virtual asignado a cliente'); // Guarda trazabilidad
        }

        header('Location: asignacionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function cambiarCoach()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $clienteId = $_POST['cliente_id']; // ID del cliente
            $coachId = $_POST['coach_id']; // Nuevo coach

            $this->clienteModel->cambiarCoach($clienteId, $coachId); // Cambia coach asignado

            $this->registrarTrazabilidad('Coach reasignado'); // Guarda trazabilidad
        }

        header('Location: asignacionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->clienteModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
            exit; // Detiene la ejecución
        }
    }
}

$controller = new AsignacionController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe la acción
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>