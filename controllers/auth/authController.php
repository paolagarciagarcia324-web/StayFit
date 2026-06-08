<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/usuario/usuarioModel.php';

class AuthController
{
    private $db;
    private $usuarioModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conectar();
        $this->usuarioModel = new UsuarioModel($this->db);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../views/auth/login.php');
            exit;
        }

        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($correo === '' || $password === '') {
            $this->alerta('warning', 'Campos incompletos', 'Ingrese correo y contraseña');
            header('Location: ../../views/auth/login.php');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->alerta('error', 'Correo inválido', 'Ingrese un correo válido');
            header('Location: ../../views/auth/login.php');
            exit;
        }

        $usuario = $this->usuarioModel->obtenerPorCorreo($correo);

        if (!$usuario) {
            $this->alerta('error', 'Usuario no encontrado', 'El correo no está registrado');
            header('Location: ../../views/auth/login.php');
            exit;
        }

        if (strtolower($usuario['estado'] ?? '') !== 'activo') {
            $this->alerta('error', 'Usuario inactivo', 'Su cuenta no tiene acceso activo');
            header('Location: ../../views/auth/login.php');
            exit;
        }

        $passwordHash = $usuario['password'] ?? $usuario['hash_contrasena'] ?? $usuario['password_hash'] ?? '';

        if (!$this->passwordValida($usuario, $password, $passwordHash)) {
            $this->alerta('error', 'Contraseña incorrecta', 'Verifique sus credenciales');
            header('Location: ../../views/auth/login.php');
            exit;
        }

        $rol = normalizarRol($usuario['rol'] ?? '');

        if ($rol === '') {
            $this->alerta('error', 'Rol no válido', 'No se pudo determinar el acceso');
            header('Location: ../../views/auth/login.php');
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'] ?? $usuario['id_usuario'];
        $_SESSION['nombre'] = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $rol;

        $this->redirigirPorRol($rol);
    }

    private function passwordValida(array $usuario, string $password, string $passwordHash): bool
    {
        if ($passwordHash !== '' && password_verify($password, $passwordHash)) {
            return true;
        }

        $infoHash = password_get_info($passwordHash);
        $esPasswordPlano = ($infoHash['algo'] ?? 0) === 0;

        if ($esPasswordPlano && $passwordHash !== '' && hash_equals($passwordHash, $password)) {
            $usuarioId = $usuario['id'] ?? $usuario['id_usuario'] ?? null;

            if ($usuarioId) {
                $this->usuarioModel->actualizarPassword($usuarioId, $password);
            }

            return true;
        }

        return false;
    }

    public function logout()
    {
        session_unset();
        session_destroy();

        header('Location: ../../views/auth/login.php');
        exit;
    }

    private function redirigirPorRol($rol)
    {
        switch (normalizarRol($rol)) {
            case 'administrador':
                header('Location: ../admin/dashboardController.php');
                exit;

            case 'coach':
                header('Location: ../coach/dashboardController.php');
                exit;

            case 'cliente':
                header('Location: ../cliente/dashboardController.php');
                exit;

            case 'clienteins':
            case 'cliente_institucional':
                header('Location: ../clienteIns/dashboardController.php');
                exit;

            default:
                $this->alerta('error', 'Rol no válido', 'No se pudo determinar el acceso');
                header('Location: ../../views/auth/login.php');
                exit;
        }
    }

    private function alerta($icono, $titulo, $texto)
    {
        $_SESSION['alert'] = [
            'icon' => $icono,
            'title' => $titulo,
            'text' => $texto
        ];
    }
}

$controller = new AuthController();

$accion = $_GET['accion'] ?? 'login';

if ($accion === 'logout') {
    $controller->logout();
} else {
    $controller->login();
}

?>
