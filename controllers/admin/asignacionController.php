<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci?n de roles
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa el modelo de clientes
require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa el modelo de coaches
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php'; // Importa programas virtuales
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes

class AsignacionController
{
    private $clienteModel; // Modelo de clientes
    private $coachModel; // Modelo de coaches
    private $programaVirtualModel; // Modelo de programas virtuales
    private $planModel; // Modelo de planes

    public function __construct()
    {
        session_start(); // Inicia la sesi?n

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->clienteModel = new ClienteModel(); // Instancia clientes
        $this->coachModel = new CoachModel(); // Instancia coaches
        $this->programaVirtualModel = new ProgramaVirtualModel(); // Instancia programas virtuales
        $this->planModel = new PlanModel(); // Instancia planes
    }

    public function index()
    {
        if ($this->planModel->contar() === 0) {
            $this->planModel->asegurarPlanesBase();
        }

        $clientes = $this->clienteModel->obtenerClientesActivos(); // Obtiene clientes activos
        $coaches = $this->coachModel->obtenerActivos(); // Obtiene coaches activos
        $programas = $this->programaVirtualModel->obtenerActivos(); // Obtiene programas virtuales
        $asignaciones = $this->clienteModel->obtenerAsignaciones(); // Obtiene asignaciones actuales
        $totalPlanes = $this->planModel->contar();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require_once __DIR__ . '/../../views/admin/asignaciones.php'; // Carga la vista
    }

    public function asignarCoach()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->planModel->contar() === 0) {
                $this->planModel->asegurarPlanesBase();
            }

            $clienteId = (int) ($_POST['cliente_id'] ?? 0);
            $coachId = (int) ($_POST['coach_id'] ?? 0);

            if ($clienteId && $coachId && $this->clienteModel->asignarCoach($clienteId, $coachId)) {
                $this->registrarTrazabilidad("Coach asignado (cliente_id={$clienteId}, coach_id={$coachId})");
                $this->flash('success', 'Coach asignado correctamente. El historial se actualiz?.');
            } else {
                $this->flash('error', 'No se pudo asignar el coach. El cliente debe existir, el coach debe estar registrado y debe haber al menos un plan en el cat?logo.');
            }
        }

        header('Location: asignacionController.php');
        exit;
    }

    public function asignarContenidoVirtual()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteId = (int) ($_POST['cliente_id'] ?? 0);
            $programaId = (int) ($_POST['programa_virtual_id'] ?? 0);

            if ($clienteId && $programaId && $this->programaVirtualModel->asignarCliente($clienteId, $programaId)) {
                $this->registrarTrazabilidad("Contenido virtual asignado (cliente_id={$clienteId}, plan_id={$programaId})");
                $this->flash('success', 'Programa virtual asignado correctamente.');
            } else {
                $this->flash('error', 'No se pudo asignar el contenido virtual al cliente.');
            }
        }

        header('Location: asignacionController.php');
        exit;
    }

    public function cambiarCoach()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteId = (int) ($_POST['cliente_id'] ?? 0);
            $coachId = (int) ($_POST['coach_id'] ?? 0);

            if ($clienteId && $coachId && $this->clienteModel->cambiarCoach($clienteId, $coachId)) {
                $this->registrarTrazabilidad("Coach reasignado (cliente_id={$clienteId}, coach_id={$coachId})");
                $this->flash('success', 'Coach reasignado correctamente.');
            } else {
                $this->flash('error', 'No se pudo reasignar el coach.');
            }
        }

        header('Location: asignacionController.php');
        exit;
    }

    private function flash($tipo, $mensaje)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->clienteModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        validarAccesoAdministrador(); // Valida sesi?n admin
    }
}

$controller = new AsignacionController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acci?n por defecto

if (method_exists($controller, $accion)) { // Verifica si existe la acci?n
    $controller->$accion(); // Ejecuta la acci?n
} else {
    $controller->index(); // Carga vista principal
}

?>