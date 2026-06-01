<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/entrenamiento/planEntrenamientoModel.php'; // Importa planes
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas

class CoachEntrenamientoController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $planEntrenamientoModel; // Modelo plan
    private $rutinaModel; // Modelo rutina

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->planEntrenamientoModel = new PlanEntrenamientoModel(); // Instancia plan
        $this->rutinaModel = new RutinaModel(); // Instancia rutina
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Obtiene clientes
        $planes = $this->planEntrenamientoModel->obtenerPorCoach($coachId); // Obtiene planes
        $rutinas = $this->rutinaModel->obtenerPorCoach($coachId); // Obtiene rutinas

        require_once __DIR__ . '/../../views/coach/entrenamientos.php'; // Carga vista
    }

    public function crearPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'nombre' => trim($_POST['nombre']), // Nombre plan
                'objetivo' => trim($_POST['objetivo']), // Objetivo
                'nivel' => $_POST['nivel'], // Nivel
                'duracion' => $_POST['duracion'], // Duración
                'estado' => 'activo' // Estado inicial
            ];

            $this->planEntrenamientoModel->crear($datos); // Crea plan

            $this->planEntrenamientoModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Plan de entrenamiento creado'); // Registra historial
        }

        header('Location: entrenamientoController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function asignarRutina()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'rutina_id' => $_POST['rutina_id'], // ID rutina
                'estado' => 'asignada' // Estado asignación
            ];

            $this->rutinaModel->asignarCliente($datos); // Asigna rutina

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Rutina asignada a cliente'); // Registra historial
        }

        header('Location: entrenamientoController.php'); // Redirige
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

$controller = new CoachEntrenamientoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>