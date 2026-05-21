<?php

require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php'; // Importa videos
require_once __DIR__ . '/../../models/contenidoVirtual/categoriaVideoModel.php'; // Importa categorías
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php'; // Importa programas virtuales

class ContenidoVirtualController
{
    private $videoModel; // Modelo de videos
    private $categoriaModel; // Modelo de categorías
    private $programaModel; // Modelo de programas virtuales

    public function __construct()
    {
        session_start(); // Inicia la sesión

        $this->validarAdministrador(); // Valida acceso del administrador

        $this->videoModel = new VideoModel(); // Instancia videos
        $this->categoriaModel = new CategoriaVideoModel(); // Instancia categorías
        $this->programaModel = new ProgramaVirtualModel(); // Instancia programas
    }

    public function index()
    {
        $videos = $this->videoModel->obtenerTodos(); // Obtiene videos
        $categorias = $this->categoriaModel->obtenerTodos(); // Obtiene categorías
        $programas = $this->programaModel->obtenerTodos(); // Obtiene programas

        require_once __DIR__ . '/../../views/admin/planes.php'; // Carga vista relacionada
    }

    public function guardarCategoria()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre de categoría
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'estado' => 'activo' // Estado inicial
            ];

            $this->categoriaModel->crear($datos); // Guarda categoría

            $this->registrarTrazabilidad('Categoría de video creada'); // Guarda trazabilidad
        }

        header('Location: contenidoVirtualController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function guardarPrograma()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'nombre' => trim($_POST['nombre']), // Nombre del programa
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'nivel' => $_POST['nivel'], // Nivel del programa
                'estado' => 'activo' // Estado inicial
            ];

            $this->programaModel->crear($datos); // Guarda programa

            $this->registrarTrazabilidad('Programa virtual creado'); // Guarda trazabilidad
        }

        header('Location: contenidoVirtualController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function guardarVideo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario

            $datos = [
                'programa_virtual_id' => $_POST['programa_virtual_id'], // Programa asociado
                'categoria_id' => $_POST['categoria_id'], // Categoría del video
                'titulo' => trim($_POST['titulo']), // Título del video
                'descripcion' => trim($_POST['descripcion']), // Descripción
                'url_video' => trim($_POST['url_video']), // URL del video
                'orden' => $_POST['orden'], // Orden dentro del programa
                'estado' => 'activo' // Estado inicial
            ];

            $this->videoModel->crear($datos); // Guarda video

            $this->registrarTrazabilidad('Video pregrabado registrado'); // Guarda trazabilidad
        }

        header('Location: contenidoVirtualController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    public function cambiarEstadoVideo()
    {
        if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos

            $id = $_GET['id']; // ID del video
            $estado = $_GET['estado']; // Nuevo estado

            $this->videoModel->cambiarEstado($id, $estado); // Cambia estado

            $this->registrarTrazabilidad('Estado de video actualizado'); // Guarda trazabilidad
        }

        header('Location: contenidoVirtualController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }

    private function registrarTrazabilidad($accion)
    {
        $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador

        $this->programaModel->registrarTrazabilidad($adminId, $accion); // Registra historial
    }

    private function validarAdministrador()
    {
        $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión

        if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
            header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
            exit; // Detiene la ejecución
        }
    }
}

$controller = new ContenidoVirtualController(); // Crea el controlador

$accion = $_GET['accion'] ?? 'index'; // Acción por defecto

if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}

?>
