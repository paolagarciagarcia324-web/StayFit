<?php

require_once __DIR__ . '/../../models/comunicacion/chatModel.php';
require_once __DIR__ . '/../../models/comunicacion/mensajeModel.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';

class ClienteComunicacionController
{
    private $chatModel;
    private $mensajeModel;
    private $clienteModel;

    public function __construct()
    {
        session_start();

        $this->validarCliente();

        $this->chatModel = new ChatModel();
        $this->mensajeModel = new MensajeModel();
        $this->clienteModel = new ClienteModel();
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId();
        $coachId = $this->clienteModel->obtenerIdCoachAsignado($clienteId);
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $chat = null;
        $mensajes = [];
        $sinCoach = ($coachId < 1);

        if (!$sinCoach) {
            $chat = $this->chatModel->obtenerOCrear($clienteId, $coachId);
            $mensajes = $this->mensajeModel->obtenerPorCliente($clienteId);
        }

        require_once __DIR__ . '/../../views/cliente/comunicacion.php';
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clienteId = $this->obtenerClienteId();
            $mensaje = trim($_POST['mensaje'] ?? '');

            if ($mensaje === '') {
                $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Escribe un mensaje antes de enviar.'];
            } else {
                try {
                    $this->mensajeModel->crear([
                        'cliente_id' => $clienteId,
                        'usuario_id' => $_SESSION['usuario_id'] ?? $clienteId,
                        'mensaje' => $mensaje,
                    ]);

                    $this->mensajeModel->registrarTrazabilidad(
                        $_SESSION['usuario_id'] ?? null,
                        'Mensaje enviado al coach'
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

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) {
            return (int) $_SESSION['cliente_id'];
        }

        $cliente = $this->clienteModel->obtenerPorUsuario($_SESSION['usuario_id'] ?? 0);

        if (!$cliente) {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }

        $_SESSION['cliente_id'] = $cliente['id'] ?? $cliente['id_usuario'];

        return (int) $_SESSION['cliente_id'];
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? '');

        if ($rol !== 'cliente') {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new ClienteComunicacionController();

$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}
