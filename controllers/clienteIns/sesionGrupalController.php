<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class ClienteInsSesionGrupalController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $sesionModel; // Modelo sesiones

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->sesionModel = new SesionModel(); // Instancia sesión
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $sesionesGrupales = $this->sesionModel->obtenerGrupalesPorCliente($clienteId); // Obtiene grupales

        require_once __DIR__ . '/../../views/clienteIns/sesionesGrupales.php'; // Carga vista
    }

    public function confirmarAsistencia()
    {
        if (isset($_GET['id'])) { // Valida sesión recibida

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'sesion_id' => $_GET['id'], // ID sesión
                'estado' => 'confirmada' // Estado asistencia
            ];

            $this->sesionModel->confirmarAsistencia($datos); // Confirma asistencia

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Asistencia grupal confirmada'); // Registra historial
        }

        header('Location: sesionGrupalController.php'); // Redirige
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

$controller = new ClienteInsSesionGrupalController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>