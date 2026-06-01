<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/entrenamiento/rutinaModel.php'; // Importa rutinas
require_once __DIR__ . '/../../models/entrenamiento/ejercicioModel.php'; // Importa ejercicios

class ClienteRutinaController
{
    private $clienteModel; // Modelo cliente
    private $rutinaModel; // Modelo rutina
    private $ejercicioModel; // Modelo ejercicio

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarCliente(); // Valida acceso cliente

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->rutinaModel = new RutinaModel(); // Instancia rutina
        $this->ejercicioModel = new EjercicioModel(); // Instancia ejercicio
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $rutinas = $this->rutinaModel->obtenerPorCliente($clienteId); // Obtiene rutinas

        require_once __DIR__ . '/../../views/cliente/entrenamiento.php'; // Carga vista
    }

    public function detalle()
    {
        $rutinaId = $_GET['id'] ?? null; // Obtiene rutina

        if (!$rutinaId) { // Valida rutina
            header('Location: rutinaController.php'); // Redirige
            exit; // Detiene ejecución
        }

        $rutina = $this->rutinaModel->obtenerPorId($rutinaId); // Obtiene detalle
        $ejercicios = $this->ejercicioModel->obtenerPorRutina($rutinaId); // Obtiene ejercicios

        require_once __DIR__ . '/../../views/cliente/entrenamiento.php'; // Carga vista
    }

    public function completar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $datos = [
                'cliente_id' => $this->obtenerClienteId(), // ID cliente
                'rutina_id' => $_POST['rutina_id'], // ID rutina
                'estado' => 'completada', // Estado final
                'observacion' => trim($_POST['observacion'] ?? '') // Observación
            ];

            $this->rutinaModel->registrarCumplimiento($datos); // Guarda cumplimiento

            $this->rutinaModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Rutina completada por cliente'); // Registra historial
        }

        header('Location: rutinaController.php'); // Redirige
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

        if ($rol !== 'cliente') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
        }
    }
}

$controller = new ClienteRutinaController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
