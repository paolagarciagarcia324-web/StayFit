<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa avance virtual

class CoachProgresoController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $progresoModel; // Modelo progreso
    private $progresoVideoModel; // Modelo avance virtual

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia videos
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Obtiene clientes
        $progresos = $this->progresoModel->obtenerPorCoach($coachId); // Obtiene progresos
        $avanceVirtual = $this->progresoVideoModel->obtenerPorCoach($coachId); // Obtiene avance virtual

        require_once __DIR__ . '/../../views/coach/progreso.php'; // Carga vista
    }

    public function observacion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'observacion' => trim($_POST['observacion']), // Observación profesional
                'estado' => 'activo' // Estado inicial
            ];

            $this->progresoModel->guardarObservacionCoach($datos); // Guarda observación

            $this->progresoModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Observación de progreso registrada'); // Registra historial
        }

        header('Location: progresoController.php'); // Redirige
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

$controller = new CoachProgresoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
