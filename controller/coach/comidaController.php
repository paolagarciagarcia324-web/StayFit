<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/nutricion/comidaModel.php'; // Importa comidas
require_once __DIR__ . '/../../models/nutricion/planNutricionalModel.php'; // Importa planes nutricionales

class CoachComidaController
{
    private $coachModel; // Modelo coach
    private $comidaModel; // Modelo comida
    private $planNutricionalModel; // Modelo nutricional

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->comidaModel = new ComidaModel(); // Instancia comida
        $this->planNutricionalModel = new PlanNutricionalModel(); // Instancia plan
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $planesNutricionales = $this->planNutricionalModel->obtenerPorCoach($coachId); // Obtiene planes
        $comidas = $this->comidaModel->obtenerPorCoach($coachId); // Obtiene comidas

        require_once __DIR__ . '/../../views/coach/nutricion.php'; // Carga vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'plan_nutricional_id' => $_POST['plan_nutricional_id'], // ID plan
                'nombre' => trim($_POST['nombre']), // Nombre comida
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'hora' => $_POST['hora'], // Hora sugerida
                'calorias' => $_POST['calorias'] ?? null, // Calorías
                'estado' => 'activo' // Estado inicial
            ];

            $this->comidaModel->crear($datos); // Crea comida

            $this->comidaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Comida nutricional creada por coach'); // Registra historial
        }

        header('Location: comidaController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'id' => $_POST['id'], // ID comida
                'nombre' => trim($_POST['nombre']), // Nombre
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'hora' => $_POST['hora'], // Hora
                'calorias' => $_POST['calorias'] ?? null, // Calorías
                'estado' => $_POST['estado'] // Estado
            ];

            $this->comidaModel->actualizar($datos); // Actualiza comida

            $this->comidaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Comida nutricional actualizada'); // Registra historial
        }

        header('Location: comidaController.php'); // Redirige
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

$controller = new CoachComidaController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>