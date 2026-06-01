<?php

require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa cliente
require_once __DIR__ . '/../../models/cliente/datosFisicosModel.php'; // Importa datos físicos

class ClienteDatosFisicosController
{
    private $clienteModel; // Modelo de cliente
    private $datosFisicosModel; // Modelo de datos físicos

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarCliente(); // Valida acceso

        $this->clienteModel = new ClienteModel(); // Instancia cliente
        $this->datosFisicosModel = new DatosFisicosModel(); // Instancia datos físicos
    }

    public function index()
    {
        header('Location: perfilController.php'); // Perfil unificado
        exit; // Detiene ejecución
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

            $datos = [
                'cliente_id' => $clienteId, // ID del cliente
                'peso' => $_POST['peso'], // Peso actual
                'estatura' => $_POST['estatura'], // Estatura
                'objetivo' => trim($_POST['objetivo']), // Objetivo físico
                'restricciones' => trim($_POST['restricciones'] ?? ''), // Restricciones
                'observaciones' => trim($_POST['observaciones'] ?? '') // Observaciones
            ];

            $this->datosFisicosModel->guardarOActualizar($datos); // Guarda datos

            $this->datosFisicosModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Datos físicos actualizados'); // Registra historial
        }

        header('Location: perfilController.php'); // Redirige al perfil
        exit; // Detiene ejecución
    }

    private function obtenerClienteId()
    {
        if (isset($_SESSION['cliente_id'])) { // Verifica cliente en sesión
            return $_SESSION['cliente_id']; // Retorna cliente
        }

        $cliente = $this->clienteModel->obtenerPorUsuario($_SESSION['usuario_id']); // Busca cliente

        $_SESSION['cliente_id'] = $cliente['id']; // Guarda en sesión

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

$controller = new ClienteDatosFisicosController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Valida método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>
