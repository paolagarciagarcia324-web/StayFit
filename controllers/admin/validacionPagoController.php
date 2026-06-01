<?php

require_once __DIR__ . '/../../config/roles.php'; // Validaci?n de roles
require_once __DIR__ . '/../../models/solicitud/solicitudIngresoModel.php'; // Importa solicitudes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuarios
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes

class ValidacionPagoController
{
    private $solicitudModel; // Modelo de solicitudes
    private $pagoModel; // Modelo de pagos
    private $usuarioModel; // Modelo de usuarios
    private $clienteModel; // Modelo de clientes
    private $accesoModel; // Modelo de accesos
    private $planModel; // Modelo de planes

    public function __construct()
    {
        session_start(); // Inicia la sesi?n

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->solicitudModel = new SolicitudIngresoModel(); // Instancia solicitudes
        $this->pagoModel = new PagoModel(); // Instancia pagos
        $this->usuarioModel = new UsuarioModel(); // Instancia usuarios
        $this->clienteModel = new ClienteModel(); // Instancia clientes
        $this->accesoModel = new AccesoModel(); // Instancia accesos
        $this->planModel = new PlanModel(); // Instancia planes
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
            exit; // Detiene la ejecuci?n
        }

        $solicitudId = $_GET['solicitud_id']; // ID de la solicitud

        $solicitud = $this->solicitudModel->obtenerPorId($solicitudId); // Busca solicitud

        if (!$solicitud) { // Valida existencia
            header('Location: validacionPagoController.php'); // Redirige si no existe
            exit; // Detiene la ejecuci?n
        }

        try {
            if (strtoupper($solicitud['estado'] ?? '') === 'APROBADA') {
                throw new RuntimeException('Esta solicitud ya fue aprobada.');
            }

            $usuarioId = $this->crearUsuarioCliente($solicitud);
            $this->crearCliente($usuarioId, $solicitud);
            $clienteId = $usuarioId;

            $planClienteId = $this->clienteModel->crearPlanClienteDesdeSolicitud($clienteId, $solicitud);

            if (!$planClienteId) {
                $planId = $this->clienteModel->resolverIdPlanDesdeSolicitud($solicitud);
                if ($planId && $this->clienteModel->asignarPlanCliente($clienteId, $planId, null)) {
                    $planClienteId = $this->clienteModel->obtenerPlanClienteActivoId($clienteId);
                }
            }

            if (!$planClienteId) {
                throw new RuntimeException('No se pudo vincular el plan al cliente. Verifique que existan planes activos.');
            }

            $this->pagoModel->vincularPlanClientePorSolicitud($solicitudId, $planClienteId);

            try {
                $this->activarAccesos($clienteId, $solicitud, $planClienteId);
            } catch (Throwable $e) {
            }

            $this->pagoModel->aprobarPorSolicitud($solicitudId);
            $this->solicitudModel->cambiarEstado($solicitudId, 'APROBADA');
            $this->registrarTrazabilidad('Pago aprobado y cliente activado');

            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Solicitud aprobada y cliente activado correctamente.'];
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'No se pudo aprobar: ' . $e->getMessage()];
        }

        header('Location: solicitudController.php');
        exit;
    }

    public function rechazar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica env?o del formulario

            $datos = [
                'solicitud_id' => $_POST['solicitud_id'], // ID de solicitud
                'estado' => 'rechazado', // Estado del pago
                'observacion' => trim($_POST['observacion']) // Motivo del rechazo
            ];

            $this->pagoModel->rechazarPorSolicitud($datos); // Rechaza el pago

            $this->solicitudModel->rechazar([
                'id' => $_POST['solicitud_id'],
                'observacion' => $datos['observacion']
            ]); // Rechaza solicitud

            $this->registrarTrazabilidad('Pago rechazado'); // Guarda trazabilidad
            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Solicitud rechazada.'];
        }

        header('Location: solicitudController.php');
        exit;
    }

    private function crearUsuarioCliente($solicitud)
    {
        require_once __DIR__ . '/../../config/helpers.php';

        $identificacion = trim($solicitud['identificacion'] ?? '');
        $correo = !empty($solicitud['correo'])
            ? trim($solicitud['correo'])
            : strtolower($identificacion) . '@stayfit.local';
        $partes = dividirNombreCompleto($solicitud['nombre_completo'] ?? $solicitud['nombre'] ?? '');

        $existente = $this->usuarioModel->obtenerPorCorreo($correo);

        if (!$existente && $identificacion !== '') {
            $existente = $this->usuarioModel->obtenerPorDocumentoIdentidad($identificacion);
        }

        if ($existente) {
            $usuarioId = (int) ($existente['id'] ?? $existente['id_usuario']);
            $this->usuarioModel->activarDesdeSolicitud($usuarioId, [
                'nombre' => $partes['nombre'],
                'apellido' => $partes['apellido'],
                'telefono' => $solicitud['celular'] ?? null,
                'documento_identidad' => $identificacion,
            ]);
            $this->usuarioModel->asignarRol($usuarioId, 3);

            return $usuarioId;
        }

        $datos = [
            'nombre' => $partes['nombre'],
            'apellido' => $partes['apellido'],
            'correo' => $correo,
            'password' => $identificacion,
            'telefono' => $solicitud['celular'] ?? null,
            'documento_identidad' => $identificacion,
            'origen_registro' => 'ADMINISTRATIVO',
            'estado' => 'ACTIVO',
        ];

        $usuarioId = $this->usuarioModel->crear($datos);
        $this->usuarioModel->asignarRol($usuarioId, 3);

        return $usuarioId;
    }

    private function crearCliente($usuarioId, $solicitud)
    {
        $datos = [
            'usuario_id' => $usuarioId,
            'edad' => $solicitud['edad'],
            'tipo_cliente' => $solicitud['tipo_cliente'] ?? 'individual',
            'fecha_nacimiento' => edadAFechaNacimiento($solicitud['edad'] ?? null),
        ];

        return $this->clienteModel->crearDesdeSolicitud($datos);
    }

    private function activarAccesos($clienteId, $solicitud, $planClienteId)
    {
        $planId = $this->clienteModel->resolverIdPlanDesdeSolicitud($solicitud);
        $planCatalogo = $planId ? $this->planModel->obtenerPorId($planId) : null;

        $modalidad = strtolower($solicitud['modalidad'] ?? $planCatalogo['modalidad'] ?? 'virtual');

        $plan = [
            'id' => $planClienteId,
            'modalidad' => $modalidad,
            'incluye_entrenamiento' => $planCatalogo['incluye_entrenamiento'] ?? 1,
            'incluye_nutricion' => $planCatalogo['incluye_nutricion'] ?? 0,
            'requiere_coach' => $planCatalogo['requiere_coach'] ?? 0,
            'duracion' => $planCatalogo['duracion_dias'] ?? 30,
        ];

        $this->accesoModel->crearAccesosPorPlan($clienteId, $plan);
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->pagoModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesi?n

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
            exit; // Detiene la ejecuci?n
        }
    }
}

$controller = new ValidacionPagoController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acci?n por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el m?todo
    $controller->$accion(); // Ejecuta la acci?n
} else {
    $controller->index(); // Carga vista principal
}

?>
