<?php

require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/plan/programaModel.php'; // Importa programas
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php'; // Importa programas virtuales

class PlanController
{
    private $planModel; // Modelo de planes
    private $programaModel; // Modelo de programas
    private $programaVirtualModel; // Modelo de programas virtuales

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->planModel = new PlanModel(); // Instancia planes
        $this->programaModel = new ProgramaModel(); // Instancia programas
        $this->programaVirtualModel = new ProgramaVirtualModel(); // Instancia programas virtuales
    }

    public function index()
    {
        $planes = $this->planModel->obtenerTodos(); // Obtiene planes
        $programas = $this->programaModel->obtenerTodos(); // Obtiene programas
        $programasVirtuales = $this->programaVirtualModel->obtenerActivos(); // Obtiene virtuales activos

        require_once __DIR__ . '/../../views/admin/planes.php'; // Carga la vista
    }

    public function guardarPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre del plan
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'precio' => $_POST['precio'], // Precio del plan
                'duracion' => $_POST['duracion'], // Duración en días
                'modalidad' => $_POST['modalidad'], // Presencial, virtual o mixta
                'incluye_entrenamiento' => isset($_POST['incluye_entrenamiento']) ? 1 : 0, // Entrenamiento
                'incluye_nutricion' => isset($_POST['incluye_nutricion']) ? 1 : 0, // Nutrición
                'requiere_coach' => isset($_POST['requiere_coach']) ? 1 : 0, // Coach requerido
                'programa_virtual_id' => $_POST['programa_virtual_id'] ?? null, // Programa virtual
                'estado' => 'activo' // Estado inicial
            ];

            $this->planModel->crear($datos); // Guarda plan

            $this->registrarTrazabilidad('Plan creado'); // Guarda trazabilidad
        }

        header('Location: planController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function actualizarPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'id' => $_POST['id'], // ID del plan
                'nombre' => trim($_POST['nombre']), // Nombre actualizado
                'descripcion' => trim($_POST['descripcion']), // Descripción actualizada
                'precio' => $_POST['precio'], // Precio actualizado
                'duracion' => $_POST['duracion'], // Duración actualizada
                'modalidad' => $_POST['modalidad'], // Modalidad actualizada
                'incluye_entrenamiento' => isset($_POST['incluye_entrenamiento']) ? 1 : 0, // Entrenamiento
                'incluye_nutricion' => isset($_POST['incluye_nutricion']) ? 1 : 0, // Nutrición
                'requiere_coach' => isset($_POST['requiere_coach']) ? 1 : 0, // Coach requerido
                'programa_virtual_id' => $_POST['programa_virtual_id'] ?? null, // Programa virtual
                'estado' => $_POST['estado'] // Estado actualizado
            ];

            $this->planModel->actualizar($datos); // Actualiza plan

            $this->registrarTrazabilidad('Plan actualizado'); // Guarda trazabilidad
        }

        header('Location: planController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function guardarPrograma()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre del programa
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'precio' => $_POST['precio'], // Precio del programa
                'modalidad' => $_POST['modalidad'], // Modalidad
                'estado' => 'activo' // Estado inicial
            ];

            $this->programaModel->crear($datos); // Guarda programa

            $this->registrarTrazabilidad('Programa creado'); // Guarda trazabilidad
        }

        header('Location: planController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID del plan
            $estado = $_GET['estado']; // Nuevo estado

            $this->planModel->cambiarEstado($id, $estado); // Cambia estado

            $this->registrarTrazabilidad('Estado de plan actualizado'); // Guarda trazabilidad
        }

        header('Location: planController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->planModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
            exit; // Detiene la ejecución
        }
    }
}

$controller = new PlanController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>
