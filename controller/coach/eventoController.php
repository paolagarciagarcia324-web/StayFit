<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class CoachEventoController
{
    private $coachModel; // Modelo coach
    private $sesionModel; // Modelo sesión

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->sesionModel = new SesionModel(); // Instancia sesión
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $eventos = $this->sesionModel->obtenerEventosPorCoach($coachId); // Obtiene eventos

        require_once __DIR__ . '/../../views/coach/agenda.php'; // Carga vista
    }

    public function marcarAsistencia()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'sesion_id' => $_POST['sesion_id'], // ID sesión
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'estado' => $_POST['estado'], // Asistió o no
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->sesionModel->marcarAsistencia($datos); // Guarda asistencia

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Asistencia registrada por coach'); // Registra historial
        }

        header('Location: eventoController.php'); // Redirige
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

$controller = new CoachEventoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>