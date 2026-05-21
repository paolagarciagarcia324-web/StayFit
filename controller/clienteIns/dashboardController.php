<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/institucion/institucionModel.php'; // Importa institución
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa plan
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa avance virtual
require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones

class ClienteInsDashboardController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $institucionModel; // Modelo institución
    private $planModel; // Modelo plan
    private $accesoModel; // Modelo acceso
    private $progresoModel; // Modelo progreso
    private $progresoVideoModel; // Modelo videos
    private $notificacionModel; // Modelo notificaciones

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->institucionModel = new InstitucionModel(); // Instancia institución
        $this->planModel = new PlanModel(); // Instancia plan
        $this->accesoModel = new AccesoModel(); // Instancia acceso
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia avance virtual
        $this->notificacionModel = new NotificacionModel(); // Instancia notificaciones
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $cliente = $this->clienteInsModel->obtenerPorId($clienteId); // Obtiene cliente
        $institucion = $this->institucionModel->obtenerPorCliente($clienteId); // Obtiene institución
        $plan = $this->planModel->obtenerPlanActivoCliente($clienteId); // Obtiene plan activo
        $accesos = $this->accesoModel->obtenerPorCliente($clienteId); // Obtiene accesos
        $progreso = $this->progresoModel->obtenerUltimoPorCliente($clienteId); // Obtiene progreso
        $avanceVirtual = $this->progresoVideoModel->obtenerAvanceCliente($clienteId); // Obtiene avance
        $notificaciones = $this->notificacionModel->obtenerPorUsuario($_SESSION['usuario_id']); // Obtiene alertas

        require_once __DIR__ . '/../../views/clienteIns/dashboard.php'; // Carga vista
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

$controller = new ClienteInsDashboardController(); // Crea controlador
$controller->index(); // Ejecuta dashboard

?>