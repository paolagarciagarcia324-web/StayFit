<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/entrenamiento/ejercicioModel.php'; // Importa ejercicios

class CoachMaterialController
{
    private $coachModel; // Modelo coach
    private $ejercicioModel; // Modelo ejercicio

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->ejercicioModel = new EjercicioModel(); // Instancia ejercicio
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $materiales = $this->ejercicioModel->obtenerMaterialPorCoach($coachId); // Obtiene material

        require_once __DIR__ . '/../../views/coach/entrenamientos.php'; // Carga vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'ejercicio_id' => $_POST['ejercicio_id'], // ID ejercicio
                'titulo' => trim($_POST['titulo']), // Título material
                'tipo' => $_POST['tipo'], // Video, imagen o enlace
                'url' => trim($_POST['url']), // Ruta o enlace
                'estado' => 'activo' // Estado inicial
            ];

            $this->ejercicioModel->guardarMaterial($datos); // Guarda material

            $this->ejercicioModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Material de entrenamiento agregado'); // Registra historial
        }

        header('Location: materialController.php'); // Redirige
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

$controller = new CoachMaterialController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>