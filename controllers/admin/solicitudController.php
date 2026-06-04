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
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header('Location: solicitudController.php');
            exit;
        }

        $solicitudes = $this->solicitudModel->obtenerTodas();
        $solicitud = $this->solicitudModel->obtenerPorId($id);
        $abrirModal = true;

        require_once __DIR__ . '/../../views/admin/solicitudes.php';
    }

    public function detalleFragment()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(404);
            exit('Solicitud no encontrada.');
        }

        $solicitud = $this->solicitudModel->obtenerPorId($id);

        if (!$solicitud) {
            http_response_code(404);
            exit('Solicitud no encontrada.');
        }

        require __DIR__ . '/../../views/admin/partials/solicitudDetalleContenido.php';
        exit;
    }

    public function marcarRevision()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            try {
                $this->solicitudModel->marcarRevision($id, $_SESSION['usuario_id'] ?? null);
                $this->registrarTrazabilidad('Solicitud marcada en revisión');
                $this->flash('success', 'Solicitud marcada en revisión.');
            } catch (Throwable $e) {
                $this->flash('error', 'No se pudo marcar en revisión: ' . $e->getMessage());
            }
        }

        header('Location: solicitudController.php');
        exit;
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
