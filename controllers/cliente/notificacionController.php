<?php

require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones

class ClienteNotificacionController
{
    private $notificacionModel; // Modelo de notificaciones

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCliente(); // Valida acceso

        $this->notificacionModel = new NotificacionModel(); // Instancia modelo
    }

    public function index()
    {
        $usuarioId = $_SESSION['usuario_id']; // ID usuario

        $notificaciones = $this->notificacionModel->obtenerPorUsuario($usuarioId); // Obtiene notificaciones

        require_once __DIR__ . '/../../views/cliente/notificaciones.php'; // Carga vista
    }

    public function marcarLeida()
    {
        if (isset($_GET['id'])) { // Verifica ID

            $id = $_GET['id']; // ID notificación

            $this->notificacionModel->marcarLeida($id); // Marca como leída
        }

        header('Location: notificacionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'cliente') { // Valida cliente
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteNotificacionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
