<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci�n de roles
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo de usuarios
require_once __DIR__ . '/../../models/usuario/rolModel.php'; // Importa el modelo de roles

class UsuarioController
{
    private $usuarioModel; // Modelo de usuarios
    private $rolModel; // Modelo de roles

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->usuarioModel = new UsuarioModel(); // Instancia el modelo de usuarios
        $this->rolModel = new RolModel(); // Instancia el modelo de roles
    }

    public function index()
    {
        $usuarios = $this->usuarioModel->obtenerTodos(); // Obtiene todos los usuarios
        $roles = $this->rolModel->obtenerTodos(); // Obtiene todos los roles
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require_once __DIR__ . '/../../views/admin/usuarios.php'; // Carga la vista de usuarios
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío por POST

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre del usuario
                'correo' => trim($_POST['correo']), // Correo del usuario
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), // Contraseña cifrada
                'rol_id' => $_POST['rol_id'], // Rol asignado
                'estado' => 'activo' // Estado inicial
            ];

            $this->usuarioModel->crear($datos); // Guarda el usuario
        }

        header('Location: usuarioController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío por POST

            $datos = [
                'id' => $_POST['id'], // ID del usuario
                'nombre' => trim($_POST['nombre']), // Nombre actualizado
                'correo' => trim($_POST['correo']), // Correo actualizado
                'rol_id' => $_POST['rol_id'], // Rol actualizado
                'estado' => $_POST['estado'] // Estado actualizado
            ];

            $this->usuarioModel->actualizar($datos); // Actualiza el usuario
        }

        header('Location: usuarioController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID del usuario
            $estado = $_GET['estado']; // Nuevo estado

            $this->usuarioModel->cambiarEstado($id, $estado); // Cambia el estado
        }

        header('Location: usuarioController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function eliminar()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $adminId = (int) ($_SESSION['usuario_id'] ?? 0);

        if ($id < 1) {
            $this->flash('error', 'Usuario no válido.');
        } elseif ($id === $adminId) {
            $this->flash('error', 'No puedes eliminar tu propia cuenta de administrador.');
        } else {
            try {
                $this->usuarioModel->eliminar($id);
                $this->usuarioModel->registrarTrazabilidad($adminId ?: null, 'Usuario eliminado (ID ' . $id . ')');
                $this->flash('success', 'Usuario eliminado correctamente.');
            } catch (PDOException $e) {
                $this->flash(
                    'error',
                    'No se pudo eliminar: el usuario tiene registros vinculados (planes, pagos, mensajes). Inactívalo en su lugar.'
                );
            }
        }

        header('Location: usuarioController.php');
        exit;
    }

    private function flash($tipo, $mensaje)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene el rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permisos
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige si no tiene acceso
            exit; // Detiene la ejecución
        }
    }
}

$controller = new UsuarioController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga listado por defecto
}

?>