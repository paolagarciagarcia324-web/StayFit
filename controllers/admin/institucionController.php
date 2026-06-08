<?php

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/institucion/institucionModel.php';
require_once __DIR__ . '/../../models/institucion/enlaceInstitucionalModel.php';
require_once __DIR__ . '/../../models/cliente/clienteInsModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';

class InstitucionController
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
        $clientesInstitucionales = $this->clienteInsModel->obtenerTodos();
        $planes = $this->planModel->obtenerPlanesInstitucionales();
        $enlaces = $this->enlaceModel->obtenerTodosConDetalle();

        $enlacesPorInstitucion = [];
        foreach ($enlaces as $enlace) {
            $enlacesPorInstitucion[(int) ($enlace['id_institucion'] ?? 0)] = $enlace;
        }

        require_once __DIR__ . '/../../views/admin/instituciones.php';
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: institucionController.php');
            exit;
        }

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'nit' => trim($_POST['nit'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'estado' => 'activo',
        ];

        if ($datos['nombre'] === '' || $planId <= 0) {
            $this->flash('error', 'Completa los datos de la institución y selecciona un plan de convenio.');
            header('Location: institucionController.php');
            exit;
        }

        if (!$this->validarPlan($planId)) {
            $this->flash('error', 'El plan seleccionado no es válido o no está activo.');
            header('Location: institucionController.php');
            exit;
        }

        $idInstitucion = $this->institucionModel->crear($datos);

        if (!$idInstitucion) {
            $this->flash('error', 'No se pudo crear la institución.');
            header('Location: institucionController.php');
            exit;
        }

        $enlace = $this->enlaceModel->sincronizarEnlace(
            $idInstitucion,
            $planId,
            (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
            false
        );

        if (!$enlace) {
            $this->flash('warning', 'Institución creada, pero no se pudo generar el enlace de registro. Edítala para intentar de nuevo.');
        } else {
            $this->flash('success', 'Institución creada con plan y enlace de registro listos para compartir.');
        }

        $this->registrarTrazabilidad('Institución registrada con plan #' . $planId);
        header('Location: institucionController.php');
        exit;
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: institucionController.php');
            exit;
        }

        $idInstitucion = (int) ($_POST['id'] ?? 0);
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $regenerarEnlace = !empty($_POST['regenerar_enlace']);

        $datos = [
            'id' => $idInstitucion,
            'nombre' => trim($_POST['nombre'] ?? ''),
            'nit' => trim($_POST['nit'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'estado' => $_POST['estado'] ?? 'activo',
        ];

        if ($idInstitucion <= 0 || $datos['nombre'] === '' || $planId <= 0) {
            $this->flash('error', 'Datos incompletos para actualizar la institución.');
            header('Location: institucionController.php');
            exit;
        }

        if (!$this->validarPlan($planId)) {
            $this->flash('error', 'El plan seleccionado no es válido.');
            header('Location: institucionController.php');
            exit;
        }

        if (!$this->institucionModel->actualizar($datos)) {
            $this->flash('error', 'No se pudo actualizar la institución.');
            header('Location: institucionController.php');
            exit;
        }

        $adminId = (int) ($_SESSION['usuario_id'] ?? 0) ?: null;
        $enlace = $this->enlaceModel->sincronizarEnlace($idInstitucion, $planId, $adminId, $regenerarEnlace);

        if (!$enlace) {
            $this->flash('warning', 'Institución actualizada, pero el enlace no pudo sincronizarse.');
        } elseif ($regenerarEnlace) {
            $this->flash('success', 'Institución, plan y enlace actualizados. El enlace anterior ya no funciona.');
        } else {
            $this->flash('success', 'Institución y plan del enlace actualizados. El mismo enlace sigue activo.');
        }

        $this->registrarTrazabilidad('Institución actualizada (plan #' . $planId . ')');
        header('Location: institucionController.php');
        exit;
    }

    public function vincularCliente()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'cliente_id' => $_POST['cliente_id'],
                'institucion_id' => $_POST['institucion_id'],
                'cargo' => trim($_POST['cargo'] ?? ''),
                'estado' => 'activo',
            ];

            $this->clienteInsModel->vincularInstitucion($datos);
            $this->registrarTrazabilidad('Cliente vinculado a institución');
        }

        header('Location: institucionController.php');
        exit;
    }

    public function cambiarEstado()
    {
        if (isset($_GET['id'], $_GET['estado'])) {
            $this->institucionModel->cambiarEstado($_GET['id'], $_GET['estado']);
            $this->registrarTrazabilidad('Estado de institución cambiado');
        }

        header('Location: institucionController.php');
        exit;
    }

    private function validarPlan(int $planId): bool
    {
        $plan = $this->planModel->obtenerPorId($planId);

        return $plan && ($plan['estado'] ?? '') === 'activo';
    }

    private function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    private function registrarTrazabilidad(string $accion): void
    {
        $adminId = $_SESSION['usuario_id'] ?? null;
        $this->institucionModel->registrarTrazabilidad($adminId, $accion);
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

$controller = new InstitucionController();

$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
