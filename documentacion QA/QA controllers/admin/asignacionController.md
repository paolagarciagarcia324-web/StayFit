Este archivo asignacionController.php corresponde al controlador encargado de gestionar las asignaciones realizadas dentro de la plataforma StayFitMVC. Su función principal es administrar la relación entre clientes, coaches y programas virtuales, permitiendo que un administrador pueda asignar entrenadores, reasignarlos cuando sea necesario y vincular contenido virtual a los clientes. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria y delega las operaciones de almacenamiento y consulta a los respectivos modelos.

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/coach/coachModel.php';
require_once __DIR__ . '/../../models/contenidoVirtual/programaVirtualModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';

//En este bloque incluyo todos los archivos necesarios para el funcionamiento del controlador. Importo el archivo roles.php para validar permisos administrativos y posteriormente cargo los modelos que gestionan clientes, coaches, programas virtuales y planes. Estas dependencias permiten que el controlador pueda acceder a la información almacenada en la base de datos y ejecutar las operaciones necesarias para las asignaciones.//

class AsignacionController
{

//En este bloque declaro la clase AsignacionController, cuya responsabilidad es centralizar todas las operaciones relacionadas con la asignación de recursos a los clientes. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

private $clienteModel;
private $coachModel;
private $programaVirtualModel;
private $planModel;

//Estas propiedades almacenan las instancias de los modelos que serán utilizadas por el controlador. Gracias a ellas puedo acceder a las funciones encargadas de consultar clientes, coaches, programas virtuales y planes sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción.//

public function __construct()

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo, validar permisos y crear las instancias necesarias para interactuar con los modelos//

session_start();

//En esta línea inicio o recupero la sesión activa del usuario. Esto me permite acceder a información importante como el identificador del usuario autenticado y su rol dentro de la plataforma.//

$this->validarAdministrador();

//Antes de permitir el acceso a cualquier funcionalidad del controlador, verifico que el usuario autenticado posea privilegios administrativos. Esta validación garantiza la seguridad del módulo y evita accesos no autorizados.//

$this->clienteModel = new ClienteModel();
$this->coachModel = new CoachModel();
$this->programaVirtualModel = new ProgramaVirtualModel();
$this->planModel = new PlanModel();

//En estas líneas creo las instancias de los modelos que serán utilizadas durante la ejecución del controlador. Cada modelo se encarga de gestionar una parte específica de la información almacenada en la base de datos.//

public function index()

//Este método representa la acción principal del controlador. Su función consiste en recopilar toda la información necesaria para mostrar la vista administrativa de asignaciones.//

if ($this->planModel->contar() === 0)

//Antes de cargar la información verifico si existen planes registrados dentro del sistema. Esta validación garantiza que las asignaciones puedan realizarse correctamente.//

$this->planModel->asegurarPlanesBase();

//Si no existen planes registrados, ejecuto este método para generar automáticamente los planes básicos necesarios para el funcionamiento de la plataforma.//

$clientes = $this->clienteModel->obtenerClientesActivos();

//Obtengo todos los clientes activos registrados en la plataforma para que puedan ser seleccionados dentro de la vista de asignaciones.//

$coaches = $this->coachModel->obtenerActivos();

//Recupero todos los coaches activos disponibles para ser asignados a los clientes.//

$programas = $this->programaVirtualModel->obtenerActivos();

//Consulto los programas virtuales activos que podrán ser asignados a los clientes como parte de su proceso de entrenamiento.//

$asignaciones = $this->clienteModel->obtenerAsignaciones();

//Recupero todas las asignaciones existentes para mostrarlas dentro de la interfaz administrativa y permitir su gestión.//

$totalPlanes = $this->planModel->contar();

//Obtengo la cantidad total de planes registrados en el sistema para mostrar información adicional dentro de la vista.//

$flash = $_SESSION['flash'] ?? null;

//Recupero cualquier mensaje temporal almacenado en la sesión para informar al administrador sobre el resultado de acciones realizadas previamente.//

unset($_SESSION['flash']);

//Una vez leído el mensaje temporal, lo elimino de la sesión para evitar que vuelva a mostrarse en futuras solicitudes.//

require_once __DIR__ . '/../../views/admin/asignaciones.php';

//Finalmente cargo la vista encargada de mostrar toda la información relacionada con las asignaciones dentro del panel administrativo.//

Ruta utilizada
views/
└── admin/
    └── asignaciones.php

public function asignarCoach()


//Este método permite asignar un coach a un cliente específico. Su función es procesar la información enviada desde el formulario administrativo y actualizar la relación correspondiente en la base de datos.//

if ($_SERVER['REQUEST_METHOD'] === 'POST')

//Verifico que la información haya sido enviada mediante una solicitud POST para garantizar que los datos provienen de un formulario válido.//

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$coachId = (int) ($_POST['coach_id'] ?? 0);

//Recupero los identificadores del cliente y del coach seleccionados dentro del formulario y los convierto a números enteros para garantizar un formato adecuado.//

$this->clienteModel->asignarCoach($clienteId, $coachId)

//Solicito al modelo que establezca la relación entre el cliente y el coach seleccionado dentro de la base de datos.//

$this->registrarTrazabilidad("Coach asignado...");

//Después de completar la asignación registro la acción en la bitácora administrativa para mantener un historial de cambios realizados por los administradores.//

$this->flash('success', 'Coach asignado correctamente...');

//Si la operación fue exitosa, genero un mensaje temporal que será mostrado al administrador cuando la página sea recargada.//

$this->flash('error', 'No se pudo asignar el coach...');

//Si ocurre algún problema durante el proceso, almaceno un mensaje de error para informar al administrador sobre la situación.//

public function asignarContenidoVirtual()

//Este método permite asociar un programa virtual a un cliente. Gracias a esta funcionalidad, los usuarios pueden acceder al contenido digital asignado por la administración.//

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$programaId = (int) ($_POST['programa_virtual_id'] ?? 0);

//Obtengo los identificadores correspondientes al cliente y al programa virtual que serán relacionados dentro del sistema.//

$this->programaVirtualModel->asignarCliente($clienteId, $programaId)

//Solicito al modelo registrar la relación entre el cliente y el programa virtual seleccionado.//

$this->registrarTrazabilidad(...)
$this->flash(...)

//Una vez completada la operación, registro el evento en la bitácora y genero un mensaje de éxito o error según el resultado obtenido.//

public function cambiarCoach()

//Este método permite reasignar un coach diferente a un cliente que ya posee una asignación previa. Se utiliza cuando es necesario realizar cambios en el acompañamiento del usuario.//

$clienteId = (int) ($_POST['cliente_id'] ?? 0);
$coachId = (int) ($_POST['coach_id'] ?? 0);

//Recupero los identificadores enviados desde el formulario para identificar tanto al cliente como al nuevo coach asignado.//

$this->clienteModel->cambiarCoach($clienteId, $coachId)

//Solicito al modelo actualizar la relación existente para que el cliente quede asociado al nuevo coach seleccionado.//

$this->registrarTrazabilidad(...)
$this->flash(...)

//Después de realizar el cambio registro el evento en la bitácora administrativa y genero el mensaje correspondiente para informar el resultado de la operación.//

private function flash($tipo, $mensaje)

//Este método privado se utiliza para almacenar mensajes temporales dentro de la sesión. Estos mensajes permiten informar al usuario sobre el resultado de las acciones ejecutadas.//

$_SESSION['flash'] = [
    'tipo' => $tipo,
    'mensaje' => $mensaje
];

//Guardo el tipo de mensaje y su contenido dentro de la sesión para que puedan mostrarse posteriormente en la interfaz.//

private function registrarTrazabilidad($accion)

//Este método se encarga de registrar todas las acciones importantes realizadas por los administradores dentro del módulo de asignaciones.//

$adminId = $_SESSION['usuario_id'] ?? null;

//Recupero el identificador del administrador autenticado para asociar la acción ejecutada con el usuario responsable.//

$this->clienteModel->registrarTrazabilidad($adminId, $accion);

//Envío la información al modelo para que quede almacenada en la bitácora o historial de actividades del sistema.//

private function validarAdministrador()

//Este método protege todas las funcionalidades del controlador garantizando que únicamente los usuarios con permisos administrativos puedan acceder a ellas.//

validarAccesoAdministrador();

//Utilizo la función definida en roles.php para verificar que exista una sesión válida y que el usuario posea el rol de administrador.//

$controller = new AsignacionController();

//En esta línea creo una instancia del controlador para comenzar a procesar las solicitudes recibidas desde el navegador.//

$accion = $_GET['accion'] ?? 'index';

//Obtengo la acción solicitada mediante la URL. Si no se especifica ninguna acción, utilizo el método index como comportamiento predeterminado.//

if (method_exists($controller, $accion))

//Antes de ejecutar la acción solicitada verifico que el método realmente exista dentro del controlador para evitar errores de ejecución.//

$controller->$accion();

//Si el método existe, lo ejecuto dinámicamente permitiendo que el controlador responda a diferentes solicitudes utilizando un único punto de entrada.//

$controller->index();

//Si la acción solicitada no existe, ejecuto el método principal index() para mostrar la vista principal del módulo.

Dentro de la arquitectura MVC implementada en StayFitMVC, el archivo asignacionController.php cumple el papel de controlador. Su responsabilidad consiste en recibir solicitudes del usuario, validar permisos administrativos, coordinar la comunicación con los modelos de clientes, coaches, programas virtuales y planes, y finalmente enviar la información necesaria a la vista asignaciones.php. Gracias a esta separación de responsabilidades se mantiene una estructura organizada, escalable y fácil de mantener dentro del proyecto.//