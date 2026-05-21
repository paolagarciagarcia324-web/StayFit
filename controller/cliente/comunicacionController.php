<?php

require_once __DIR__ . '/../../models/comunicacion/chatModel.php'; // Importa chat
require_once __DIR__ . '/../../models/comunicacion/mensajeModel.php'; // Importa mensajes

class ClienteComunicacionController
{
    private $chatModel; // Modelo de chat
    private $mensajeModel; // Modelo de mensajes

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarCliente(); // Valida acceso del cliente

        $this->chatModel = new ChatModel(); // Instancia chat
        $this->mensajeModel = new MensajeModel(); // Instancia mensajes
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $chat = $this->chatModel->obtenerPorCliente($clienteId); // Obtiene chat
        $mensajes = $this->mensajeModel->obtenerPorCliente($clienteId); // Obtiene mensajes

        require_once __DIR__ . '/../../views/cliente/comunicacion.php'; // Carga vista
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida envío

            $datos = [
                'cliente_id' => $_SESSION['cliente_id'], // ID cliente
                'mensaje' => trim($_POST['mensaje']), // Mensaje enviado
                'emisor' => 'cliente' // Emisor
            ];

            $this->mensajeModel->crear($datos); // Guarda mensaje

            $this->mensajeModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Mensaje enviado al coach'); // Registra historial
        }

        header('Location: comunicacionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'cliente') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteComunicacionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga vista
}

?>
