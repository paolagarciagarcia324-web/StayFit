<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/entrenamiento/ejercicioModel.php'; // Importa ejercicios

class CoachRutinaController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $rutinaModel; // Modelo rutina
    private $ejercicioModel; // Modelo ejercicio

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->rutinaModel = new RutinaModel(); // Instancia rutina
        $this->ejercicioModel = new EjercicioModel(); // Instancia ejercicio
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $rutinas = $this->rutinaModel->obtenerPorCoach($coachId); // Obtiene rutinas
        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Obtiene clientes

        require_once __DIR__ . '/../../views/coach/entrenamientos.php'; // Carga vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'nombre' => trim($_POST['nombre']), // Nombre rutina
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'nivel' => $_POST['nivel'], // Nivel
                'estado' => 'activa' // Estado inicial
            ];

            $this->rutinaModel->crear($datos); // Crea rutina

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Rutina creada por coach'); // Registra historial
        }

        header('Location: rutinaController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function detalle()
    {
        $rutinaId = $_GET['id'] ?? null; // ID rutina

        if (!$rutinaId) { // Valida ID
            header('Location: rutinaController.php'); // Redirige
            exit; // Detiene ejecución
        }

        $rutina = $this->rutinaModel->obtenerPorId($rutinaId); // Obtiene rutina
        $ejercicios = $this->ejercicioModel->obtenerPorRutina($rutinaId); // Obtiene ejercicios

        require_once __DIR__ . '/../../views/coach/entrenamientos.php'; // Carga vista
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Valida datos

            $id = $_GET['id']; // ID rutina
            $estado = $_GET['estado']; // Nuevo estado

            $this->rutinaModel->cambiarEstado($id, $estado); // Cambia estado

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Estado de rutina actualizado'); // Registra historial
        }

        header('Location: rutinaController.php'); // Redirige
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

$controller = new CoachRutinaController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
