<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/entrenamiento/planEntrenamientoModel.php'; // Importa plan
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php'; // Importa videos
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa avance

class ClienteEntrenamientoController
{
    private $clienteModel; // Modelo cliente
    private $planEntrenamientoModel; // Modelo entrenamiento
    private $rutinaModel; // Modelo rutinas
    private $videoModel; // Modelo videos
    private $progresoVideoModel; // Modelo avance virtual

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCliente(); // Valida acceso

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->planEntrenamientoModel = new PlanEntrenamientoModel(); // Instancia plan
        $this->rutinaModel = new RutinaModel(); // Instancia rutinas
        $this->videoModel = new VideoModel(); // Instancia videos
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia progreso video
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente

        $planEntrenamiento = $this->planEntrenamientoModel->obtenerPorCliente($clienteId); // Plan asignado
        $rutinas = $this->rutinaModel->obtenerPorCliente($clienteId); // Rutinas asignadas
        $videos = $this->videoModel->obtenerPorCliente($clienteId); // Videos asignados
        $avanceVirtual = $this->progresoVideoModel->obtenerAvanceCliente($clienteId); // Avance virtual

        require_once __DIR__ . '/../../views/cliente/entrenamiento.php'; // Carga vista
    }

    public function marcarRutina()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'rutina_id' => $_POST['rutina_id'], // ID rutina
                'estado' => $_POST['estado'], // Estado rutina
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->rutinaModel->registrarCumplimiento($datos); // Guarda cumplimiento

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Rutina actualizada por cliente'); // Registra historial
        }

        header('Location: entrenamientoController.php'); // Redirige
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

$controller = new ClienteEntrenamientoController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
