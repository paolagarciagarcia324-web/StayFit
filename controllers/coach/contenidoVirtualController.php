<?php

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/categoriaVideoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';

class CoachContenidoVirtualController
{
    private $planModel;
    private $videoModel;
    private $categoriaModel;
    private $programaModel;

    public function __construct()
    {
        session_start();
        $this->validarCoach();

        $this->planModel = new PlanModel();
        $this->videoModel = new VideoModel();
        $this->categoriaModel = new CategoriaVideoModel();
        $this->programaModel = new ProgramaVirtualModel();
    }

    public function index()
    {
        $planId = (int) ($_GET['plan_id'] ?? 0);
        $planes = $this->planModel->obtenerPlanesVirtuales();
        $categorias = $this->categoriaModel->obtenerActivas();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $plan = null;
        $programa = null;
        $materiales = [];

        if ($planId > 0) {
            $plan = $this->planModel->obtenerPorId($planId);
            $programa = $this->programaModel->obtenerPorPlan($planId);

            if ($programa) {
                $materiales = $this->videoModel->obtenerPorPrograma($programa['id'], false);
            }
        }

        $baseController = 'coach';
        require_once __DIR__ . '/../../views/coach/contenidoVirtual.php';
    }

    public function guardarPrograma()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir();
        }

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($planId < 1 || $nombre === '') {
            $this->flash('error', 'Plan y nombre del programa son obligatorios.');
            $this->redirigir($planId);
        }

        $programa = $this->programaModel->obtenerPorPlan($planId);

        if ($programa) {
            $this->programaModel->actualizar([
                'id' => $programa['id'],
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'nivel' => $_POST['nivel'] ?? 'General',
                'activo' => 1,
            ]);
            $this->flash('success', 'Programa virtual actualizado.');
        } else {
            $this->programaModel->crear([
                'id_plan' => $planId,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'nivel' => $_POST['nivel'] ?? 'General',
                'activo' => 1,
            ]);
            $this->flash('success', 'Programa virtual creado.');
        }

        $this->redirigir($planId);
    }

    public function guardarMaterial()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir();
        }

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $tipoMedia = strtoupper(trim($_POST['tipo_media'] ?? 'ENLACE'));

        if ($planId < 1 || $titulo === '') {
            $this->flash('error', 'Plan y título son obligatorios.');
            $this->redirigir($planId);
        }

        $plan = $this->planModel->obtenerPorId($planId);
        $programa = $this->programaModel->obtenerOcrearPorPlan($planId, $plan['nombre'] ?? null);
        $urlVideo = trim($_POST['url_video'] ?? '');

        if ($tipoMedia !== 'ENLACE' && !empty($_FILES['archivo']['name'])) {
            $ruta = guardarMaterialVirtual($_FILES['archivo']);

            if (!$ruta) {
                $this->flash('error', 'No se pudo guardar el archivo.');
                $this->redirigir($planId);
            }

            $urlVideo = $ruta;
            $tipoMedia = tipoMediaDesdeArchivo($_FILES['archivo']['name']);
        } elseif ($tipoMedia === 'ENLACE' && $urlVideo === '') {
            $this->flash('error', 'Indica la URL del enlace.');
            $this->redirigir($planId);
        } elseif ($urlVideo === '') {
            $this->flash('error', 'Sube un archivo o indica una URL.');
            $this->redirigir($planId);
        }

        $this->videoModel->crear([
            'programa_virtual_id' => $programa['id'],
            'categoria_id' => $_POST['categoria_id'] ?? null,
            'titulo' => $titulo,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'url_video' => $urlVideo,
            'tipo_media' => $tipoMedia,
            'id_subido_por' => $_SESSION['usuario_id'] ?? null,
            'duracion' => $_POST['duracion'] ?? null,
            'orden' => (int) ($_POST['orden'] ?? 1),
            'activo' => 1,
        ]);

        $this->flash('success', 'Material publicado.');
        $this->redirigir($planId);
    }

    public function eliminarMaterial()
    {
        $planId = (int) ($_GET['plan_id'] ?? 0);
        $id = (int) ($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->videoModel->eliminar($id);
            $this->flash('success', 'Material eliminado.');
        }

        $this->redirigir($planId);
    }

    public function cambiarEstadoMaterial()
    {
        $planId = (int) ($_GET['plan_id'] ?? 0);
        $id = (int) ($_GET['id'] ?? 0);
        $estado = $_GET['estado'] ?? 'inactivo';

        if ($id > 0) {
            $this->videoModel->cambiarEstado($id, $estado);
        }

        $this->redirigir($planId);
    }

    public function guardarCategoria()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->categoriaModel->crear([
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'estado' => 'activo',
            ]);
            $this->flash('success', 'Categoría creada.');
        }

        $this->redirigir((int) ($_POST['plan_id'] ?? $_GET['plan_id'] ?? 0));
    }

    private function flash($tipo, $mensaje)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    private function redirigir($planId = 0)
    {
        $url = 'contenidoVirtualController.php';

        if ($planId > 0) {
            $url .= '?plan_id=' . $planId;
        }

        header('Location: ' . $url);
        exit;
    }

    private function validarCoach()
    {
        if (!esCoach()) {
            header('Location: ../../views/auth/accesoDenegado.php');
            exit;
        }
    }
}

$controller = new CoachContenidoVirtualController();
$accion = $_GET['accion'] ?? 'index';

if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}

?>
