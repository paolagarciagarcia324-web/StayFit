<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci�n de roles
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa el modelo de accesos

class AccesoController
{
    private $accesoModel; // Modelo de accesos

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->accesoModel = new AccesoModel(); // Instancia el modelo
    }

    public function index()
    {
        $accesos = $this->accesoModel->obtenerTodos(); // Obtiene todos los accesos

        require_once __DIR__ . '/../../views/admin/pagos.php'; // Carga la vista relacionada
    }

    public function activar()
    {
        if (isset($_GET['id'])) { // Verifica el ID recibido

            $id = $_GET['id']; // ID del acceso

            $this->accesoModel->cambiarEstado($id, 'activo'); // Activa el acceso

            $this->registrarTrazabilidad('Acceso activado'); // Guarda trazabilidad
        }

        header('Location: accesoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function bloquear()
    {
        if (isset($_GET['id'])) { // Verifica el ID recibido

            $id = $_GET['id']; // ID del acceso

            $this->accesoModel->cambiarEstado($id, 'bloqueado'); // Bloquea el acceso

            $this->registrarTrazabilidad('Acceso bloqueado'); // Guarda trazabilidad
        }

        header('Location: accesoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function vencer()
    {
        if (isset($_GET['id'])) { // Verifica el ID recibido

            $id = $_GET['id']; // ID del acceso

            $this->accesoModel->cambiarEstado($id, 'vencido'); // Marca acceso vencido

            $this->registrarTrazabilidad('Acceso vencido'); // Guarda trazabilidad
        }

        header('Location: accesoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function actualizarModulos()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'cliente_id' => $_POST['cliente_id'], // ID del cliente
                'entrenamiento' => isset($_POST['entrenamiento']) ? 1 : 0, // Acceso a entrenamiento
                'nutricion' => isset($_POST['nutricion']) ? 1 : 0, // Acceso a nutrición
                'contenido_virtual' => isset($_POST['contenido_virtual']) ? 1 : 0, // Acceso a videos
                'sesiones' => isset($_POST['sesiones']) ? 1 : 0, // Acceso a sesiones
                'acompanamiento' => isset($_POST['acompanamiento']) ? 1 : 0 // Acceso a coach
            ];

            $this->accesoModel->actualizarModulos($datos); // Actualiza módulos del cliente

            $this->registrarTrazabilidad('Módulos de acceso actualizados'); // Guarda trazabilidad
        }

        header('Location: accesoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->accesoModel->registrarTrazabilidad($adminId, $accion); // Registra historial
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

$controller = new AccesoController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe la acción
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>