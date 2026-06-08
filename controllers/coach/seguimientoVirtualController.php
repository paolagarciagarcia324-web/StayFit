<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa progreso virtual
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php'; // Importa videos
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso físico

class CoachSeguimientoVirtualController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $progresoVideoModel; // Modelo progreso de videos
    private $videoModel; // Modelo videos
    private $progresoModel; // Modelo progreso físico

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia progreso virtual
        $this->videoModel = new VideoModel(); // Instancia videos
        $this->progresoModel = new ProgresoModel(); // Instancia progreso físico
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerVirtualesPorCoach($coachId); // Clientes virtuales con seguimiento
        $avanceVirtual = $this->progresoVideoModel->obtenerResumenPorCoach($coachId); // Avance global por clienta
        $avancePorVideo = $this->progresoVideoModel->obtenerPorCoach($coachId); // Detalle por video
        $progresos = $this->progresoModel->obtenerPorCoach($coachId); // Progreso físico

        require_once __DIR__ . '/../../views/coach/seguimientoVirtual.php';
    }

    public function detalle()
    {
        $clienteId = $_GET['cliente_id'] ?? null; // ID del cliente

        if (!$clienteId) { // Valida cliente
            header('Location: seguimientoVirtualController.php'); // Redirige
            exit; // Detiene ejecución
        }

        $cliente = $this->clienteModel->obtenerPorId($clienteId); // Obtiene cliente
        $videos = $this->videoModel->obtenerPorCliente($clienteId); // Obtiene videos asignados
        $avance = $this->progresoVideoModel->obtenerAvanceCliente($clienteId); // Obtiene avance
        $progreso = $this->progresoModel->obtenerPorCliente($clienteId); // Obtiene progreso físico

        require_once __DIR__ . '/../../views/coach/seguimientoVirtual.php';
    }

    public function observacion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'observacion' => trim($_POST['observacion']), // Observación del coach
                'tipo' => 'virtual' // Tipo de seguimiento
            ];

            $this->progresoVideoModel->guardarObservacion($datos); // Guarda observación

            $this->progresoVideoModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Seguimiento virtual registrado'); // Registra historial
        }

        header('Location: seguimientoVirtualController.php'); // Redirige
        exit; // Detiene ejecución
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
        if (strtolower($_SESSION['rol'] ?? '') !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachSeguimientoVirtualController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
