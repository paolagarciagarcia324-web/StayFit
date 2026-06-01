<?php

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php'; // Validación de roles
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

            $partes = dividirNombreCompleto(trim($_POST['nombre'] ?? ''));
            $identificacion = trim($_POST['identificacion'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '') ?: $identificacion;

            $usuario = [
                'nombre' => $partes['nombre'],
                'apellido' => $partes['apellido'],
                'correo' => trim($_POST['correo']),
                'password' => $contrasena,
                'telefono' => trim($_POST['celular'] ?? ''),
                'documento_identidad' => $identificacion,
                'origen_registro' => 'ADMINISTRATIVO',
                'estado' => 'ACTIVO',
            ];

            $usuarioId = $this->usuarioModel->crear($usuario);
            $this->usuarioModel->asignarRol($usuarioId, 2); // Rol Coach

            $coach = [
                'id_coach' => $usuarioId, // ID del usuario creado
                'especialidad' => trim($_POST['especialidad']), // Especialidad fitness
                'credencial' => trim($_POST['identificacion'] ?? ''), // Credencial
                'biografia' => trim($_POST['biografia'] ?? '') // Descripción profesional
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
                'id_coach' => $_POST['id'], // ID del coach
                'especialidad' => trim($_POST['especialidad']), // Especialidad actualizada
                'credencial' => trim($_POST['identificacion'] ?? ''), // Credencial
                'biografia' => trim($_POST['biografia'] ?? '') // Biografía actualizada
            ];

            $this->coachModel->actualizar($datos); // Actualiza el coach

            if (!empty($_POST['estado'])) { // Cambia estado usuario
                $this->coachModel->cambiarEstado($_POST['id'], strtoupper($_POST['estado'])); // ACTIVO/INACTIVO
            }

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
        validarAccesoAdministrador(); // Valida sesión admin
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