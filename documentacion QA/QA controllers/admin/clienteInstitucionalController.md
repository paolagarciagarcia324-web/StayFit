Controlador encargado de gestionar los clientes institucionales de la plataforma StayFitMVC, permitiendo la administración de instituciones, generación de enlaces de registro y control de accesos institucionales.

//Este archivo clienteInstitucionalController.php corresponde al controlador encargado de gestionar toda la lógica relacionada con los clientes institucionales dentro de la plataforma StayFitMVC. Su función principal es administrar las relaciones entre instituciones, planes institucionales y los enlaces de acceso que permiten a los miembros de una institución registrarse en la plataforma. A diferencia del controlador de clientes fijos, este maneja un flujo más complejo donde las instituciones pueden generar enlaces únicos para que sus asociados se registren automáticamente bajo su institución. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (validaciones de instituciones activas, generación de tokens, control de estados) y delega las operaciones de almacenamiento y consulta a los respectivos modelos.//

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/institucion/institucionModel.php';
require_once __DIR__ . '/../../models/institucion/enlaceInstitucionalModel.php';
require_once __DIR__ . '/../../models/cliente/clienteInsModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos, helpers.php me proporciona funciones auxiliares reutilizables. Posteriormente cargo los modelos específicos que necesito: institucionModel.php para gestionar las instituciones registradas, enlaceInstitucionalModel.php para manejar los enlaces de acceso institucional, clienteInsModel.php para los clientes institucionales, y planModel.php para consultar los planes disponibles. Estas dependencias me permiten acceder a toda la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión de clientes institucionales.//

class ClienteInstitucionalController
{
    private InstitucionModel $institucionModel;
    private EnlaceInstitucionalModel $enlaceModel;
    private ClienteInsModel $clienteInsModel;
    private PlanModel $planModel;

//En este bloque declaro la clase ClienteInstitucionalController y sus propiedades privadas con tipado fuerte:
Declaro las cuatro propiedades que almacenarán las instancias de los modelos necesarios. A diferencia del controlador anterior, aquí utilizo tipado fuerte en las propiedades (InstitucionModel, EnlaceInstitucionalModel, etc.) lo cual me ayuda a tener un código más robusto y con mejor autocompletado en el IDE. Gracias a estas propiedades puedo acceder a las funciones encargadas de consultar instituciones, generar enlaces, gestionar clientes institucionales y consultar planes sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start();
    $this->validarAdministrador();
    $this->institucionModel = new InstitucionModel();
    $this->enlaceModel = new EnlaceInstitucionalModel();
    $this->clienteInsModel = new ClienteInsModel();
    $this->planModel = new PlanModel();
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades. Finalmente, instancio los cuatro modelos que necesito, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador. Esto me evita tener que crear las instancias repetidamente en cada método y mantiene el código más limpio.//

public function index()
{
    $instituciones = $this->institucionModel->obtenerTodos();
    $planes = $this->planModel->obtenerPlanesInstitucionales();
    $enlaces = $this->enlaceModel->obtenerTodosConDetalle();
    $clientesInstitucionales = $this->clienteInsModel->obtenerTodos();
    $enlacesPorInstitucion = [];
    foreach ($enlaces as $enlace) {
        $enlacesPorInstitucion[(int) ($enlace['id_institucion'] ?? 0)] = $enlace;
    }
    $mapaInstituciones = [];
    foreach ($instituciones as $inst) {
        $mapaInstituciones[(int) ($inst['id'] ?? $inst['id_institucion'] ?? 0)] = $inst['nombre'] ?? 'Institución';
    }
    foreach ($clientesInstitucionales as &$cliente) {
        $idInst = (int) ($cliente['id_institucion'] ?? 0);
        $cliente['institucion'] = $mapaInstituciones[$idInst] ?? 'Sin institución';
        $cliente['cliente'] = trim(($cliente['nombre_completo'] ?? '')
            ?: trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? '')));
    }
    unset($cliente);
    require_once __DIR__ . '/../../views/admin/clienteInstitucional.php';
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado completo de clientes institucionales con toda su información relacionada:
Este método lo diseñé para ser bastante completo, ya que necesito mostrar en una sola vista múltiples fuentes de datos. Primero obtengo todas las instituciones, los planes institucionales disponibles, los enlaces generados y los clientes institucionales registrados.
Luego realizo un procesamiento de datos en memoria para enriquecer la información que verá el administrador. Creo un array $enlacesPorInstitucion que mapea cada institución con su enlace correspondiente, lo cual me permite acceder rápidamente a esta información sin tener que buscar repetidamente.
Después construyo un $mapaInstituciones que asocia cada ID de institución con su nombre, facilitando la visualización. Finalmente, recorro los clientes institucionales y les agrego dos campos calculados: el nombre de la institución a la que pertenecen (usando el mapa que construí) y el nombre completo del cliente (que puede venir en un solo campo nombre_completo o separado en nombre y apellido).
El unset($cliente) al final es importante porque estoy usando referencia (&$cliente) en el foreach, y si no limpio la variable, podría causar comportamientos inesperados. Finalmente cargo la vista clienteInstitucional.php con todos estos datos ya procesados y listos para mostrarse.//

public function generarEnlace()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $institucionId = (int) ($_POST['institucion_id'] ?? 0);
    $planId = (int) ($_POST['plan_id'] ?? 0);
    if ($institucionId <= 0 || $planId <= 0) {
        $this->flash('error', 'Selecciona institución y plan.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $institucion = $this->institucionModel->obtenerPorId($institucionId);
    if (!$institucion || ($institucion['estado'] ?? '') !== 'activo') {
        $this->flash('error', 'La institución no existe o está inactiva.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $plan = $this->planModel->obtenerPorId($planId);
    if (!$plan || ($plan['estado'] ?? '') !== 'activo') {
        $this->flash('error', 'El plan seleccionado no está disponible.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $enlace = $this->enlaceModel->generarOActualizar(
        $institucionId,
        $planId,
        (int) ($_SESSION['usuario_id'] ?? 0) ?: null
    );
    if (!$enlace) {
        $this->flash('error', 'No se pudo generar el enlace. Verifica que la migración SQL esté aplicada.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $this->enlaceModel->registrarTrazabilidad(
        (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        'Enlace generado para ' . ($institucion['nombre'] ?? 'institución')
    );
    $this->flash('success', 'Enlace generado correctamente. Compártelo con las personas de la institución.');
    header('Location: clienteInstitucionalController.php');
    exit;
}

//El método generarEnlace() es uno de los más importantes de este controlador, ya que permite crear enlaces únicos para que los miembros de una institución se registren automáticamente:
Este método lo diseñé con múltiples capas de validación para asegurar que todo el proceso sea seguro y consistente.
Primero verifico que la solicitud sea POST, ya que esta acción modifica datos en el sistema. Luego obtengo y convierto a enteros los IDs de institución y plan que vienen del formulario. Valido que ambos sean mayores a cero para evitar IDs inválidos.
Después realizo validaciones más profundas: verifico que la institución exista y esté activa, y que el plan seleccionado también exista y esté disponible. Si alguna de estas validaciones falla, uso el método flash() para mostrar un mensaje de error y redirijo de vuelta.
Una vez que todo está validado, llamo al método generarOActualizar() del modelo de enlaces. Este método es inteligente: si ya existe un enlace para esa institución y plan, lo actualiza; si no, crea uno nuevo. Le paso el ID del administrador actual desde la sesión para registrar quién generó el enlace.
Si la generación falla (posiblemente por un problema de base de datos o migración no aplicada), muestro un error específico. Si todo sale bien, registro la acción en la trazabilidad para mantener un historial de auditoría y muestro un mensaje de éxito indicando que el enlace está listo para compartirse con los miembros de la institución.//

public function toggleEnlace()
{
    $idEnlace = (int) ($_GET['id'] ?? 0);
    $activo = ($_GET['activo'] ?? '1') === '1';
    if ($idEnlace <= 0) {
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $this->enlaceModel->activarDesactivar($idEnlace, $activo);
    $this->enlaceModel->registrarTrazabilidad(
        (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        $activo ? 'Enlace activado' : 'Enlace desactivado'
    );
    $this->flash('success', $activo ? 'Enlace activado.' : 'Enlace desactivado.');
    header('Location: clienteInstitucionalController.php');
    exit;
}

//El método toggleEnlace() me permite activar o desactivar enlaces institucionales de forma rápida:
Este método lo uso cuando necesito controlar el acceso de una institución temporalmente. Recibo mediante GET el ID del enlace y el estado al que debe cambiar (activo o inactivo).
Valido que el ID sea válido (mayor a cero) para evitar manipulaciones. Luego llamo al método activarDesactivar() del modelo que cambia el estado del enlace en la base de datos. Registro la acción en la trazabilidad con un mensaje dinámico que indica si se activó o desactivó el enlace.
Finalmente muestro un mensaje de confirmación al usuario y redirijo de vuelta al listado. Esta funcionalidad es muy útil cuando una institución suspende temporalmente su convenio con nosotros o cuando necesito revocar accesos por alguna razón administrativa.//

public function regenerarToken()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $idEnlace = (int) ($_POST['id_enlace'] ?? 0);
    if ($idEnlace <= 0) {
        $this->flash('error', 'Enlace no válido.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $enlace = $this->enlaceModel->regenerarToken(
        $idEnlace,
        (int) ($_SESSION['usuario_id'] ?? 0) ?: null
    );
    if (!$enlace) {
        $this->flash('error', 'No se pudo regenerar el token.');
        header('Location: clienteInstitucionalController.php');
        exit;
    }
    $this->enlaceModel->registrarTrazabilidad(
        (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        'Token regenerado para enlace #' . $idEnlace
    );
    $this->flash('success', 'Token regenerado. El enlace anterior ya no funciona.');
    header('Location: clienteInstitucionalController.php');
    exit;
}

//El método regenerarToken() me permite generar un nuevo token para un enlace existente, invalidando el anterior:
Esta funcionalidad la implementé por seguridad. Si un enlace institucional se comparte accidentalmente con personas no autorizadas o si necesito revocar el acceso anterior, puedo regenerar el token para que el enlace antiguo deje de funcionar inmediatamente.
Verifico que la solicitud sea POST (ya que es una acción que modifica datos) y que el ID del enlace sea válido. Luego llamo al método regenerarToken() del modelo que genera un nuevo token único y actualiza el registro en la base de datos.
Si la regeneración falla, muestro un error. Si tiene éxito, registro la acción en la trazabilidad para mantener el historial de auditoría y muestro un mensaje claro indicando que el enlace anterior ya no funciona. Esto es importante para que el administrador sepa que debe compartir el nuevo enlace con los miembros de la institución.//

private function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

//El método privado flash() es una herramienta interna que me permite almacenar mensajes temporales en la sesión:
Este método lo creé para no repetir código en cada función del controlador. Recibe dos parámetros: el tipo de mensaje (success, error, warning, etc.) y el mensaje en sí. Almacena ambos en la variable de sesión $_SESSION['flash'] que luego la vista puede leer y mostrar al usuario.
La ventaja de usar este sistema es que los mensajes persisten a través de redirecciones (ya que uso header('Location: ...') en todos los métodos), permitiendo mostrar feedback al usuario después de una acción. Una vez que la vista muestra el mensaje, normalmente se limpia la variable de sesión para no mostrarlo nuevamente.//

private function validarAdministrador(): void
{
    $rol = strtolower($_SESSION['rol'] ?? '');
    if ($rol !== 'admin' && $rol !== 'administrador') {
        header('Location: ../../views/auth/accesoDenegado.php');
        exit;
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
A diferencia del controlador anterior que usaba una función externa, aquí implementé la validación directamente en el controlador. Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive (así evito problemas si el rol está guardado como 'Admin', 'ADMIN', etc.).
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente.//

$controller = new ClienteInstitucionalController();
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
clienteInstitucionalController.php?accion=index (listado principal)
clienteInstitucionalController.php?accion=generarEnlace (generar nuevo enlace)
clienteInstitucionalController.php?accion=toggleEnlace&id=5&activo=0 (activar/desactivar enlace)
clienteInstitucionalController.php?accion=regenerarToken (regenerar token de enlace)

Este controlador es el corazón de la gestión de clientes institucionales en StayFitMVC. Centraliza toda la lógica de negocio relacionada con instituciones, desde la visualización completa de clientes institucionales con sus datos enriquecidos, hasta la generación y gestión de enlaces de acceso. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo, las validaciones y la preparación de datos; los modelos se encargan de la base de datos; y las vistas presentan la información al usuario. El sistema de enlaces institucionales permite un flujo de registro automatizado donde los miembros de una institución pueden registrarse directamente bajo su institución usando un enlace único, lo cual facilita la gestión de convenios corporativos.//