<?php

require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuario

class ClienteInsCuentaController
{
    private $usuarioModel; // Modelo usuario

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->usuarioModel = new UsuarioModel(); // Instancia usuario
    }

    public function index()
    {
        $usuarioId = $_SESSION['usuario_id']; // ID del usuario

        $cuenta = $this->usuarioModel->obtenerPorId($usuarioId); // Obtiene cuenta

        require_once __DIR__ . '/../../views/clienteIns/perfil.php'; // Carga vista
    }

    public function cambiarPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $usuarioId = $_SESSION['usuario_id']; // ID usuario
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Cifra contraseña

            $this->usuarioModel->actualizarPassword($usuarioId, $password); // Actualiza contraseña

            $this->usuarioModel->registrarTrazabilidad($usuarioId, 'Contraseña actualizada por cliente institucional'); // Registra historial
        }

        header('Location: cuentaController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function validarClienteInstitucional()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'clienteins' && $rol !== 'cliente_institucional') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteInsCuentaController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>