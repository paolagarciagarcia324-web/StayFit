<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/nutricion/planNutricionalModel.php'; // Importa planes nutricionales
require_once __DIR__ . '/../../models/nutricion/comidaModel.php'; // Importa comidas

class CoachNutricionController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $planNutricionalModel; // Modelo nutrición
    private $comidaModel; // Modelo comida

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->planNutricionalModel = new PlanNutricionalModel(); // Instancia plan nutricional
        $this->comidaModel = new ComidaModel(); // Instancia comida
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Obtiene clientes
        $planesNutricionales = $this->planNutricionalModel->obtenerPorCoach($coachId); // Obtiene planes
        $comidas = $this->comidaModel->obtenerPorCoach($coachId); // Obtiene comidas

        require_once __DIR__ . '/../../views/coach/nutricion.php'; // Carga vista
    }

    public function crearPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'nombre' => trim($_POST['nombre']), // Nombre del plan
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'objetivo' => trim($_POST['objetivo']), // Objetivo nutricional
                'estado' => 'activo' // Estado inicial
            ];

            $this->planNutricionalModel->crear($datos); // Crea plan nutricional

            $this->planNutricionalModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Plan nutricional creado por coach'); // Registra historial
        }

        header('Location: nutricionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function actualizarPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'id' => $_POST['id'], // ID del plan
                'nombre' => trim($_POST['nombre']), // Nombre
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'objetivo' => trim($_POST['objetivo']), // Objetivo
                'estado' => $_POST['estado'] // Estado
            ];

            $this->planNutricionalModel->actualizar($datos); // Actualiza plan

            $this->planNutricionalModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Plan nutricional actualizado'); // Registra historial
        }

        header('Location: nutricionController.php'); // Redirige
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

$controller = new CoachNutricionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>