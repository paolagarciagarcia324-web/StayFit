<?php

require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones

class CoachNotificacionController
{
    private $notificacionModel; // Modelo notificación

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->notificacionModel = new NotificacionModel(); // Instancia modelo
    }

    public function index()
    {
        $usuarioId = $_SESSION['usuario_id']; // ID usuario

        $notificaciones = $this->notificacionModel->obtenerPorUsuario($usuarioId); // Obtiene notificaciones

        require_once __DIR__ . '/../../views/coach/notificaciones.php'; // Carga vista
    }

    public function marcarLeida()
    {
        if (isset($_GET['id'])) { // Valida ID

            $id = $_GET['id']; // ID notificación

            $this->notificacionModel->marcarLeida($id); // Marca como leída
        }

        header('Location: notificacionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function validarCoach()
    {
        if (strtolower($_SESSION['rol'] ?? '') !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachNotificacionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>