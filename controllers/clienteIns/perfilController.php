<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php';
require_once __DIR__ . '/../../models/usuario/usuarioModel.php';
require_once __DIR__ . '/../../models/cliente/datosFisicosModel.php';
require_once __DIR__ . '/../../models/institucion/institucionModel.php';
require_once __DIR__ . '/../../config/roles.php';

class ClienteInsPerfilController
{
    private $clienteInsModel;
    private $usuarioModel;
    private $datosFisicosModel;
    private $institucionModel;

    public function __construct()
    {
        session_start();

        $this->validarClienteInstitucional();

        $this->clienteInsModel = new ClienteInsModel();
        $this->usuarioModel = new UsuarioModel();
        $this->datosFisicosModel = new DatosFisicosModel();
        $this->institucionModel = new InstitucionModel();
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId();

        $cliente = $this->clienteInsModel->obtenerPorId($clienteId);
        $usuario = $this->usuarioModel->obtenerPorId($_SESSION['usuario_id']);
        $datosFisicos = $this->datosFisicosModel->obtenerPorCliente($clienteId);
        $institucion = $this->institucionModel->obtenerPorCliente($clienteId);

        require_once __DIR__ . '/../../views/clienteIns/perfil.php';
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Valida formulario

            $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

            $datosUsuario = [
                'id' => $_SESSION['usuario_id'], // ID usuario
                'nombre' => trim($_POST['nombre']), // Nombre
                'correo' => trim($_POST['correo']) // Correo
            ];

            $datosCliente = [
                'id' => $clienteId, // ID cliente
                'identificacion' => trim($_POST['identificacion']), // Documento
                'edad' => $_POST['edad'], // Edad
                'celular' => trim($_POST['celular']) // Celular
            ];

            $this->usuarioModel->actualizarBasico($datosUsuario); // Actualiza usuario
            $this->clienteInsModel->actualizarPerfil($datosCliente); // Actualiza cliente

            $this->clienteInsModel->registrarTrazabilidad($_SESSION['usuario_id'], 'Perfil institucional actualizado'); // Registra historial
        }

        header('Location: perfilController.php'); // Redirige
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
        if (!esClienteInstitucional()) {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new ClienteInsPerfilController(); // Crea controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Ejecuta inicio
}

?>