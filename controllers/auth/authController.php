<?php

session_start(); // Inicia la sesión

require_once __DIR__ . '/../../config/database.php'; // Importa la conexión
require_once __DIR__ . '/../../config/roles.php'; // Helpers de roles
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo usuario

class AuthController
{
    private $db; // Conexión a la base de datos
    private $usuarioModel; // Modelo de usuario

    public function __construct()
    {
        $database = new Database(); // Crea instancia de conexión
        $this->db = $database->conectar(); // Abre la conexión

        $this->usuarioModel = new UsuarioModel(); // Instancia el modelo
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Valida método POST
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene la ejecución
        }

        $correo = trim($_POST['correo'] ?? ''); // Captura el correo
        $password = trim($_POST['password'] ?? ''); // Captura la contraseña

        if (empty($correo) || empty($password)) { // Valida campos vacíos
            $this->alerta('warning', 'Campos incompletos', 'Ingrese correo y contraseña'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene la ejecución
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) { // Valida formato correo
            $this->alerta('error', 'Correo inválido', 'Ingrese un correo válido'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene la ejecución
        }

        $usuario = $this->usuarioModel->obtenerPorCorreo($correo); // Busca usuario por correo

        if (!$usuario) { // Verifica si existe
            $this->alerta('error', 'Usuario no encontrado', 'El correo no está registrado'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene la ejecución
        }

        if (strtolower($usuario['estado'] ?? '') !== 'activo') { // Valida estado activo
            $this->alerta('error', 'Usuario inactivo', 'Su cuenta no tiene acceso activo'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene la ejecución
        }

        $passwordHash = $usuario['password'] ?? $usuario['hash_contrasena'] ?? $usuario['password_hash'] ?? ''; // Hash de contraseña

        if (!password_verify($password, $passwordHash)) { // Verifica contraseña
            $this->alerta('error', 'Contraseña incorrecta', 'Verifique sus credenciales'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene la ejecución
        }

        session_regenerate_id(true); // Regenera ID de sesión

        $_SESSION['usuario_id'] = $usuario['id'] ?? $usuario['id_usuario']; // Guarda ID del usuario
        $_SESSION['nombre'] = $usuario['nombre']; // Guarda nombre
        $_SESSION['correo'] = $usuario['correo']; // Guarda correo
        $_SESSION['rol'] = normalizarRol($usuario['rol']); // Guarda rol normalizado

        $this->redirigirPorRol($_SESSION['rol']); // Redirige según rol
    }

    public function logout()
    {
        session_unset(); // Limpia variables de sesión
        session_destroy(); // Destruye la sesión

        header('Location: ../../views/auth/login.php'); // Redirige al login
        exit; // Detiene la ejecución
    }

    private function redirigirPorRol($rol)
    {
        $rol = normalizarRol($rol); // Normaliza el rol

        switch ($rol) {
            case 'administrador':
                header('Location: ../admin/dashboardController.php'); // Dashboard admin
                exit; // Detiene la ejecución

            case 'coach':
                header('Location: ../coach/dashboardController.php'); // Dashboard coach
                exit; // Detiene la ejecución

            case 'cliente':
                header('Location: ../cliente/dashboardController.php'); // Dashboard cliente
                exit; // Detiene la ejecución

            case 'cliente_institucional':
            case 'clienteins':
                header('Location: ../clienteIns/dashboardController.php'); // Dashboard institucional
                exit; // Detiene la ejecución

            default:
                $this->alerta('error', 'Rol no válido', 'No se pudo determinar el acceso'); // Guarda alerta
                header('Location: ../../views/auth/login.php'); // Redirige al login
                exit; // Detiene la ejecución
        }
    }

    private function alerta($icono, $titulo, $texto)
    {
        $_SESSION['alert'] = [ // Guarda mensaje de alerta
            'icon' => $icono, // Tipo de alerta
            'title' => $titulo, // Título de alerta
            'text' => $texto // Mensaje de alerta
        ];
    }
}

$controller = new AuthController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'login'; // Acción por defecto

if ($accion === 'logout') { // Valida cierre de sesión
    $controller->logout(); // Ejecuta logout
} else {
    $controller->login(); // Ejecuta login
}

?>