<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class CoachReporteController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $progresoModel; // Modelo progreso
    private $rutinaModel; // Modelo rutina
    private $sesionModel; // Modelo sesión

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
        $this->rutinaModel = new RutinaModel(); // Instancia rutina
        $this->sesionModel = new SesionModel(); // Instancia sesión
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $reporteClientes = $this->clienteModel->reportePorCoach($coachId); // Reporte clientes
        $reporteProgreso = $this->progresoModel->reportePorCoach($coachId); // Reporte progreso
        $reporteRutinas = $this->rutinaModel->reportePorCoach($coachId); // Reporte rutinas
        $reporteSesiones = $this->sesionModel->reportePorCoach($coachId); // Reporte sesiones

        require_once __DIR__ . '/../../views/coach/progreso.php'; // Carga vista existente
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

$controller = new CoachReporteController(); // Crea controlador
$controller->index(); // Ejecuta reporte

?>