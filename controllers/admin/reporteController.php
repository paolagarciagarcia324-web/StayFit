<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci�n de roles
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso

class ReporteController
{
    private $clienteModel; // Modelo de clientes
    private $pagoModel; // Modelo de pagos
    private $planModel; // Modelo de planes
    private $progresoModel; // Modelo de progreso

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->clienteModel = new ClienteModel(); // Instancia clientes
        $this->pagoModel = new PagoModel(); // Instancia pagos
        $this->planModel = new PlanModel(); // Instancia planes
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
    }

    public function index()
    {
        $reporteClientes = $this->clienteModel->reporteGeneral(); // Reporte de clientes
        $reportePagos = $this->pagoModel->reporteGeneral(); // Reporte de pagos
        $reportePlanes = $this->planModel->reporteGeneral(); // Reporte de planes
        $reporteProgreso = $this->progresoModel->reporteGeneral(); // Reporte de progreso

        require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga la vista
    }

    public function pagos()
    {
        $reportePagos = $this->pagoModel->reporteGeneral(); // Obtiene reporte de pagos

        require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista
    }

    public function clientes()
    {
        $reporteClientes = $this->clienteModel->reporteGeneral(); // Obtiene reporte de clientes

        require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista
    }

    public function progreso()
    {
        $reporteProgreso = $this->progresoModel->reporteGeneral(); // Obtiene reporte de progreso

        require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
            exit; // Detiene ejecución
        }
    }
}

$controller = new ReporteController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga principal
}

?>
