<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/pago/comprobanteModel.php'; // Importa comprobantes

class ClienteInsPagoController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $pagoModel; // Modelo pagos
    private $comprobanteModel; // Modelo comprobantes

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->pagoModel = new PagoModel(); // Instancia pagos
        $this->comprobanteModel = new ComprobanteModel(); // Instancia comprobantes
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $pagos = $this->pagoModel->obtenerPorCliente($clienteId); // Obtiene pagos

        require_once __DIR__ . '/../../views/clienteIns/plan.php'; // Carga vista existente
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

            $datos = [
                'cliente_id' => $clienteId, // ID cliente
                'plan_id' => $_POST['plan_id'], // Plan seleccionado
                'monto' => $_POST['monto'], // Valor pagado
                'tipo_cuenta' => trim($_POST['tipo_cuenta']), // Tipo de cuenta
                'numero_cuenta' => trim($_POST['numero_cuenta']), // Número de cuenta
                'estado' => 'pendiente' // Estado inicial
            ];

            $pagoId = $this->pagoModel->crear($datos); // Crea pago

            $this->guardarComprobante($pagoId); // Guarda comprobante

            $this->pagoModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Pago enviado por cliente institucional'); // Registra historial
        }

        header('Location: pagoController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function guardarComprobante($pagoId)
    {
        if (!isset($_FILES['comprobante'])) { // Valida archivo
            return; // Sale si no hay archivo
        }

        $datos = [
            'pago_id' => $pagoId, // ID pago
            'nombre_archivo' => $_FILES['comprobante']['name'], // Nombre archivo
            'ruta_archivo' => $_FILES['comprobante']['tmp_name'], // Ruta temporal
            'tipo_archivo' => $_FILES['comprobante']['type'] // Tipo archivo
        ];

        $this->comprobanteModel->crear($datos); // Guarda comprobante
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteInsModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda cliente en sesión

        return $cliente['id']; // Retorna ID
    }

    private function validarClienteInstitucional()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'clienteins' && $rol !== 'cliente_institucional') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteInsPagoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>