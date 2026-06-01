<?php

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/solicitud/solicitudIngresoModel.php'; // Importa el modelo de solicitudes

class SolicitudController
{
    private $solicitudModel; // Modelo de solicitudes

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->solicitudModel = new SolicitudIngresoModel(); // Instancia el modelo
    }

    public function index()
    {
        $solicitudes = $this->solicitudModel->obtenerTodas(); // Obtiene todas las solicitudes
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require_once __DIR__ . '/../../views/admin/solicitudes.php'; // Carga la vista
    }

    public function pendientes()
    {
        $solicitudes = $this->solicitudModel->obtenerPorEstado('pendiente'); // Obtiene solicitudes pendientes

        require_once __DIR__ . '/../../views/admin/solicitudes.php'; // Carga la vista
    }

    public function detalle()
    {
        $id = $_GET['id'] ?? null; // Obtiene el ID de la solicitud

        if (!$id) { // Valida si existe el ID
            header('Location: solicitudController.php'); // Redirige al listado
            exit; // Detiene la ejecución
        }

        $solicitudes = $this->solicitudModel->obtenerTodas();
        $solicitud = $this->solicitudModel->obtenerPorId($id);

        require_once __DIR__ . '/../../views/admin/solicitudes.php';
    }

    public function marcarRevision()
    {
        if (isset($_GET['id'])) { // Verifica el ID recibido

            $id = $_GET['id']; // ID de la solicitud

            $this->solicitudModel->marcarRevision($id); // Marca en revisión

            $this->registrarTrazabilidad('Solicitud marcada en revisión'); // Guarda trazabilidad
        }

        header('Location: solicitudController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    public function rechazar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'id' => $_POST['id'], // ID de la solicitud
                'estado' => 'rechazada', // Estado final
                'observacion' => trim($_POST['observacion']) // Motivo del rechazo
            ];

            $this->solicitudModel->rechazar($datos); // Rechaza la solicitud

            $this->registrarTrazabilidad('Solicitud rechazada'); // Guarda trazabilidad
        }

        header('Location: solicitudController.php'); // Redirige al listado
        exit; // Detiene la ejecución
    }

    private function flash($tipo, $mensaje)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->solicitudModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        validarAccesoAdministrador(); // Valida sesión admin
    }
}

$controller = new SolicitudController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>
