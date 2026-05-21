<?php

require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa el modelo de pagos
require_once __DIR__ . '/../../models/pago/comprobanteModel.php'; // Importa comprobantes

class PagoController
{
    private $pagoModel; // Modelo de pagos
    private $comprobanteModel; // Modelo de comprobantes

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->pagoModel = new PagoModel(); // Instancia pagos
        $this->comprobanteModel = new ComprobanteModel(); // Instancia comprobantes
    }

    public function index()
    {
        $pagos = $this->pagoModel->obtenerTodos(); // Obtiene todos los pagos
        $pendientes = $this->pagoModel->obtenerPendientes(); // Obtiene pagos pendientes

        require_once __DIR__ . '/../../views/admin/pagos.php'; // Carga la vista
    }

    public function detalle()
    {
        $id = $_GET['id'] ?? null; // Obtiene ID del pago

        if (!$id) { // Valida ID
            header('Location: pagoController.php'); // Redirige al listado
            exit; // Detiene ejecución
        }

        $pago = $this->pagoModel->obtenerPorId($id); // Obtiene pago
        $comprobante = $this->comprobanteModel->obtenerPorPago($id); // Obtiene comprobante

        require_once __DIR__ . '/../../views/admin/pagos.php'; // Carga vista
    }

    public function aprobar()
    {
        if (isset($_GET['id'])) { // Verifica ID recibido

            $id = $_GET['id']; // ID del pago

            $this->pagoModel->cambiarEstado($id, 'aprobado'); // Aprueba pago

            $this->registrarTrazabilidad('Pago aprobado'); // Guarda trazabilidad
        }

        header('Location: pagoController.php'); // Redirige al panel
        exit; // Detiene ejecución
    }

    public function rechazar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica formulario

            $datos = [
                'id' => $_POST['id'], // ID del pago
                'estado' => 'rechazado', // Estado del pago
                'observacion' => trim($_POST['observacion']) // Motivo del rechazo
            ];

            $this->pagoModel->rechazar($datos); // Rechaza pago

            $this->registrarTrazabilidad('Pago rechazado'); // Guarda trazabilidad
        }

        header('Location: pagoController.php'); // Redirige al panel
        exit; // Detiene ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->pagoModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
            exit; // Detiene ejecución
        }
    }
}

$controller = new PagoController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga principal
}

?>