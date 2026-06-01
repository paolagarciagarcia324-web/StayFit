<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa avance virtual
require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones

class ClienteDashboardController
{
    private $clienteModel; // Modelo de cliente
    private $accesoModel; // Modelo de acceso
    private $planModel; // Modelo de plan
    private $progresoModel; // Modelo de progreso
    private $progresoVideoModel; // Modelo de videos
    private $notificacionModel; // Modelo de notificaciones

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarCliente(); // Valida acceso

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->accesoModel = new AccesoModel(); // Instancia accesos
        $this->planModel = new PlanModel(); // Instancia planes
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia videos
        $this->notificacionModel = new NotificacionModel(); // Instancia notificaciones
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $cliente = $this->clienteModel->obtenerPorId($clienteId); // Datos del cliente
        $plan = $this->planModel->obtenerPlanActivoCliente($clienteId); // Plan activo
        $coach = $this->clienteModel->obtenerCoachAsignado($clienteId); // Coach asignado
        if (!$coach && $plan && !empty($plan['coach_nombre'])) {
            $coach = [
                'nombre_completo' => $plan['coach_nombre'],
                'correo' => $plan['coach_correo'] ?? '',
                'especialidad' => $plan['coach_especialidad'] ?? '',
            ];
        }
        $accesos = $this->accesoModel->obtenerPorCliente($clienteId); // Accesos activos
        $progreso = $this->progresoModel->obtenerUltimoPorCliente($clienteId); // Último progreso
        $avanceVirtual = $this->progresoVideoModel->obtenerAvanceCliente($clienteId); // Avance virtual
        $notificaciones = $this->notificacionModel->obtenerPorUsuario($_SESSION['usuario_id']); // Alertas

        require_once __DIR__ . '/../../views/cliente/dashboard.php'; // Carga vista
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica cliente en sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteModel->obtenerPorUsuario($_SESSION['usuario_id']);

        if (!$cliente) {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }

        $_SESSION['cliente_id'] = $cliente['id'] ?? $cliente['id_usuario'];

        return $_SESSION['cliente_id'];
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

$controller = new ClienteDashboardController(); // Crea controlador
$controller->index(); // Ejecuta dashboard

?>
