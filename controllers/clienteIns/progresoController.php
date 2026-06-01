<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso

class ClienteInsProgresoController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $progresoModel; // Modelo progreso

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->progresoModel = new ProgresoModel(); // Instancia progreso
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $progresos = $this->progresoModel->obtenerPorCliente($clienteId); // Obtiene historial

        require_once __DIR__ . '/../../views/clienteIns/progreso.php'; // Carga vista
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'peso' => $_POST['peso'], // Peso actual
                'medidas' => trim($_POST['medidas'] ?? ''), // Medidas
                'observacion' => trim($_POST['observacion'] ?? ''), // Observación
                'foto' => $_FILES['foto']['name'] ?? null // Foto progreso
            ];

            $this->progresoModel->crear($datos); // Guarda progreso

            $this->progresoModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Progreso institucional registrado'); // Registra historial
        }

        header('Location: progresoController.php'); // Redirige
        exit; // Detiene ejecución
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteInsModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda cliente en sesión

        return $cliente['id']; // Retorna ID
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

$controller = new ClienteInsProgresoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>