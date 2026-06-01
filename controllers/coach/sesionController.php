<?php

require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa coach
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa sesiones

class CoachSesionController
{
    private $coachModel; // Modelo coach
    private $clienteModel; // Modelo cliente
    private $sesionModel; // Modelo sesión

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCoach(); // Valida acceso coach

        $this->coachModel = new CoachModel(); // Instancia coach
        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->sesionModel = new SesionModel(); // Instancia sesión
    }

    public function index()
    {
        $coachId = $this->obtenerCoachId(); // Obtiene coach actual

        $clientes = $this->clienteModel->obtenerPorCoach($coachId); // Obtiene clientes asignados
        $sesiones = $this->sesionModel->obtenerPorCoach($coachId); // Obtiene sesiones

        require_once __DIR__ . '/../../views/coach/agenda.php'; // Carga vista
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'coach_id' => $this->obtenerCoachId(), // ID coach
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'titulo' => trim($_POST['titulo']), // Título
                'descripcion' => trim($_POST['descripcion'] ?? ''), // Descripción
                'fecha' => $_POST['fecha'], // Fecha
                'hora' => $_POST['hora'], // Hora
                'modalidad' => $_POST['modalidad'], // Presencial, virtual o mixta
                'tipo' => $_POST['tipo'], // Individual o grupal
                'estado' => 'programada' // Estado inicial
            ];

            $this->sesionModel->crear($datos); // Crea sesión

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Sesión creada por coach'); // Registra historial
        }

        header('Location: sesionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function actualizarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'id' => $_POST['id'], // ID sesión
                'estado' => $_POST['estado'], // Nuevo estado
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->sesionModel->actualizarEstado($datos); // Actualiza estado

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Estado de sesión actualizado'); // Registra historial
        }

        header('Location: sesionController.php'); // Redirige
        exit; // Detiene ejecución
    }

    public function marcarAsistencia()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'sesion_id' => $_POST['sesion_id'], // ID sesión
                'cliente_id' => $_POST['cliente_id'], // ID cliente
                'estado' => $_POST['estado'], // Asistió o no asistió
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->sesionModel->marcarAsistencia($datos); // Registra asistencia

            $this->sesionModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Asistencia registrada por coach'); // Registra historial
        }

        header('Location: sesionController.php'); // Redirige
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
        if (strtolower($_SESSION['rol'] ?? '') !== 'coach') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new CoachSesionController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
