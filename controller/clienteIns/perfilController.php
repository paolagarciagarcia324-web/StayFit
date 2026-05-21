<?php

require_once __DIR__ . '/../../models/cliente/clienteInsModel.php'; // Importa cliente institucional
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuario
require_once __DIR__ . '/../../models/cliente/datosFisicosModel.php'; // Importa datos físicos

class ClienteInsPerfilController
{
    private $clienteInsModel; // Modelo cliente institucional
    private $usuarioModel; // Modelo usuario
    private $datosFisicosModel; // Modelo datos físicos

    public function __construct()
    {
        session_start(); // Inicia sesión

        $this->validarClienteInstitucional(); // Valida acceso

        $this->clienteInsModel = new ClienteInsModel(); // Instancia cliente institucional
        $this->usuarioModel = new UsuarioModel(); // Instancia usuario
        $this->datosFisicosModel = new DatosFisicosModel(); // Instancia datos físicos
    }

    public function index()
    {
        $clienteId = $this->obtenerClienteId(); // Obtiene cliente actual

        $cliente = $this->clienteInsModel->obtenerPorId($clienteId); // Obtiene cliente
        $usuario = $this->usuarioModel->obtenerPorId($_SESSION['usuario_id']); // Obtiene usuario
        $datosFisicos = $this->datosFisicosModel->obtenerPorCliente($clienteId); // Obtiene datos físicos

        require_once __DIR__ . '/../../views/clienteIns/perfil.php'; // Carga vista
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
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol

        if ($rol !== 'clienteins' && $rol !== 'cliente_institucional') { // Valida rol
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige
            exit; // Detiene ejecución
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