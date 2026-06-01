<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci�n de roles
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa el modelo de sesiones

class EventoController
{
    private $sesionModel; // Modelo para sesiones y eventos

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->sesionModel = new SesionModel(); // Instancia el modelo
    }

    public function index()
    {
        $eventos = $this->sesionModel->obtenerTodos(); // Obtiene eventos y sesiones

        require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista existente
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'titulo' => trim($_POST['titulo']), // Título del evento
                'descripcion' => trim($_POST['descripcion']), // Descripción del evento
                'fecha' => $_POST['fecha'], // Fecha del evento
                'hora' => $_POST['hora'], // Hora del evento
                'modalidad' => $_POST['modalidad'], // Presencial, virtual o mixta
                'coach_id' => $_POST['coach_id'] ?? null, // Coach asignado
                'estado' => 'activo' // Estado inicial
            ];

            $this->sesionModel->crearEvento($datos); // Guarda el evento

            $this->registrarTrazabilidad('Evento registrado'); // Guarda trazabilidad
        }

        header('Location: eventoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID del evento
            $estado = $_GET['estado']; // Nuevo estado

            $this->sesionModel->cambiarEstado($id, $estado); // Cambia estado

            $this->registrarTrazabilidad('Estado de evento actualizado'); // Guarda trazabilidad
        }

        header('Location: eventoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->sesionModel->registrarTrazabilidad($adminId, $accion); // Registra historial
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

$controller = new EventoController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga vista principal
}

?>
