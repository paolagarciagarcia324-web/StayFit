<?php

require_once __DIR__ . '/../../models/coach/coachModel.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/nutricion/planNutricionalModel.php';
require_once __DIR__ . '/../../models/nutricion/comidaModel.php';

class CoachNutricionController
{
    private $coachModel;
    private $clienteModel;
    private $planNutricionalModel;
    private $comidaModel;

    public function __construct()
    {
        session_start();

        $this->validarCoach();

        $this->coachModel = new CoachModel();
        $this->clienteModel = new ClienteModel();
        $this->planNutricionalModel = new PlanNutricionalModel();
        $this->comidaModel = new ComidaModel();
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId();

        $clientes = $this->clienteModel->obtenerPorCoach($coachId);
        $planesNutricionales = $this->planNutricionalModel->obtenerPorCoach($coachId);
        $comidas = $this->comidaModel->obtenerPorCoach($coachId);
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require_once __DIR__ . '/../../views/coach/nutricion.php';
    }

    public function crearPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: nutricionController.php');
            exit;
        }

        $coachId = $this->obtenerCoachId();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $objetivo = trim($_POST['objetivo'] ?? '');

        if ($clienteId < 1 || $nombre === '' || $objetivo === '') {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Completa cliente, nombre y objetivo nutricional.'];
            header('Location: nutricionController.php');
            exit;
        }

        if (!$this->clientePerteneceAlCoach($clienteId, $coachId)) {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'La clienta seleccionada no está asignada a tu perfil de coach.'];
            header('Location: nutricionController.php');
            exit;
        }

        try {
            $planId = $this->planNutricionalModel->crear([
                'coach_id' => $coachId,
                'id_coach' => $coachId,
                'cliente_id' => $clienteId,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'objetivo' => $objetivo,
                'estado' => 'ACTIVO',
            ]);

            if (!$planId) {
                throw new RuntimeException('No se pudo guardar el plan en la base de datos.');
            }

            $this->planNutricionalModel->registrarTrazabilidad(
                $_SESSION['usuario_id'] ?? null,
                'Plan nutricional creado por coach'
            );

            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Plan nutricional creado correctamente.'];
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Error al crear el plan: ' . $e->getMessage()];
        }

        header('Location: nutricionController.php');
        exit;
    }

    public function actualizarPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id' => $_POST['id'],
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'objetivo' => trim($_POST['objetivo']),
                'estado' => $_POST['estado'],
            ];

            $this->planNutricionalModel->actualizar($datos);
            $this->planNutricionalModel->registrarTrazabilidad($_SESSION['usuario_id'] ?? null, 'Plan nutricional actualizado');
            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Plan actualizado correctamente.'];
        }

        header('Location: nutricionController.php');
        exit;
    }

    private function clientePerteneceAlCoach(int $clienteId, int $coachId): bool
    {
        foreach ($this->clienteModel->obtenerPorCoach($coachId) as $cliente) {
            if ((int) ($cliente['id'] ?? $cliente['id_cliente'] ?? 0) === $clienteId) {
                return true;
            }
        }

        return false;
    }

    private function obtenerCoachId()
    {
        if (isset($_SESSION['coach_id'])) {
            return (int) $_SESSION['coach_id'];
        }

        $coach = $this->coachModel->obtenerPorUsuario($_SESSION['usuario_id']);
        $_SESSION['coach_id'] = $coach['id'];

        return (int) $coach['id'];
    }

    private function validarCoach()
    {
        if (strtolower($_SESSION['rol'] ?? '') !== 'coach') {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new CoachNutricionController();

$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
