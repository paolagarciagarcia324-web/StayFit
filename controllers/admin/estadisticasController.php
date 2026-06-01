<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci�n de roles
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa progreso de videos

class EstadisticasController
{
    private $clienteModel; // Modelo de clientes
    private $pagoModel; // Modelo de pagos
    private $planModel; // Modelo de planes
    private $progresoVideoModel; // Modelo de progreso virtual

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->clienteModel = new ClienteModel(); // Instancia clientes
        $this->pagoModel = new PagoModel(); // Instancia pagos
        $this->planModel = new PlanModel(); // Instancia planes
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia progreso videos
    }

    public function index()
    {
        $estadisticas = [
            'clientesActivos' => $this->clienteModel->contarActivos(), // Total clientes activos
            'pagosAprobados' => $this->pagoModel->contarAprobados(), // Total pagos aprobados
            'planesVendidos' => $this->planModel->contarVendidos(), // Total planes vendidos
            'clientesVirtuales' => $this->clienteModel->contarPorModalidad('virtual'), // Clientes virtuales
            'avanceVirtual' => $this->progresoVideoModel->promedioAvance() // Promedio avance virtual
        ];

        require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista existente
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

$controller = new EstadisticasController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga principal
}

?>
