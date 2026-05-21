<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa el modelo de coach
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo de usuario

class CoachController
{
    private $coachModel; // Modelo de coach
    private $usuarioModel; // Modelo de usuario

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->coachModel = new CoachModel(); // Instancia el modelo coach
        $this->usuarioModel = new UsuarioModel(); // Instancia el modelo usuario
    }

    public function index()
    {
        $coaches = $this->coachModel->obtenerTodos(); // Obtiene todos los coaches

        require_once __DIR__ . '/../../views/admin/coaches.php'; // Carga la vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $usuario = [
                'nombre' => trim($_POST['nombre']), // Nombre del coach
                'correo' => trim($_POST['correo']), // Correo del coach
                'password' => password_hash($_POST['identificacion'], PASSWORD_DEFAULT), // Contraseña inicial
                'rol' => 'coach', // Rol asignado
                'estado' => 'activo' // Estado inicial
            ];

            $usuarioId = $this->usuarioModel->crear($usuario); // Crea el usuario

            $coach = [
                'usuario_id' => $usuarioId, // ID del usuario creado
                'identificacion' => trim($_POST['identificacion']), // Documento del coach
                'celular' => trim($_POST['celular']), // Celular del coach
                'especialidad' => trim($_POST['especialidad']), // Especialidad fitness
                'biografia' => trim($_POST['biografia']), // Descripción profesional
                'estado' => 'activo' // Estado del coach
            ];

            $this->coachModel->crear($coach); // Registra el coach

            $this->registrarTrazabilidad('Coach registrado'); // Guarda trazabilidad
        }

        header('Location: coachController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'id' => $_POST['id'], // ID del coach
                'nombre' => trim($_POST['nombre']), // Nombre actualizado
                'correo' => trim($_POST['correo']), // Correo actualizado
                'identificacion' => trim($_POST['identificacion']), // Documento actualizado
                'celular' => trim($_POST['celular']), // Celular actualizado
                'especialidad' => trim($_POST['especialidad']), // Especialidad actualizada
                'biografia' => trim($_POST['biografia']), // Biografía actualizada
                'estado' => $_POST['estado'] // Estado actualizado
            ];

            $this->coachModel->actualizar($datos); // Actualiza el coach

            $this->registrarTrazabilidad('Coach actualizado'); // Guarda trazabilidad
        }

        header('Location: coachController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID del coach
            $estado = $_GET['estado']; // Nuevo estado

            $this->coachModel->cambiarEstado($id, $estado); // Cambia estado del coach

            $this->registrarTrazabilidad('Estado de coach cambiado'); // Guarda trazabilidad
        }

        header('Location: coachController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function detalle()
    {
        $id = $_GET['id'] ?? null; // Obtiene ID del coach

        if (!$id) { // Valida si existe ID
            header('Location: coachController.php'); // Redirige si no hay ID
            exit; // Detiene la ejecución
        }

        $coach = $this->coachModel->obtenerPorId($id); // Obtiene datos del coach
        $clientes = $this->coachModel->obtenerClientesAsignados($id); // Obtiene clientes asignados

        require_once __DIR__ . '/../../views/admin/coaches.php'; // Carga la vista
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->coachModel->registrarTrazabilidad($adminId, $accion); // Registra historial
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

$controller = new CoachController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Define acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Muestra vista principal
}

?>