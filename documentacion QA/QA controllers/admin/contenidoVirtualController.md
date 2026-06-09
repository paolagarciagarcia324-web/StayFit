Controlador encargado de gestionar todo el contenido virtual de la plataforma StayFitMVC, incluyendo programas virtuales, materiales (videos, enlaces, archivos), categorías y su asociación con los planes de entrenamiento.

//Este archivo contenidoVirtualController.php corresponde al controlador encargado de gestionar todo el contenido virtual dentro de la plataforma StayFitMVC. Su función principal es administrar los programas virtuales asociados a los planes, los materiales multimedia (videos, enlaces externos, archivos subidos) y las categorías que los organizan. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (validaciones de datos, manejo de archivos, control de estados) y delega las operaciones de almacenamiento y consulta a los respectivos modelos. Es uno de los controladores más complejos del sistema porque maneja tanto la creación de programas como la gestión completa de materiales con soporte para archivos físicos y enlaces externos.//

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/plan/planModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/videoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/categoriaVideoModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos, helpers.php me proporciona funciones auxiliares como guardarMaterialVirtual() y tipoMediaDesdeArchivo() que usaré más adelante para el manejo de archivos. Posteriormente cargo los cuatro modelos que necesito: planModel.php para gestionar los planes, videoModel.php para los materiales/videos, categoriaVideoModel.php para las categorías de contenido, y programaVirtualModel.php para los programas virtuales asociados a cada plan. Estas dependencias me permiten acceder a toda la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión de contenido virtual.//

class ContenidoVirtualController
{
    private $planModel;
    private $videoModel;
    private $categoriaModel;
    private $programaModel;

//En este bloque declaro la clase ContenidoVirtualController y sus propiedades privadas:
Declaro las cuatro propiedades que almacenarán las instancias de los modelos necesarios. Gracias a ellas puedo acceder a las funciones encargadas de consultar planes, gestionar materiales/videos, administrar categorías y manejar programas virtuales sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start();
    validarAccesoAdministrador();
    $this->planModel = new PlanModel();
    $this->videoModel = new VideoModel();
    $this->categoriaModel = new CategoriaVideoModel();
    $this->programaModel = new ProgramaVirtualModel();
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo directamente a la función validarAccesoAdministrador() que está definida en el archivo roles.php para verificar que solo los administradores puedan acceder a estas funcionalidades. Finalmente, instancio los cuatro modelos que necesito, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador. A diferencia de otros controladores donde la validación está en un método privado, aquí la hago directamente en el constructor para simplificar el código.//

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
    require_once __DIR__ . '/../../views/admin/contenidoVirtual.php';
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar la gestión de contenido virtual de un plan específico:
Este método lo diseñé para ser dinámico según el plan seleccionado. Primero obtengo el plan_id desde los parámetros GET y lo convierto a entero. Si no hay un plan seleccionado (valor 0), la vista mostrará solo la lista de planes disponibles para elegir.
Luego obtengo todos los planes virtuales disponibles y las categorías activas de contenido, que serán necesarias para los formularios de creación. Recupero cualquier mensaje flash almacenado en la sesión (de acciones anteriores) y lo limpio inmediatamente con unset() para que no se muestre dos veces.
Inicializo las variables $plan, $programa y $materiales con valores vacíos. Si hay un planId válido, procedo a cargar toda la información relacionada: el plan específico, el programa virtual asociado a ese plan (si existe), y todos los materiales del programa. El segundo parámetro false en obtenerPorPrograma() indica que quiero obtener todos los materiales sin filtrar por estado.
Finalmente cargo la vista contenidoVirtual.php con todos estos datos. La vista se encargará de mostrar diferente contenido según si hay un plan seleccionado o no.//

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
    $this->programaModel->registrarTrazabilidad($_SESSION['usuario_id'] ?? null, 'Programa virtual guardado');
    $this->redirigir($planId);
}

//El método guardarPrograma() se encarga de crear o actualizar el programa virtual asociado a un plan:
Este método lo diseñé con un patrón "crear o actualizar" (upsert) muy útil. Primero verifico que la solicitud sea POST, ya que es una acción que modifica datos. Si no lo es, redirijo inmediatamente.
Obtengo el ID del plan, el nombre y la descripción del formulario. Valido que el plan sea válido (mayor a 0) y que el nombre no esté vacío. Si alguna validación falla, muestro un mensaje de error y redirijo de vuelta.
Luego verifico si ya existe un programa virtual para ese plan usando obtenerPorPlan(). Si existe, lo actualizo con los nuevos datos; si no, creo uno nuevo. Este enfoque me permite que el administrador pueda editar el programa las veces que quiera sin generar duplicados.
En ambos casos establezco el campo activo en 1 para que el programa esté disponible inmediatamente. El campo nivel tiene un valor por defecto 'General' si no se especifica. Finalmente registro la acción en la trazabilidad y redirijo al plan correspondiente.//

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
            $this->flash('error', 'No se pudo guardar el archivo. Usa MP4, WEBM, JPG, PNG o PDF.');
            $this->redirigir($planId);
        }
        $urlVideo = $ruta;
        $tipoMedia = tipoMediaDesdeArchivo($_FILES['archivo']['name']);
    } elseif ($tipoMedia === 'ENLACE' && $urlVideo === '') {
        $this->flash('error', 'Indica la URL del enlace externo.');
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
    $this->videoModel->registrarTrazabilidad($_SESSION['usuario_id'] ?? null, 'Material virtual añadido: ' . $titulo);
    $this->flash('success', 'Material publicado correctamente.');
    $this->redirigir($planId);
}

//El método guardarMaterial() es uno de los más complejos del controlador, ya que maneja la creación de materiales con soporte para archivos físicos y enlaces externos:
Este método lo diseñé para ser flexible y aceptar diferentes tipos de contenido. Primero verifico que la solicitud sea POST y obtengo los datos básicos: plan ID, título y tipo de media (que por defecto es 'ENLACE').
Valido que el plan sea válido y que el título no esté vacío. Luego obtengo el plan y uso el método obtenerOcrearPorPlan() del modelo de programa virtual, que es muy inteligente: si ya existe un programa para ese plan, lo devuelve; si no, crea uno nuevo automáticamente usando el nombre del plan. Esto me permite agregar materiales incluso si el programa aún no ha sido creado explícitamente.
Después manejo la lógica de contenido multimedia con tres escenarios:
Escenario 1: Si el tipo de media NO es enlace y se subió un archivo, uso la función auxiliar guardarMaterialVirtual() para guardar el archivo en el servidor. Si falla, muestro un error específico indicando los formatos permitidos (MP4, WEBM, JPG, PNG, PDF). Si tiene éxito, actualizo la URL con la ruta del archivo y detecto automáticamente el tipo de media usando tipoMediaDesdeArchivo().
Escenario 2: Si es tipo enlace pero no se proporcionó URL, muestro un error pidiendo la URL.
Escenario 3: Si no hay archivo ni URL, muestro un error genérico.
Una vez validado todo, creo el registro del material con todos sus metadatos: el programa al que pertenece, la categoría, título, descripción, URL (que puede ser ruta de archivo o enlace externo), tipo de media, el usuario que lo subió, duración opcional, orden de visualización y estado activo. Registro la acción en la trazabilidad y muestro un mensaje de éxito.//

public function actualizarMaterial()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirigir();
    }
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $id = (int) ($_POST['id'] ?? 0);
    $material = $this->videoModel->obtenerPorId($id);
    if (!$material) {
        $this->flash('error', 'Material no encontrado.');
        $this->redirigir($planId);
    }
    $urlVideo = trim($_POST['url_video'] ?? $material['url_video']);
    $tipoMedia = strtoupper(trim($_POST['tipo_media'] ?? $material['tipo_media'] ?? 'ENLACE'));
    if (!empty($_FILES['archivo']['name'])) {
        $ruta = guardarMaterialVirtual($_FILES['archivo']);
        if ($ruta) {
            $urlVideo = $ruta;
            $tipoMedia = tipoMediaDesdeArchivo($_FILES['archivo']['name']);
        }
    }
    $this->videoModel->actualizar([
        'id' => $id,
        'programa_virtual_id' => $material['programa_virtual_id'],
        'categoria_id' => $_POST['categoria_id'] ?? $material['categoria_id'],
        'titulo' => trim($_POST['titulo'] ?? $material['titulo']),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'url_video' => $urlVideo,
        'tipo_media' => $tipoMedia,
        'orden' => (int) ($_POST['orden'] ?? $material['orden'] ?? 1),
        'activo' => $material['activo'] ?? 1,
    ]);
    $this->flash('success', 'Material actualizado.');
    $this->redirigir($planId);
}

//El método actualizarMaterial() me permite modificar un material existente manteniendo los valores anteriores si no se proporcionan nuevos:
Este método lo diseñé con un patrón de "valores por defecto" muy útil. Primero verifico que sea POST y obtengo el ID del material a actualizar. Busco el material existente en la base de datos y si no existe, muestro un error y redirijo.
La clave de este método está en cómo manejo los valores: para cada campo, uso el valor del formulario si existe, o mantengo el valor actual del material si no se proporcionó uno nuevo. Esto lo hago con el operador de fusión null (??).
Para el archivo, si se subió uno nuevo, lo guardo y actualizo la URL y el tipo de media. Si no se subió archivo, mantengo la URL y el tipo de media actuales.
Actualizo el registro en la base de datos con todos los campos, manteniendo el programa_virtual_id y el estado activo originales (no permito cambiarlos desde este formulario). Muestro un mensaje de éxito y redirijo.//

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

//El método eliminarMaterial() me permite eliminar un material de forma sencilla:
Recibo el ID del material y el plan ID mediante GET. Valido que el ID sea mayor a cero para evitar eliminaciones accidentales. Si es válido, llamo al método eliminar() del modelo que se encarga de borrar el registro de la base de datos. Muestro un mensaje de confirmación y redirijo de vuelta al plan.
Es importante notar que este método solo elimina el registro de la base de datos, no el archivo físico del servidor. En una versión futura podría mejorar esto para limpiar también los archivos huérfanos.//

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

//El método cambiarEstadoMaterial() me permite activar o desactivar materiales sin eliminarlos:
Recibo mediante GET el ID del material, el plan ID y el nuevo estado (que por defecto es 'inactivo' si no se especifica). Valido que el ID sea válido y llamo al método cambiarEstado() del modelo.
Esta funcionalidad es muy útil cuando quiero ocultar temporalmente un material sin perder su información. Por ejemplo, si un video tiene problemas de derechos de autor o necesita ser actualizado, puedo desactivarlo rápidamente y reactivarlo cuando esté listo.//

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

//El método guardarCategoria() me permite crear nuevas categorías para organizar el contenido:
Verifico que la solicitud sea POST y creo la categoría con el nombre, descripción opcional y estado 'activo' por defecto. Muestro un mensaje de éxito.
Para la redirección, uso una lógica inteligente: intento obtener el plan_id primero del POST (si viene del formulario de creación de categoría), y si no existe, lo obtengo del GET. Esto me permite redirigir correctamente al plan correspondiente sin importar desde dónde se envió el formulario.//

private function flash($tipo, $mensaje)
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

//El método privado flash() es una herramienta interna que me permite almacenar mensajes temporales en la sesión:
Este método lo creé para no repetir código en cada función del controlador. Recibe dos parámetros: el tipo de mensaje (success, error, warning) y el mensaje en sí. Almacena ambos en la variable de sesión $_SESSION['flash'] que luego la vista puede leer y mostrar al usuario.
La ventaja de usar este sistema es que los mensajes persisten a través de redirecciones, permitiendo mostrar feedback al usuario después de una acción. Una vez que la vista muestra el mensaje, normalmente se limpia la variable de sesión.//

private function redirigir($planId = 0)
{
    $url = 'contenidoVirtualController.php';
    if ($planId > 0) {
        $url .= '?plan_id=' . $planId;
    }
    header('Location: ' . $url);
    exit;
}

//El método privado redirigir() es una herramienta interna que me permite redirigir de forma consistente:
Este método lo creé para centralizar la lógica de redirección. Recibe opcionalmente un plan ID y construye la URL apropiada. Si hay un plan ID válido, lo agrega como parámetro GET para que al redirigir se mantenga el contexto del plan seleccionado.
Siempre uso exit después del header para detener la ejecución inmediatamente. Esto es crucial para evitar que se ejecute código adicional después de la redirección, lo cual podría causar comportamientos inesperados o errores.//

$controller = new ContenidoVirtualController();
$accion = $_GET['accion'] ?? 'index';
if (method_exists($controller, $accion)) {
    $controller->$accion();
} else {
    $controller->index();
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().

Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:

contenidoVirtualController.php?accion=index (vista principal)
contenidoVirtualController.php?accion=index&plan_id=5 (ver contenido de un plan)
contenidoVirtualController.php?accion=guardarPrograma (crear/actualizar programa)
contenidoVirtualController.php?accion=guardarMaterial (crear material)
contenidoVirtualController.php?accion=actualizarMaterial (editar material)
contenidoVirtualController.php?accion=eliminarMaterial&id=5&plan_id=3 (eliminar material)
contenidoVirtualController.php?accion=cambiarEstadoMaterial&id=5&estado=activo&plan_id=3 (activar/desactivar)
contenidoVirtualController.php?accion=guardarCategoria (crear categoría)//

//Este controlador es el corazón de la gestión de contenido virtual en StayFitMVC. Centraliza toda la lógica de negocio relacionada con programas virtuales y materiales multimedia, desde la creación de programas asociados a planes, hasta la gestión completa de materiales con soporte para archivos físicos y enlaces externos. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo, las validaciones y la preparación de datos; los modelos se encargan de la base de datos; y las vistas presentan la información al usuario. Una característica importante es el sistema de redirección centralizado y el manejo flexible de archivos que permite múltiples formatos de contenido.//
