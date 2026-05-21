<?php

require_once __DIR__ . '/../../models/comunicacion/chatModel.php'; // Importa chat
require_once __DIR__ . '/../../models/comunicacion/mensajeModel.php'; // Importa mensajes

class ClienteInsComunicacionController
{
    private $chatModel; // Modelo chat
    private $mensajeModel; // Modelo mensajes

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->chatModel = new ChatModel(); // Instancia chat
        $this->mensajeModel = new MensajeModel(); // Instancia mensajes
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $chat = $this->chatModel->obtenerPorCliente($clienteId); // Obtiene chat
        $mensajes = $this->mensajeModel->obtenerPorCliente($clienteId); // Obtiene mensajes

        require_once __DIR__ . '/../../views/clienteIns/comunicacion.php'; // Carga vista
    }

    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $_SESSION['cliente_id'], // ID cliente
                'mensaje' => trim($_POST['mensaje']), // Mensaje
                'emisor' => 'cliente_institucional' // Emisor
            ];

            $this->mensajeModel->crear($datos); // Guarda mensaje

            $this->mensajeModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Mensaje institucional enviado'); // Registra historial
        }

        header('Location: comunicacionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function validarClienteInstitucional()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'clienteins' && $rol !== 'cliente_institucional') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteInsComunicacionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta vista
}

?>