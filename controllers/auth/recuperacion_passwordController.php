<?php

session_start(); // Inicia la sesión

require_once __DIR__ . '/../../config/database.php'; // Importa conexión
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuario

class RecuperacionPasswordController
{
    private $db; // Conexión
    private $usuarioModel; // Modelo usuario

    public function __construct()
    {
        $database = new Database(); // Instancia base de datos
        $this->db = $database->conectar(); // Abre conexión

        $this->usuarioModel = new UsuarioModel($this->db); // Instancia modelo
    }

    public function recuperar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Valida método POST
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        $correo = trim($_POST['correo'] ?? ''); // Captura correo

        if (empty($correo)) { // Valida campo vacío
            $this->alerta('warning', 'Campo requerido', 'Ingrese su correo'); // Guarda alerta
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) { // Valida correo
            $this->alerta('error', 'Correo inválido', 'Ingrese un correo válido'); // Guarda alerta
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        $usuario = $this->usuarioModel->obtenerPorCorreo($correo); // Busca usuario

        if (!$usuario) { // Valida existencia
            $this->alerta('error', 'Usuario no encontrado', 'El correo no está registrado'); // Guarda alerta
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        $token = bin2hex(random_bytes(32)); // Genera token seguro

        $this->usuarioModel->guardarTokenRecuperacion($usuario['id'], $token); // Guarda token

        $_SESSION['token_recuperacion'] = $token; // Guarda token temporal

        $this->alerta('success', 'Solicitud generada', 'Se generó el proceso de recuperación'); // Guarda alerta

        header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function cambiarPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Valida método POST
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        $token = $_POST['token'] ?? ''; // Captura token
        $password = trim($_POST['password'] ?? ''); // Captura contraseña

        if (empty($token) || empty($password)) { // Valida campos
            $this->alerta('warning', 'Campos incompletos', 'Complete la información'); // Guarda alerta
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        $usuario = $this->usuarioModel->obtenerPorTokenRecuperacion($token); // Busca token

        if (!$usuario) { // Valida token
            $this->alerta('error', 'Token inválido', 'No se pudo validar la recuperación'); // Guarda alerta
            header('Location: ../../views/auth/recuperarPassword.php'); // Redirige
            exit; // Detiene ejecución
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // Cifra contraseña

        $this->usuarioModel->actualizarPassword($usuario['id'], $passwordHash); // Actualiza contraseña

        $this->usuarioModel->limpiarTokenRecuperacion($usuario['id']); // Elimina token

        $this->alerta('success', 'Contraseña actualizada', 'Ya puede iniciar sesión'); // Guarda alerta

        header('Location: ../../views/auth/login.php'); // Redirige al login
        exit; // Detiene ejecución
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

$controller = new RecuperacionPasswordController(); // Crea controlador

$accion = $_GET['accion'] ?? 'recuperar'; // Acción por defecto

if ($accion === 'cambiar') { // Valida cambio de contraseña
    $controller->cambiarPassword(); // Ejecuta cambio
} else {
    $controller->recuperar(); // Ejecuta recuperación
}

?>