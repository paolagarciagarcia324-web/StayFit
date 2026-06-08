<?php

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/institucion/institucionModel.php';
require_once __DIR__ . '/../../models/institucion/enlaceInstitucionalModel.php';
require_once __DIR__ . '/../../models/cliente/clienteInsModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';

class ClienteInstitucionalController
{
    private InstitucionModel $institucionModel;
    private EnlaceInstitucionalModel $enlaceModel;
    private ClienteInsModel $clienteInsModel;
    private PlanModel $planModel;

    public function __construct()
    {
        session_start();
        $this->validarAdministrador();

        $this->institucionModel = new InstitucionModel();
        $this->enlaceModel = new EnlaceInstitucionalModel();
        $this->clienteInsModel = new ClienteInsModel();
        $this->planModel = new PlanModel();
    }

    public function index()
    {
        $instituciones = $this->institucionModel->obtenerTodos();
        $planes = $this->planModel->obtenerPlanesInstitucionales();
        $enlaces = $this->enlaceModel->obtenerTodosConDetalle();
        $clientesInstitucionales = $this->clienteInsModel->obtenerTodos();

        $enlacesPorInstitucion = [];
        foreach ($enlaces as $enlace) {
            $enlacesPorInstitucion[(int) ($enlace['id_institucion'] ?? 0)] = $enlace;
        }

        $mapaInstituciones = [];
        foreach ($instituciones as $inst) {
            $mapaInstituciones[(int) ($inst['id'] ?? $inst['id_institucion'] ?? 0)] = $inst['nombre'] ?? 'Institución';
        }

        foreach ($clientesInstitucionales as &$cliente) {
            $idInst = (int) ($cliente['id_institucion'] ?? 0);
            $cliente['institucion'] = $mapaInstituciones[$idInst] ?? 'Sin institución';
            $cliente['cliente'] = trim(($cliente['nombre_completo'] ?? '')
                ?: trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')));
        }
        unset($cliente);

        require_once __DIR__ . '/../../views/admin/clienteInstitucional.php';
    }

    public function generarEnlace()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $institucionId = (int) ($_POST['institucion_id'] ?? 0);
        $planId = (int) ($_POST['plan_id'] ?? 0);

        if ($institucionId <= 0 || $planId <= 0) {
            $this->flash('error', 'Selecciona institución y plan.');
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $institucion = $this->institucionModel->obtenerPorId($institucionId);
        if (!$institucion || ($institucion['estado'] ?? '') !== 'activo') {
            $this->flash('error', 'La institución no existe o está inactiva.');
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $plan = $this->planModel->obtenerPorId($planId);
        if (!$plan || ($plan['estado'] ?? '') !== 'activo') {
            $this->flash('error', 'El plan seleccionado no está disponible.');
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $enlace = $this->enlaceModel->generarOActualizar(
            $institucionId,
            $planId,
            (int) ($_SESSION['usuario_id'] ?? 0) ?: null
        );

        if (!$enlace) {
            $this->flash('error', 'No se pudo generar el enlace. Verifica que la migración SQL esté aplicada.');
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $this->enlaceModel->registrarTrazabilidad(
            (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
            'Enlace generado para ' . ($institucion['nombre'] ?? 'institución')
        );

        $this->flash('success', 'Enlace generado correctamente. Compártelo con las personas de la institución.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }

    public function toggleEnlace()
    {
        $idEnlace = (int) ($_GET['id'] ?? 0);
        $activo = ($_GET['activo'] ?? '1') === '1';

        if ($idEnlace <= 0) {
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $this->enlaceModel->activarDesactivar($idEnlace, $activo);
        $this->enlaceModel->registrarTrazabilidad(
            (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
            $activo ? 'Enlace activado' : 'Enlace desactivado'
        );

        $this->flash('success', $activo ? 'Enlace activado.' : 'Enlace desactivado.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }

    public function regenerarToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $idEnlace = (int) ($_POST['id_enlace'] ?? 0);

        if ($idEnlace <= 0) {
            $this->flash('error', 'Enlace no válido.');
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $enlace = $this->enlaceModel->regenerarToken(
            $idEnlace,
            (int) ($_SESSION['usuario_id'] ?? 0) ?: null
        );

        if (!$enlace) {
            $this->flash('error', 'No se pudo regenerar el token.');
            header('Location: clienteInstitucionalController.php');
            exit;
        }

        $this->enlaceModel->registrarTrazabilidad(
            (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
            'Token regenerado para enlace #' . $idEnlace
        );

        $this->flash('success', 'Token regenerado. El enlace anterior ya no funciona.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }

    private function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    private function validarAdministrador(): void
    {
        $rol = strtolower($_SESSION['rol'] ?? '');

        if ($rol !== 'admin' && $rol !== 'administrador') {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new ClienteInstitucionalController();

$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
