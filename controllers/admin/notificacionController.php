<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci?n de roles
require_once __DIR__ . '/../../config/roles.php'; // Validaci?n de roles
require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones

class NotificacionController
{
    private $notificacionModel; // Modelo de notificaciones

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->notificacionModel = new NotificacionModel(); // Instancia el modelo
    }

    public function index()
    {
        $notificaciones = $this->notificacionModel->obtenerPorRol('admin'); // Obtiene notificaciones admin

        require_once __DIR__ . '/../../views/admin/notificaciones.php'; // Carga la vista
    }

    public function marcarLeida()
    {
        if (isset($_GET['id'])) { // Verifica ID recibido

            $id = $_GET['id']; // ID de notificación

            $this->notificacionModel->marcarLeida($id); // Marca como leída
        }

        header('Location: notificacionController.php'); // Redirige al panel
        exit; // Detiene ejecución
    }

    public function eliminar()
    {
        if (isset($_GET['id'])) { // Verifica ID recibido

            $id = $_GET['id']; // ID de notificación

            $this->notificacionModel->eliminar($id); // Elimina notificación
        }

        header('Location: notificacionController.php'); // Redirige al panel
        exit; // Detiene ejecución
    }

    private function validarAdministrador()
    {
        validarAccesoAdministrador(); // Valida sesión admin
    }
}

$controller = new NotificacionController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga principal
}

?>
