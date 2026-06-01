<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/agenda/agendaModel.php'; // Importa agenda
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class CoachAgendaController
{
    private $coachModel; // Modelo coach
    private $agendaModel; // Modelo agenda
    private $sesionModel; // Modelo sesión

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->agendaModel = new AgendaModel(); // Instancia agenda
        $this->sesionModel = new SesionModel(); // Instancia sesiones
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $agenda = $this->agendaModel->obtenerPorCoach($coachId); // Obtiene agenda
        $sesiones = $this->sesionModel->obtenerPorCoach($coachId); // Obtiene sesiones

        require_once __DIR__ . '/../../views/coach/agenda.php'; // Carga vista
    }

    public function programarSesion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'titulo' => trim($_POST['titulo']), // Título sesión
                'fecha' => $_POST['fecha'], // Fecha sesión
                'hora' => $_POST['hora'], // Hora sesión
                'modalidad' => $_POST['modalidad'], // Presencial, virtual o mixta
                'estado' => 'programada' // Estado inicial
            ];

            $this->sesionModel->crear($datos); // Crea sesión

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Sesión programada por coach'); // Registra historial
        }

        header('Location: agendaController.php'); // Redirige
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
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachAgendaController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>