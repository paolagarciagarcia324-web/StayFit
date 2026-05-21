<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/comunicacion/chatModel.php'; // Importa chat
require_once __DIR__ . '/../../models/comunicacion/mensajeModel.php'; // Importa mensajes

class CoachComunicacionController
{
    private $coachModel; // Modelo coach
    private $chatModel; // Modelo chat
    private $mensajeModel; // Modelo mensaje

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->chatModel = new ChatModel(); // Instancia chat
        $this->mensajeModel = new MensajeModel(); // Instancia mensaje
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $chats = $this->chatModel->obtenerPorCoach($coachId); // Obtiene chats
        $mensajes = $this->mensajeModel->obtenerPorCoach($coachId); // Obtiene mensajes

        require_once __DIR__ . '/../../views/coach/comunicacion.php'; // Carga vista
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'mensaje' => trim($_POST['mensaje']), // Mensaje
                'emisor' => 'coach' // Emisor
            ];

            $this->mensajeModel->crear($datos); // Guarda mensaje

            $this->mensajeModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Mensaje enviado por coach'); // Registra historial
        }

        header('Location: comunicacionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function obtenerCoachId()
    {
        if (isset($_SESSION['coach_id'])) { // Verifica sesión
            return $_SESSION['coach_id']; // Retorna coach
        }

        $coach = $this->coachModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca coach

        $_SESSION['coach_id'] = $coach['id']; // Guarda coach

        return $coach['id']; // Retorna ID
    }

    private function validarCoach()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachComunicacionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>