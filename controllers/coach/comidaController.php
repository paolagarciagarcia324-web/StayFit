<?php

require_once __DIR__ . '/../../models/coach/coachModel.php';
require_once __DIR__ . '/../../models/nutricion/comidaModel.php';
require_once __DIR__ . '/../../models/nutricion/planNutricionalModel.php';

class CoachComidaController
{
    private $coachModel;
    private $comidaModel;
    private $planNutricionalModel;

    public function __construct()
    {
        session_start();

        $this->validarCoach();

        $this->coachModel = new CoachModel();
        $this->comidaModel = new ComidaModel();
        $this->planNutricionalModel = new PlanNutricionalModel();
    }

    public function index()
    {
        header('Location: nutricionController.php');
        exit;
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: nutricionController.php');
            exit;
        }

        $coachId = $this->obtenerCoachId();
        $planId = (int) ($_POST['plan_nutricional_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');

        if ($planId < 1 || $nombre === '') {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Selecciona un plan e ingresa el nombre de la comida.'];
            header('Location: nutricionController.php');
            exit;
        }

        if (!$this->planPerteneceAlCoach($planId, $coachId)) {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'El plan seleccionado no pertenece a tu panel.'];
            header('Location: nutricionController.php');
            exit;
        }

        try {
            $ok = $this->comidaModel->crear([
                'plan_nutricional_id' => $planId,
                'id_plan_nutricional' => $planId,
                'nombre' => $nombre,
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'hora' => $_POST['hora'] ?? null,
                'hora_sugerida' => $_POST['hora'] ?? null,
                'calorias' => $_POST['calorias'] !== '' ? $_POST['calorias'] : null,
                'calorias_aprox' => $_POST['calorias'] !== '' ? $_POST['calorias'] : null,
                'tipo_comida' => $_POST['tipo_comida'] ?? 'OTRO',
            ]);

            if (!$ok) {
                throw new RuntimeException('No se pudo guardar la comida.');
            }

            $this->comidaModel->registrarTrazabilidad($_SESSION['usuario_id'] ?? null, 'Comida nutricional creada por coach');
            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Comida agregada al plan correctamente.'];
        } catch (Throwable $e) {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Error al guardar la comida: ' . $e->getMessage()];
        }

        header('Location: nutricionController.php');
        exit;
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id' => $_POST['id'],
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'hora' => $_POST['hora'],
                'calorias' => $_POST['calorias'] ?? null,
                'estado' => $_POST['estado'],
            ];

            $this->comidaModel->actualizar($datos);
            $this->comidaModel->registrarTrazabilidad($_SESSION['usuario_id'] ?? null, 'Comida nutricional actualizada');
            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Comida actualizada correctamente.'];
        }

        header('Location: nutricionController.php');
        exit;
    }

    private function planPerteneceAlCoach(int $planId, int $coachId): bool
    {
        foreach ($this->planNutricionalModel->obtenerPorCoach($coachId) as $plan) {
            if ((int) ($plan['id'] ?? $plan['id_plan_nutricional'] ?? 0) === $planId) {
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

$controller = new CoachComidaController();

$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
