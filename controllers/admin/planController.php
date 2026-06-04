<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci髇 de roles
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
        session_start(); // Inicia la sesi贸n

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $modalidad = trim($_POST['modalidad'] ?? 'VIRTUAL');

            $datos = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'precio' => $_POST['precio'] ?? 0,
                'duracion_dias' => $_POST['duracion_dias'] ?? $_POST['duracion'] ?? null,
                'modalidad' => $modalidad,
                'requiere_coach' => isset($_POST['requiere_coach']),
                'incluye_entrenamiento' => isset($_POST['incluye_entrenamiento']),
                'incluye_nutricion' => isset($_POST['incluye_nutricion']),
                'incluye_videos' => !empty($_POST['programa_virtual_id'])
                    || in_array(strtoupper($modalidad), ['VIRTUAL', 'MIXTA', 'MIXTO'], true),
                'estado_plan' => 'ACTIVO',
            ];

            $this->planModel->crear($datos);

            $this->registrarTrazabilidad('Plan creado');
        }

        header('Location: planController.php');
        exit;
    }

    public function actualizarPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica env铆o del formulario

            $datos = [
                'id_plan'                         => $_POST['id_plan'],                        // ID del plan
                'nombre'                          => trim($_POST['nombre']),                    // Nombre actualizado
                'descripcion'                     => trim($_POST['descripcion']),               // Descripci贸n actualizada
                'precio'                          => $_POST['precio'],                          // Precio actualizado
                'duracion_dias'                   => $_POST['duracion_dias'] ?? null,           // Duraci贸n actualizada
                'dias_previos_recordatorio_default' => $_POST['dias_previos_recordatorio_default'] ?? 5, // D铆as recordatorio
                'estado_plan'                     => $_POST['estado_plan'] ?? 'ACTIVO'         // Estado actualizado
            ];

            $this->planModel->actualizar($datos); // Actualiza plan

            $this->registrarTrazabilidad('Plan actualizado'); // Guarda trazabilidad
        }

        header('Location: planController.php'); // Redirige al panel
        exit; // Detiene la ejecuci贸n
    }

    public function guardarPrograma()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica env铆o del formulario

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre del programa
                'descripcion' => trim($_POST['descripcion']), // Descripci贸n
                'precio' => $_POST['precio'], // Precio del programa
                'modalidad' => $_POST['modalidad'], // Modalidad
                'estado' => 'activo' // Estado inicial
            ];

            $this->programaModel->crear($datos); // Guarda programa

            $this->registrarTrazabilidad('Programa creado'); // Guarda trazabilidad
        }

        header('Location: planController.php'); // Redirige al panel
        exit; // Detiene la ejecuci贸n
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
        exit; // Detiene la ejecuci贸n
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->planModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        validarAccesoAdministrador(); // Valida sesi髇 admin
    }
}

$controller = new PlanController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acci贸n por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el m茅todo
    $controller->$accion(); // Ejecuta la acci贸n
} else {
    $controller->index(); // Carga vista principal
}

?>
