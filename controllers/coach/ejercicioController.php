<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/entrenamiento/ejercicioModel.php'; // Importa ejercicios
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas

class CoachEjercicioController
{
    private $coachModel; // Modelo coach
    private $ejercicioModel; // Modelo ejercicio
    private $rutinaModel; // Modelo rutina

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->ejercicioModel = new EjercicioModel(); // Instancia ejercicio
        $this->rutinaModel = new RutinaModel(); // Instancia rutina
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $ejercicios = $this->ejercicioModel->obtenerPorCoach($coachId); // Obtiene ejercicios
        $rutinas = $this->rutinaModel->obtenerPorCoach($coachId); // Obtiene rutinas

        require_once __DIR__ . '/../../views/coach/entrenamientos.php'; // Carga vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'rutina_id' => $_POST['rutina_id'], // ID rutina
                'nombre' => trim($_POST['nombre']), // Nombre ejercicio
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'series' => $_POST['series'], // Series
                'repeticiones' => $_POST['repeticiones'], // Repeticiones
                'descanso' => $_POST['descanso'], // Descanso
                'estado' => 'activo' // Estado inicial
            ];

            $this->ejercicioModel->crear($datos); // Guarda ejercicio

            $this->ejercicioModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Ejercicio creado por coach'); // Registra historial
        }

        header('Location: ejercicioController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'id' => $_POST['id'], // ID ejercicio
                'nombre' => trim($_POST['nombre']), // Nombre
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'series' => $_POST['series'], // Series
                'repeticiones' => $_POST['repeticiones'], // Repeticiones
                'descanso' => $_POST['descanso'], // Descanso
                'estado' => $_POST['estado'] // Estado
            ];

            $this->ejercicioModel->actualizar($datos); // Actualiza ejercicio

            $this->ejercicioModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Ejercicio actualizado por coach'); // Registra historial
        }

        header('Location: ejercicioController.php'); // Redirige
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

$controller = new CoachEjercicioController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>