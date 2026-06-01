<?php

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/usuario/usuarioModel.php';

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $identificacion = trim($_POST['identificacion'] ?? '');
            $celular = trim($_POST['celular'] ?? '');
            $edad = (int) ($_POST['edad'] ?? 0);
            $contrasena = trim($_POST['contrasena'] ?? '');

            if ($nombre === '' || $apellido === '' || $correo === '' || $identificacion === '' || $celular === '' || $edad < 12) {
                $_SESSION['alert'] = [
                    'icon' => 'warning',
                    'title' => 'Datos incompletos',
                    'text' => 'Complete nombre, apellido, correo, identificación, edad y celular.',
                ];
                header('Location: clienteController.php');
                exit;
            }

            if ($this->usuarioModel->obtenerPorCorreo($correo)) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Correo en uso',
                    'text' => 'Ya existe un usuario con ese correo.',
                ];
                header('Location: clienteController.php');
                exit;
            }

            if ($contrasena === '') {
                $contrasena = $identificacion;
            }

            $usuario = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'correo' => $correo,
                'password' => $contrasena,
                'telefono' => $celular,
                'documento_identidad' => $identificacion,
                'origen_registro' => 'ADMINISTRATIVO',
                'estado' => 'ACTIVO',
            ];

            $usuarioId = $this->usuarioModel->crear($usuario);
            $this->usuarioModel->asignarRol($usuarioId, 3);

            $cliente = [
                'usuario_id' => $usuarioId,
                'edad' => $edad,
                'tipo_cliente' => $_POST['tipo_cliente'] ?? 'individual',
                'fecha_nacimiento' => edadAFechaNacimiento($edad),
            ];

            $this->clienteModel->crearClienteFijo($cliente);

            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Cliente registrado',
                'text' => 'La clienta puede ingresar con su correo y la contraseña definida.',
            ];

            $this->registrarTrazabilidad('Cliente fijo registrado');
        }

        header('Location: clienteController.php');
        exit;
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'id' => $_POST['id'], // ID del cliente
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
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
        validarAccesoAdministrador(); // Valida sesión admin
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
