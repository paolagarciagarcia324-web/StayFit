<?php

session_start(); // Inicia la sesión

require_once __DIR__ . '/../../config/database.php'; // Importa conexión
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuario

class RegisterController
{
    private $db; // Conexión
    private $usuarioModel; // Modelo usuario

    public function __construct()
    {
        $database = new Database(); // Instancia base de datos
        $this->db = $database->conectar(); // Abre conexión

        $this->usuarioModel = new UsuarioModel($this->db); // Instancia modelo
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Valida método POST
            header('Location: ../../views/auth/login.php'); // Redirige al login
            exit; // Detiene ejecución
        }

        $nombre = trim($_POST['nombre'] ?? ''); // Captura nombre
        $correo = trim($_POST['correo'] ?? ''); // Captura correo
        $password = trim($_POST['password'] ?? ''); // Captura contraseña
        $rol = $_POST['rol'] ?? 'cliente'; // Rol por defecto

        if (empty($nombre) || empty($correo) || empty($password)) { // Valida campos
            $this->alerta('warning', 'Campos incompletos', 'Complete todos los campos'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige
            exit; // Detiene ejecución
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) { // Valida correo
            $this->alerta('error', 'Correo inválido', 'Ingrese un correo válido'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige
            exit; // Detiene ejecución
        }

        if ($this->usuarioModel->obtenerPorCorreo($correo)) { // Verifica duplicado
            $this->alerta('error', 'Correo registrado', 'Este correo ya existe'); // Guarda alerta
            header('Location: ../../views/auth/login.php'); // Redirige
            exit; // Detiene ejecución
        }

        $datos = [
            'nombre' => $nombre, // Nombre usuario
            'correo' => $correo, // Correo usuario
            'password' => password_hash($password, PASSWORD_DEFAULT), // Contraseña cifrada
            'rol' => $rol, // Rol asignado
            'estado' => 'activo' // Estado inicial
        ];

        $this->usuarioModel->crear($datos); // Crea usuario

        $this->alerta('success', 'Registro exitoso', 'Ya puede iniciar sesión'); // Guarda alerta

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

$controller = new RegisterController(); // Crea controlador
$controller->registrar(); // Ejecuta registro

?>