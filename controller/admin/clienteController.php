<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa el modelo de clientes
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo de usuarios

class ClienteController
{
    private $clienteModel; // Modelo de clientes
    private $usuarioModel; // Modelo de usuarios

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso de administrador

        $this->clienteModel = new ClienteModel(); // Instancia el modelo cliente
        $this->usuarioModel = new UsuarioModel(); // Instancia el modelo usuario
    }

    public function index()
    {
        $clientes = $this->clienteModel->obtenerTodos(); // Obtiene todos los clientes

        require_once __DIR__ . '/../../views/admin/clientes.php'; // Carga la vista
    }

    public function guardarClienteFijo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $usuario = [
                'nombre' => trim($_POST['nombre']), // Nombre del cliente
                'correo' => trim($_POST['correo']), // Correo del cliente
                'password' => password_hash($_POST['identificacion'], PASSWORD_DEFAULT), // Contraseña inicial
                'rol' => 'cliente', // Rol asignado
                'estado' => 'activo' // Estado del usuario
            ];

            $usuarioId = $this->usuarioModel->crear($usuario); // Crea el usuario

            $cliente = [
                'usuario_id' => $usuarioId, // Relación con usuario
                'identificacion' => trim($_POST['identificacion']), // Documento del cliente
                'edad' => $_POST['edad'], // Edad del cliente
                'celular' => trim($_POST['celular']), // Celular del cliente
                'tipo_cliente' => $_POST['tipo_cliente'], // Individual o institucional
                'estado' => 'activo' // Estado del cliente
            ];

            $this->clienteModel->crearClienteFijo($cliente); // Crea cliente fijo

            $this->registrarTrazabilidad('Cliente fijo registrado'); // Registra acción
        }

        header('Location: clienteController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'id' => $_POST['id'], // ID del cliente
                'nombre' => trim($_POST['nombre']), // Nombre actualizado
                'correo' => trim($_POST['correo']), // Correo actualizado
                'identificacion' => trim($_POST['identificacion']), // Documento actualizado
                'edad' => $_POST['edad'], // Edad actualizada
                'celular' => trim($_POST['celular']), // Celular actualizado
                'tipo_cliente' => $_POST['tipo_cliente'], // Tipo de cliente
                'estado' => $_POST['estado'] // Estado actualizado
            ];

            $this->clienteModel->actualizar($datos); // Actualiza el cliente

            $this->registrarTrazabilidad('Cliente actualizado'); // Registra acción
        }

        header('Location: clienteController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID del cliente
            $estado = $_GET['estado']; // Nuevo estado

            $this->clienteModel->cambiarEstado($id, $estado); // Cambia estado del cliente

            $this->registrarTrazabilidad('Estado de cliente cambiado'); // Registra acción
        }

        header('Location: clienteController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function detalle()
    {
        $id = $_GET['id'] ?? null; // Obtiene ID del cliente

        if (!$id) { // Valida existencia del ID
            header('Location: clienteController.php'); // Redirige si no hay ID
            exit; // Detiene la ejecución
        }

        $cliente = $this->clienteModel->obtenerPorId($id); // Obtiene detalle del cliente
        $pagos = $this->clienteModel->obtenerPagos($id); // Obtiene pagos del cliente
        $plan = $this->clienteModel->obtenerPlanActivo($id); // Obtiene plan activo
        $coach = $this->clienteModel->obtenerCoachAsignado($id); // Obtiene coach asignado

        require_once __DIR__ . '/../../views/admin/clientes.php'; // Carga la vista
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->clienteModel->registrarTrazabilidad($adminId, $accion); // Guarda historial
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige si no tiene acceso
            exit; // Detiene la ejecución
        }
    }
}

$controller = new ClienteController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe la acción
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>
