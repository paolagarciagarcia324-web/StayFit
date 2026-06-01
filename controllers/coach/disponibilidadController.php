<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/coach/disponibilidadModel.php'; // Importa disponibilidad

class CoachDisponibilidadController
{
    private $coachModel; // Modelo coach
    private $disponibilidadModel; // Modelo disponibilidad

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->disponibilidadModel = new DisponibilidadModel(); // Instancia disponibilidad
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $disponibilidades = $this->disponibilidadModel->obtenerPorCoach($coachId); // Obtiene horarios

        require_once __DIR__ . '/../../views/coach/agenda.php'; // Carga vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'dia' => $_POST['dia'], // Día disponible
                'hora_inicio' => $_POST['hora_inicio'], // Hora inicio
                'hora_fin' => $_POST['hora_fin'], // Hora fin
                'modalidad' => $_POST['modalidad'], // Presencial o virtual
                'estado' => 'activo' // Estado inicial
            ];

            $this->disponibilidadModel->crear($datos); // Guarda disponibilidad

            $this->disponibilidadModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Disponibilidad registrada por coach'); // Registra historial
        }

        header('Location: disponibilidadController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function eliminar()
    {
        if (isset($_GET['id'])) { // Valida ID

            $this->disponibilidadModel->eliminar($_GET['id']); // Elimina disponibilidad

            $this->disponibilidadModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Disponibilidad eliminada por coach'); // Registra historial
        }

        header('Location: disponibilidadController.php'); // Redirige
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

$controller = new CoachDisponibilidadController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>