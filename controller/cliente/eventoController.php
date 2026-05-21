<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class ClienteEventoController
{
    private $clienteModel; // Modelo cliente
    private $sesionModel; // Modelo sesiones

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCliente(); // Valida acceso

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->sesionModel = new SesionModel(); // Instancia sesiones
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente

        $eventos = $this->sesionModel->obtenerEventosPorCliente($clienteId); // Obtiene eventos

        require_once __DIR__ . '/../../views/cliente/calendario.php'; // Carga vista
    }

    public function inscribirse()
    {
        if (isset($_GET['id'])) { // Valida evento recibido

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'evento_id' => $_GET['id'], // ID evento
                'estado' => 'inscrito' // Estado inicial
            ];

            $this->sesionModel->inscribirClienteEvento($datos); // Inscribe cliente

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Cliente inscrito a evento'); // Registra historial
        }

        header('Location: eventoController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda cliente

        return $cliente['id']; // Retorna ID
    }

    private function validarCliente()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'cliente') { // Valida cliente
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteEventoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
