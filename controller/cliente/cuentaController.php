<?php

require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuario

class ClienteCuentaController
{
    private $usuarioModel; // Modelo de usuario

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarCliente(); // Valida acceso del cliente

        $this->usuarioModel = new UsuarioModel(); // Instancia usuario
    }

    public function index()
    {
        $usuarioId = $_SESSION['usuario_id'] ?? null; // ID del usuario

        $cuenta = $this->usuarioModel->obtenerPorId($usuarioId); // Obtiene cuenta

        require_once __DIR__ . '/../../views/cliente/perfil.php'; // Carga vista
    }

    public function cambiarPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida envío

            $usuarioId = $_SESSION['usuario_id']; // ID usuario
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Cifra contraseña

            $this->usuarioModel->actualizarPassword($usuarioId, $password); // Actualiza contraseña

            $this->usuarioModel->registrarTrazabilidad($usuarioId, 'Contraseña actualizada por cliente'); // Guarda trazabilidad
        }

        header('Location: cuentaController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'cliente') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteCuentaController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga vista
}

?>
