<?php

require_once __DIR__ . '/../../models/solicitud/solicitudIngresoModel.php'; // Importa solicitudes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuarios
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos

class ValidacionPagoController
{
    private $solicitudModel; // Modelo de solicitudes
    private $pagoModel; // Modelo de pagos
    private $usuarioModel; // Modelo de usuarios
    private $clienteModel; // Modelo de clientes
    private $accesoModel; // Modelo de accesos

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->solicitudModel = new SolicitudIngresoModel(); // Instancia solicitudes
        $this->pagoModel = new PagoModel(); // Instancia pagos
        $this->usuarioModel = new UsuarioModel(); // Instancia usuarios
        $this->clienteModel = new ClienteModel(); // Instancia clientes
        $this->accesoModel = new AccesoModel(); // Instancia accesos
    }

    public function index()
    {
        $pagosPendientes = $this->pagoModel->obtenerPendientes(); // Obtiene pagos pendientes

        require_once __DIR__ . '/../../views/admin/pagos.php'; // Carga la vista
    }

    public function aprobar()
    {
        if (!isset($_GET['solicitud_id'])) { // Verifica solicitud recibida
            header('Location: validacionPagoController.php'); // Redirige al panel
            exit; // Detiene la ejecución
        }

        $solicitudId = $_GET['solicitud_id']; // ID de la solicitud

        $solicitud = $this->solicitudModel->obtenerPorId($solicitudId); // Busca solicitud

        if (!$solicitud) { // Valida existencia
            header('Location: validacionPagoController.php'); // Redirige si no existe
            exit; // Detiene la ejecución
        }

        $usuarioId = $this->crearUsuarioCliente($solicitud); // Crea usuario cliente

        $clienteId = $this->crearCliente($usuarioId, $solicitud); // Crea perfil cliente

        $this->activarAccesos($clienteId, $solicitud); // Activa módulos comprados

        $this->pagoModel->aprobarPorSolicitud($solicitudId); // Aprueba el pago

        $this->solicitudModel->cambiarEstado($solicitudId, 'aprobada'); // Aprueba solicitud

        $this->registrarTrazabilidad('Pago aprobado y cliente activado'); // Guarda trazabilidad

        header('Location: validacionPagoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function rechazar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'solicitud_id' => $_POST['solicitud_id'], // ID de solicitud
                'estado' => 'rechazado', // Estado del pago
                'observacion' => trim($_POST['observacion']) // Motivo del rechazo
            ];

            $this->pagoModel->rechazarPorSolicitud($datos); // Rechaza el pago

            $this->solicitudModel->cambiarEstado($_POST['solicitud_id'], 'rechazada'); // Rechaza solicitud

            $this->registrarTrazabilidad('Pago rechazado'); // Guarda trazabilidad
        }

        header('Location: validacionPagoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function crearUsuarioCliente($solicitud)
    {
        $datos = [
            'nombre' => $solicitud['nombre'], // Nombre del solicitante
            'correo' => $solicitud['correo'] ?? null, // Correo si existe
            'password' => password_hash($solicitud['identificacion'], PASSWORD_DEFAULT), // Contraseña inicial
            'rol' => 'cliente', // Rol asignado
            'estado' => 'activo' // Estado inicial
        ];

        return $this->usuarioModel->crear($datos); // Retorna ID creado
    }

    private function crearCliente($usuarioId, $solicitud)
    {
        $datos = [
            'usuario_id' => $usuarioId, // ID del usuario
            'identificacion' => $solicitud['identificacion'], // Documento
            'edad' => $solicitud['edad'], // Edad
            'celular' => $solicitud['celular'], // Celular
            'tipo_cliente' => $solicitud['tipo_cliente'] ?? 'individual', // Tipo cliente
            'estado' => 'activo' // Estado del cliente
        ];

        return $this->clienteModel->crearDesdeSolicitud($datos); // Retorna ID cliente
    }

    private function activarAccesos($clienteId, $solicitud)
    {
        $modalidad = strtolower($solicitud['modalidad']); // Modalidad comprada

        $datos = [
            'cliente_id' => $clienteId, // ID del cliente
            'plan_id' => $solicitud['plan_id'], // Plan comprado
            'modalidad' => $modalidad, // Modalidad del plan
            'entrenamiento' => 1, // Activa entrenamiento
            'nutricion' => $solicitud['incluye_nutricion'] ?? 0, // Activa nutrición
            'contenido_virtual' => ($modalidad === 'virtual' || $modalidad === 'mixta') ? 1 : 0, // Activa videos
            'acompanamiento' => ($modalidad === 'presencial' || $modalidad === 'mixta') ? 1 : 0, // Activa coach
            'estado' => 'activo' // Estado del acceso
        ];

        $this->accesoModel->crear($datos); // Crea acceso del cliente
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
            exit; // Detiene la ejecución
        }
    }
}

$controller = new ValidacionPagoController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>
