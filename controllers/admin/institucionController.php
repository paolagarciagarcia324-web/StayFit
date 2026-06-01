<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci�n de roles
require_once __DIR__ . '/../../models/institucion/institucionModel.php'; // Importa el modelo de institución
require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa el modelo de cliente institucional

class InstitucionController
{
    private $institucionModel; // Modelo de instituciones
    private $clienteInsModel; // Modelo de clientes institucionales

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->institucionModel = new InstitucionModel(); // Instancia instituciones
        $this->clienteInsModel = new ClienteInsModel(); // Instancia clientes institucionales
    }

    public function index()
    {
        $instituciones = $this->institucionModel->obtenerTodos(); // Obtiene instituciones
        $clientesInstitucionales = $this->clienteInsModel->obtenerTodos(); // Obtiene clientes institucionales

        require_once __DIR__ . '/../../views/admin/instituciones.php'; // Carga la vista
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre de institución
                'nit' => trim($_POST['nit']), // NIT o identificación
                'telefono' => trim($_POST['telefono']), // Teléfono de contacto
                'correo' => trim($_POST['correo']), // Correo de contacto
                'direccion' => trim($_POST['direccion']), // Dirección
                'estado' => 'activo' // Estado inicial
            ];

            $this->institucionModel->crear($datos); // Guarda institución

            $this->registrarTrazabilidad('Institución registrada'); // Guarda trazabilidad
        }

        header('Location: institucionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'id' => $_POST['id'], // ID de institución
                'nombre' => trim($_POST['nombre']), // Nombre actualizado
                'nit' => trim($_POST['nit']), // NIT actualizado
                'telefono' => trim($_POST['telefono']), // Teléfono actualizado
                'correo' => trim($_POST['correo']), // Correo actualizado
                'direccion' => trim($_POST['direccion']), // Dirección actualizada
                'estado' => $_POST['estado'] // Estado actualizado
            ];

            $this->institucionModel->actualizar($datos); // Actualiza institución

            $this->registrarTrazabilidad('Institución actualizada'); // Guarda trazabilidad
        }

        header('Location: institucionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function vincularCliente()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'cliente_id' => $_POST['cliente_id'], // ID del cliente
                'institucion_id' => $_POST['institucion_id'], // ID de institución
                'cargo' => trim($_POST['cargo']), // Cargo o relación
                'estado' => 'activo' // Estado de vinculación
            ];

            $this->clienteInsModel->vincularInstitucion($datos); // Vincula cliente con institución

            $this->registrarTrazabilidad('Cliente vinculado a institución'); // Guarda trazabilidad
        }

        header('Location: institucionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID de institución
            $estado = $_GET['estado']; // Nuevo estado

            $this->institucionModel->cambiarEstado($id, $estado); // Cambia estado

            $this->registrarTrazabilidad('Estado de institución cambiado'); // Guarda trazabilidad
        }

        header('Location: institucionController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->institucionModel->registrarTrazabilidad($adminId, $accion); // Registra historial
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

$controller = new InstitucionController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe la acción
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>