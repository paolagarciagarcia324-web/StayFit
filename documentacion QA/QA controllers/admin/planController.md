Controlador encargado de gestionar los planes de entrenamiento, programas y programas virtuales dentro de la plataforma StayFitMVC, permitiendo su creación, actualización y control de estado.

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/plan/programaModel.php'; // Importa programas
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php'; // Importa programas virtuales

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Posteriormente cargo los tres modelos que necesito: planModel.php para gestionar los planes de entrenamiento, programaModel.php para los programas generales, y programaVirtualModel.php para los programas virtuales asociados a contenido multimedia. Estas dependencias me permiten acceder a toda la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión de la oferta comercial de StayFitMVC.//

class PlanController
{
    private $planModel; // Modelo de planes
    private $programaModel; // Modelo de programas
    private $programaVirtualModel; // Modelo de programas virtuales

//En este bloque declaro la clase PlanController y sus propiedades privadas:
Declaro las tres propiedades que almacenarán las instancias de los modelos necesarios. Gracias a ellas puedo acceder a las funciones encargadas de consultar, crear y actualizar planes y programas sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->planModel = new PlanModel(); // Instancia planes
    $this->programaModel = new ProgramaModel(); // Instancia programas
    $this->programaVirtualModel = new ProgramaVirtualModel(); // Instancia programas virtuales
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades. Finalmente, instancio los tres modelos que necesito, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador.//

public function index()
{
    $planes = $this->planModel->adjuntarInfoCupo($this->planModel->obtenerTodos());
    $programas = $this->programaModel->obtenerTodos();
    $programasVirtuales = $this->programaVirtualModel->obtenerActivos();
    require_once __DIR__ . '/../../views/admin/planes.php';
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de planes y programas:
Este método lo diseñé para cargar toda la información necesaria para la vista de gestión de planes. Primero obtengo todos los planes y, de forma encadenada, uso el método adjuntarInfoCupo() para enriquecer cada plan con información sobre los cupos disponibles o vendidos. Esto me permite mostrar al administrador el estado de ocupación de cada plan directamente en el listado.
Luego obtengo todos los programas generales y los programas virtuales activos. Toda esta información se pone a disposición de la vista planes.php que renderizará el listado completo, los formularios de creación y edición, y la información de los programas asociados.//

public function guardarPlan()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $modalidad = trim($_POST['modalidad'] ?? 'VIRTUAL');
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => $_POST['precio'] ?? 0,
            'duracion_dias' => $_POST['duracion_dias'] ?? $_POST['duracion'] ?? null,
            'modalidad' => $modalidad,
            'requiere_coach' => isset($_POST['requiere_coach']),
            'incluye_entrenamiento' => isset($_POST['incluye_entrenamiento']),
            'incluye_nutricion' => isset($_POST['incluye_nutricion']),
            'incluye_videos' => !empty($_POST['programa_virtual_id'])
                || in_array(strtoupper($modalidad), ['VIRTUAL', 'MIXTA', 'MIXTO'], true),
            'estado_plan' => 'ACTIVO',
        ];
        $this->planModel->crear($datos);
        $this->registrarTrazabilidad('Plan creado');
    }
    header('Location: planController.php');
    exit;
}

//El método guardarPlan() se encarga del registro completo de un nuevo plan de entrenamiento:
Este método lo diseñé con una lógica de negocio interesante para la automatización de campos. Primero verifico que la solicitud sea POST y obtengo la modalidad del plan.
Preparo un array con todos los datos del plan. Lo más destacable es el campo incluye_videos: lo calculo automáticamente verificando si se seleccionó un programa virtual específico (!empty($_POST['programa_virtual_id'])) o si la modalidad del plan es virtual o mixta (in_array(...)). Esto me permite que el sistema marque automáticamente que el plan incluye contenido virtual sin que el administrador tenga que hacerlo manualmente.
También manejo la duración de forma flexible, aceptando tanto duracion_dias como duracion por compatibilidad con diferentes versiones del formulario. Los campos booleanos como requiere_coach, incluye_entrenamiento e incluye_nutricion los evalúo con isset() para detectar si los checkboxes estaban marcados. Finalmente creo el plan con estado 'ACTIVO' y registro la trazabilidad.//

public function actualizarPlan()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'id_plan'                         => $_POST['id_plan'],                        // ID del plan
            'nombre'                          => trim($_POST['nombre']),                    // Nombre actualizado
            'descripcion'                     => trim($_POST['descripcion']),               // Descripción actualizada
            'precio'                          => $_POST['precio'],                          // Precio actualizado
            'duracion_dias'                   => $_POST['duracion_dias'] ?? null,           // Duración actualizada
            'dias_previos_recordatorio_default' => $_POST['dias_previos_recordatorio_default'] ?? 5, // Días recordatorio
            'estado_plan'                     => $_POST['estado_plan'] ?? 'ACTIVO'         // Estado actualizado
        ];
        $this->planModel->actualizar($datos); // Actualiza plan
        $this->registrarTrazabilidad('Plan actualizado'); // Guarda trazabilidad
    }
    header('Location: planController.php'); // Redirige al panel
    exit; // Detiene la ejecución
}

//El método actualizarPlan() me permite modificar la información de un plan existente:
Este método recibe los datos actualizados mediante POST y los organiza en un array $datos. Aquí incluyo un campo específico llamado dias_previos_recordatorio_default, que tiene un valor por defecto de 5 días si no se proporciona. Este campo es útil para configurar automáticamente los recordatorios de renovación o vencimiento del plan.
Una vez recopilados los datos, llamo al método actualizar() del modelo que se encarga de persistir los cambios en la base de datos. Después registro la acción en la trazabilidad para mantener un historial de modificaciones. Finalmente, redirijo al listado principal.//

public function guardarPrograma()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'nombre' => trim($_POST['nombre']), // Nombre del programa
            'descripcion' => trim($_POST['descripcion']), // Descripción
            'precio' => $_POST['precio'], // Precio del programa
            'modalidad' => $_POST['modalidad'], // Modalidad
            'estado' => 'activo' // Estado inicial
        ];
        $this->programaModel->crear($datos); // Guarda programa
        $this->registrarTrazabilidad('Programa creado'); // Guarda trazabilidad
    }
    header('Location: planController.php'); // Redirige al panel
    exit; // Detiene la ejecución
}

//El método guardarPrograma() me permite crear programas de entrenamiento independientes:
Este método lo diseñé para manejar la creación de programas que pueden existir por fuera de los planes específicos (por ejemplo, programas especiales o temporales). Verifico que la solicitud sea POST y preparo un array con los datos básicos del programa: nombre, descripción, precio, modalidad y estado inicial 'activo'.
Llamo al método crear() del modelo de programas que inserta el registro en la base de datos. Registro la acción en la trazabilidad y redirijo al listado. Esto me permite mantener una biblioteca de programas que luego pueden ser asociados a diferentes planes.//

public function cambiarEstado()
{
    if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos
        $id = $_GET['id']; // ID del plan
        $estado = $_GET['estado']; // Nuevo estado
        $this->planModel->cambiarEstado($id, $estado); // Cambia estado
        $this->registrarTrazabilidad('Estado de plan actualizado'); // Guarda trazabilidad
    }
    header('Location: planController.php'); // Redirige al panel
    exit; // Detiene la ejecución
}

//El método cambiarEstado() me permite activar o desactivar planes de forma rápida:
Recibo mediante GET el ID del plan y el nuevo estado que debe tener. Verifico que ambos parámetros existan antes de proceder.
Llamo al método cambiarEstado() del modelo que actualiza únicamente este campo en la base de datos. Esto me permite inhabilitar un plan temporalmente sin perder su información histórica (clientes asociados, programas vinculados, etc.). Registro la acción en la trazabilidad y redirijo al listado. Este método es muy útil cuando un plan se descataloga o se suspende temporalmente.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->planModel->registrarTrazabilidad($adminId, $accion); // Registra historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Plan creado", "Programa creado").
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién hizo qué cambio y cuándo, lo cual es fundamental para la seguridad y el control administrativo.//

private function validarAdministrador()
{
    validarAccesoAdministrador(); // Valida sesión admin
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas.//

$controller = new PlanController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Acción por defecto
if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
planController.php?accion=index (listado de planes y programas)
planController.php?accion=guardarPlan (crear nuevo plan - vía POST)
planController.php?accion=actualizarPlan (editar plan - vía POST)
planController.php?accion=guardarPrograma (crear programa - vía POST)
planController.php?accion=cambiarEstado&id=5&estado=INACTIVO (activar/desactivar plan)//

//Este controlador es el centro de gestión de la oferta comercial en StayFitMVC. Centraliza toda la lógica de negocio relacionada con planes de entrenamiento y programas, desde su creación con lógica automatizada (como la detección de inclusión de videos según la modalidad), hasta la actualización y gestión de estado. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, los modelos se encargan de la base de datos, y las vistas presentan la información al usuario. Una característica importante es el enriquecimiento de datos en el método index() mediante adjuntarInfoCupo(), lo cual permite mostrar información de ocupación directamente en el listado de planes.//
