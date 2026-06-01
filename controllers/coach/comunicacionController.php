<?php

require_once __DIR__ . '/../../models/coach/coachModel.php';
require_once __DIR__ . '/../../models/comunicacion/chatModel.php';
require_once __DIR__ . '/../../models/comunicacion/mensajeModel.php';

class CoachComunicacionController
{
    private $coachModel;
    private $chatModel;
    private $mensajeModel;

    public function __construct()
    {
        session_start();

        $this->validarCoach();

        $this->coachModel = new CoachModel();
        $this->chatModel = new ChatModel();
        $this->mensajeModel = new MensajeModel();
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $chats = $this->chatModel->obtenerPorCoach($coachId);
        $mensajes = $this->mensajeModel->obtenerPorCoach($coachId);
        $clientes = $this->coachModel->obtenerClientesAsignados($coachId);

        require_once __DIR__ . '/../../views/coach/comunicacion.php';
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $coachId = $this->obtenerCoachId();
            $clienteId = (int) ($_POST['cliente_id'] ?? 0);
            $mensaje = trim($_POST['mensaje'] ?? '');

            if ($clienteId < 1 || $mensaje === '') {
                $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Selecciona un cliente y escribe un mensaje.'];
            } else {
                try {
                    $this->mensajeModel->crear([
                        'coach_id' => $coachId,
                        'cliente_id' => $clienteId,
                        'usuario_id' => $_SESSION['usuario_id'] ?? $coachId,
                        'mensaje' => $mensaje,
                    ]);

                    $this->mensajeModel->registrarTrazabilidad(
                        $_SESSION['usuario_id'] ?? null,
                        'Mensaje enviado por coach'
                    );

                    $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Mensaje enviado correctamente.'];
                } catch (Throwable $e) {
                    $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => $e->getMessage()];
                }
            }
        }

        header('Location: comunicacionController.php');
        exit;
    }

    private function obtenerCoachId()
    {
        if (isset($_SESSION['coach_id'])) {
            return (int) $_SESSION['coach_id'];
        }

        $coach = $this->coachModel->obtenerPorUsuario($_SESSION['usuario_id'] ?? 0);

        if (!$coach) {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }

        $_SESSION['coach_id'] = $coach['id'] ?? $coach['id_usuario'];

        return (int) $_SESSION['coach_id'];
    }

    private function validarCoach()
    {
        $rol = strtolower($_SESSION['rol'] ?? '');

        if ($rol !== 'coach') {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new CoachComunicacionController();

$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}
