<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/comunicacion/mensajeModel.php'; // Importa mensajes
require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa avance virtual

class CoachDashboardController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $sesionModel; // Modelo sesión
    private $rutinaModel; // Modelo rutina
    private $mensajeModel; // Modelo mensaje
    private $notificacionModel; // Modelo notificación
    private $progresoVideoModel; // Modelo avance virtual

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->sesionModel = new SesionModel(); // Instancia sesión
        $this->rutinaModel = new RutinaModel(); // Instancia rutina
        $this->mensajeModel = new MensajeModel(); // Instancia mensaje
        $this->notificacionModel = new NotificacionModel(); // Instancia notificación
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia avance virtual
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Clientes asignados
        $sesiones = $this->sesionModel->obtenerProximasPorCoach($coachId); // Sesiones próximas
        $rutinasPendientes = $this->rutinaModel->obtenerPendientesPorCoach($coachId); // Rutinas pendientes
        $mensajes = $this->mensajeModel->obtenerNoLeidosPorCoach($coachId); // Mensajes no leídos
        $avanceVirtual = $this->progresoVideoModel->obtenerPorCoach($coachId); // Avance virtual
        $notificaciones = $this->notificacionModel->obtenerPorUsuario($_SESSION['usuario_id']); // Alertas

        require_once __DIR__ . '/../../views/coach/dashboard.php'; // Carga vista
    }

    private function obtenerCoachId()
    {
        if (isset($_SESSION['coach_id'])) { // Verifica sesión
            return $_SESSION['coach_id']; // Retorna coach
        }

        $coach = $this->coachModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca coach

        $_SESSION['coach_id'] = $coach['id']; // Guarda coach

        return $coach['id']; // Retorna ID
    }

    private function validarCoach()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachDashboardController(); // Crea controlador
$controller->index(); // Ejecuta dashboard

?>