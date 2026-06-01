<?php

session_start(); // Inicia la sesión

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión
require_once __DIR__ . '/../../config/roles.php'; // Helpers de roles
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo usuario

class LoginController
{
    private $db; // Conexión
    private $usuarioModel; // Modelo usuario

    public function __construct()
    {
        $database = new Database(); // Instancia la base de datos
        $this->db = $database->conectar(); // Abre conexión

        $this->usuarioModel = new UsuarioModel(); // Instancia modelo
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Valida método POST
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        $correo = trim($_POST['correo'] ?? ''); // Captura correo
        $password = trim($_POST['password'] ?? ''); // Captura contraseña

        if (empty($correo) || empty($password)) { // Valida campos vacíos
            $this->alerta('warning', 'Campos incompletos', 'Ingrese correo y contraseña'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) { // Valida formato de correo
            $this->alerta('error', 'Correo inválido', 'Ingrese un correo válido'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        $usuario = $this->usuarioModel->obtenerPorCorreo($correo); // Busca usuario

        if (!$usuario) { // Valida existencia
            $this->alerta('error', 'Usuario no encontrado', 'El correo no está registrado'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        if (strtolower($usuario['estado'] ?? '') !== 'activo') { // Valida estado activo
            $this->alerta('error', 'Usuario inactivo', 'Su cuenta no está activa'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        $passwordHash = $usuario['password'] ?? $usuario['hash_contrasena'] ?? ''; // Hash de contraseña

        if (!password_verify($password, $passwordHash)) { // Verifica contraseña
            $this->alerta('error', 'Contraseña incorrecta', 'Verifique sus credenciales'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        session_regenerate_id(true); // Regenera sesión

        $_SESSION['usuario_id'] = $usuario['id'] ?? $usuario['id_usuario']; // Guarda ID
        $_SESSION['nombre'] = $usuario['nombre']; // Guarda nombre
        $_SESSION['correo'] = $usuario['correo']; // Guarda correo
        $_SESSION['rol'] = normalizarRol($usuario['rol']); // Guarda rol normalizado

        $this->redirigirPorRol($_SESSION['rol']); // Redirige según rol
    }

    private function redirigirPorRol($rol)
    {
        $rol = normalizarRol($rol); // Normaliza rol

        switch ($rol) {
            case 'administrador':
                header('Location: ../admin/dashboardController.php'); // Panel admin
                exit;

            case 'coach':
                header('Location: ../coach/dashboardController.php'); // Panel coach
                exit;

            case 'cliente':
                header('Location: ../cliente/dashboardController.php'); // Panel cliente
                exit;

            case 'clienteins':
            case 'cliente_institucional':
                header('Location: ../clienteIns/dashboardController.php'); // Panel institucional
                exit;

            default:
                $this->alerta('error', 'Rol no válido', 'No se pudo determinar el acceso'); // Guarda alerta
                header('Location: ../../views/auth/login.php'); // Redirige al login
                exit;
        }
    }

    private function alerta($icono, $titulo, $texto)
    {
        $_SESSION['alert'] = [ // Guarda alerta
            'icon' => $icono, // Tipo
            'title' => $titulo, // Título
            'text' => $texto // Mensaje
        ];
    }
}

$controller = new LoginController(); // Crea controlador
$controller->login(); // Ejecuta login

?>