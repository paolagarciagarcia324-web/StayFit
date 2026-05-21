<?php

require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php'; // Importa videos
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa progreso virtual

class ClienteInsContenidoVirtualController
{
    private $videoModel; // Modelo videos
    private $progresoVideoModel; // Modelo progreso videos

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->videoModel = new VideoModel(); // Instancia videos
        $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia progreso
    }

    public function index()
    {
        $clienteId = $_SESSION['cliente_id'] ?? null; // ID del cliente

        $videos = $this->videoModel->obtenerPorCliente($clienteId); // Obtiene videos asignados
        $avance = $this->progresoVideoModel->obtenerAvanceCliente($clienteId); // Obtiene avance

        require_once __DIR__ . '/../../views/clienteIns/entrenamiento.php'; // Carga vista
    }

    public function marcarVisto()
    {
        if (isset($_GET['video_id'])) { // Valida video

            $datos = [
                'cliente_id' => $_SESSION['cliente_id'], // ID cliente
                'video_id' => $_GET['video_id'], // ID video
                'estado' => 'visto' // Estado
            ];

            $this->progresoVideoModel->marcarVisto($datos); // Marca video

            $this->progresoVideoModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Video institucional marcado como visto'); // Registra historial
        }

        header('Location: contenidoVirtualController.php'); // Redirige
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

$controller = new ClienteInsContenidoVirtualController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta vista
}

?>