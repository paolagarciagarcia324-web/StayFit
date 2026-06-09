Controlador encargado de gestionar las instituciones y convenios corporativos dentro de la plataforma StayFitMVC, permitiendo su registro, actualización, vinculación de clientes y gestión de enlaces de acceso institucional.

//Este archivo institucionController.php corresponde al controlador encargado de gestionar todas las operaciones relacionadas con las instituciones y convenios corporativos dentro de la plataforma StayFitMVC. Su función principal es administrar el ciclo de vida completo de las instituciones: desde el registro con sus datos fiscales y de contacto, la generación automática de enlaces de registro para sus asociados, la vinculación de clientes existentes a instituciones, hasta la gestión de su estado. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (validaciones de datos, verificación de planes activos, sincronización de enlaces) y delega las operaciones de almacenamiento y consulta a los respectivos modelos. Es uno de los controladores más complejos del sistema porque maneja la relación entre instituciones, planes institucionales, enlaces de acceso y clientes institucionales.//

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/institucion/institucionModel.php';
require_once __DIR__ . '/../../models/institucion/enlaceInstitucionalModel.php';
require_once __DIR__ . '/../../models/cliente/clienteInsModel.php';
require_once __DIR__ . '/../../models/plan/planModel.php';

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos, helpers.php me proporciona funciones auxiliares reutilizables. Posteriormente cargo los cuatro modelos que necesito: institucionModel.php para gestionar las instituciones registradas, enlaceInstitucionalModel.php para manejar los enlaces de acceso institucional, clienteInsModel.php para los clientes institucionales y sus vinculaciones, y planModel.php para consultar los planes disponibles. Estas dependencias me permiten acceder a toda la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión completa de instituciones y convenios corporativos.//

class InstitucionController
{
    private InstitucionModel $institucionModel;
    private EnlaceInstitucionalModel $enlaceModel;
    private ClienteInsModel $clienteInsModel;
    private PlanModel $planModel;

//En este bloque declaro la clase InstitucionController y sus propiedades privadas con tipado fuerte:
Declaro las cuatro propiedades que almacenarán las instancias de los modelos necesarios. Utilizo tipado fuerte en las propiedades (InstitucionModel, EnlaceInstitucionalModel, etc.) lo cual me ayuda a tener un código más robusto y con mejor autocompletado en el IDE. Gracias a estas propiedades puedo acceder a las funciones encargadas de consultar instituciones, generar enlaces, gestionar clientes institucionales y consultar planes sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

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
    $clientesInstitucionales = $this->clienteInsModel->obtenerTodos();
    $planes = $this->planModel->obtenerPlanesInstitucionales();
    $enlaces = $this->enlaceModel->obtenerTodosConDetalle();
    $enlacesPorInstitucion = [];
    foreach ($enlaces as $enlace) {
        $enlacesPorInstitucion[(int) ($enlace['id_institucion'] ?? 0)] = $enlace;
    }
    require_once __DIR__ . '/../../views/admin/instituciones.php';
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado completo de instituciones con toda su información relacionada:
Este método lo diseñé para cargar todos los datos necesarios para la vista de instituciones. Primero obtengo todas las instituciones registradas, los clientes institucionales, los planes institucionales disponibles y los enlaces generados con sus detalles.
Luego proceso los enlaces en memoria para crear un array $enlacesPorInstitucion que mapea cada institución con su enlace correspondiente. Esto me permite acceder rápidamente a esta información en la vista sin tener que buscar repetidamente. La vista instituciones.php recibirá todos estos datos y se encargará de mostrar el listado completo, los formularios de creación y edición, y la información de los enlaces de registro.//

public function guardar()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: institucionController.php');
        exit;
    }
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $datos = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'nit' => trim($_POST['nit'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'correo' => trim($_POST['correo'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'estado' => 'activo',
    ];
    if ($datos['nombre'] === '' || $planId <= 0) {
        $this->flash('error', 'Completa los datos de la institución y selecciona un plan de convenio.');
        header('Location: institucionController.php');
        exit;
    }
    if (!$this->validarPlan($planId)) {
        $this->flash('error', 'El plan seleccionado no es válido o no está activo.');
        header('Location: institucionController.php');
        exit;
    }
    $idInstitucion = $this->institucionModel->crear($datos);
    if (!$idInstitucion) {
        $this->flash('error', 'No se pudo crear la institución.');
        header('Location: institucionController.php');
        exit;
    }
    $enlace = $this->enlaceModel->sincronizarEnlace(
        $idInstitucion,
        $planId,
        (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        false
    );
    if (!$enlace) {
        $this->flash('warning', 'Institución creada, pero no se pudo generar el enlace de registro. Edítala para intentar de nuevo.');
    } else {
        $this->flash('success', 'Institución creada con plan y enlace de registro listos para compartir.');
    }
    $this->registrarTrazabilidad('Institución registrada con plan #' . $planId);
    header('Location: institucionController.php');
    exit;
}

//El método guardar() se encarga del registro completo de una nueva institución con generación automática de enlace:
Este método lo diseñé con múltiples capas de validación y un flujo de creación que incluye tanto la institución como su enlace de acceso.
Primero verifico que la solicitud sea POST, ya que es una acción que modifica datos. Obtengo el ID del plan y preparo un array con todos los datos de la institución: nombre, NIT, teléfono, correo, dirección y estado inicial 'activo'.
Valido que el nombre no esté vacío y que se haya seleccionado un plan válido. Luego uso el método privado validarPlan() para verificar que el plan exista y esté activo. Si alguna validación falla, muestro un mensaje de error específico y redirijo.
Una vez validado todo, llamo al método crear() del modelo de instituciones. Si la creación falla, muestro un error. Si tiene éxito, procedo a generar el enlace de registro usando el método sincronizarEnlace() del modelo de enlaces. Este método es inteligente: crea o actualiza el enlace según sea necesario, y el parámetro false indica que no debe regenerar el token (es una creación nueva).
Si el enlace no se pudo generar, muestro una advertencia pero la institución ya fue creada. Si todo salió bien, muestro un mensaje de éxito indicando que la institución y el enlace están listos para compartirse. Finalmente registro la acción en la trazabilidad y redirijo al listado.//

public function actualizar()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: institucionController.php');
        exit;
    }
    $idInstitucion = (int) ($_POST['id'] ?? 0);
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $regenerarEnlace = !empty($_POST['regenerar_enlace']);
    $datos = [
        'id' => $idInstitucion,
        'nombre' => trim($_POST['nombre'] ?? ''),
        'nit' => trim($_POST['nit'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'correo' => trim($_POST['correo'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'estado' => $_POST['estado'] ?? 'activo',
    ];
    if ($idInstitucion <= 0 || $datos['nombre'] === '' || $planId <= 0) {
        $this->flash('error', 'Datos incompletos para actualizar la institución.');
        header('Location: institucionController.php');
        exit;
    }
    if (!$this->validarPlan($planId)) {
        $this->flash('error', 'El plan seleccionado no es válido.');
        header('Location: institucionController.php');
        exit;
    }
    if (!$this->institucionModel->actualizar($datos)) {
        $this->flash('error', 'No se pudo actualizar la institución.');
        header('Location: institucionController.php');
        exit;
    }
    $adminId = (int) ($_SESSION['usuario_id'] ?? 0) ?: null;
    $enlace = $this->enlaceModel->sincronizarEnlace($idInstitucion, $planId, $adminId, $regenerarEnlace);
    if (!$enlace) {
        $this->flash('warning', 'Institución actualizada, pero el enlace no pudo sincronizarse.');
    } elseif ($regenerarEnlace) {
        $this->flash('success', 'Institución, plan y enlace actualizados. El enlace anterior ya no funciona.');
    } else {
        $this->flash('success', 'Institución y plan del enlace actualizados. El mismo enlace sigue activo.');
    }
    $this->registrarTrazabilidad('Institución actualizada (plan #' . $planId . ')');
    header('Location: institucionController.php');
    exit;
}

//El método actualizar() me permite modificar la información de una institución existente y sincronizar su enlace:
Este método lo diseñé con un flujo similar al de creación pero con opciones adicionales para la gestión del enlace.
Primero verifico que sea POST y obtengo el ID de la institución, el plan ID y una bandera regenerarEnlace que indica si debo generar un nuevo token para el enlace. Preparo el array de datos con toda la información actualizable, incluyendo el estado que puede cambiar entre 'activo' e 'inactivo'.
Valido que el ID sea válido, que el nombre no esté vacío y que se haya seleccionado un plan. Luego verifico que el plan sea válido y activo. Si alguna validación falla, muestro el error correspondiente y redirijo.
Actualizo la institución en la base de datos usando el método actualizar() del modelo. Si falla, muestro un error. Si tiene éxito, procedo a sincronizar el enlace usando sincronizarEnlace() con el parámetro $regenerarEnlace que determina si se genera un nuevo token o se mantiene el existente.
Manejo tres escenarios de respuesta:
Si el enlace no se pudo sincronizar, muestro una advertencia pero la institución ya fue actualizada.
Si se solicitó regenerar el enlace y tuvo éxito, informo que el enlace anterior ya no funciona.
Si no se solicitó regenerar, informo que el mismo enlace sigue activo.
Finalmente registro la acción en la trazabilidad y redirijo al listado.//

public function vincularCliente()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $datos = [
            'cliente_id' => $_POST['cliente_id'],
            'institucion_id' => $_POST['institucion_id'],
            'cargo' => trim($_POST['cargo'] ?? ''),
            'estado' => 'activo',
        ];
        $this->clienteInsModel->vincularInstitucion($datos);
        $this->registrarTrazabilidad('Cliente vinculado a institución');
    }
    header('Location: institucionController.php');
    exit;
}

//El método vincularCliente() me permite asociar un cliente existente a una institución:
Este método lo diseñé para manejar casos donde un cliente ya registrado en el sistema debe ser vinculado a una institución específica (por ejemplo, cuando una empresa contrata el servicio para sus empleados y algunos ya tenían cuenta individual).
Verifico que la solicitud sea POST y preparo un array con los datos de la vinculación: el ID del cliente, el ID de la institución, el cargo del cliente dentro de la institución (opcional) y el estado inicial 'activo'.
Llamo al método vincularInstitucion() del modelo de clientes institucionales que crea el registro de vinculación en la base de datos. Registro la acción en la trazabilidad para mantener un historial de estas asociaciones. Redirijo al listado principal.//

public function cambiarEstado()
{
    if (isset($_GET['id'], $_GET['estado'])) {
        $this->institucionModel->cambiarEstado($_GET['id'], $_GET['estado']);
        $this->registrarTrazabilidad('Estado de institución cambiado');
    }
    header('Location: institucionController.php');
    exit;
}

//El método cambiarEstado() me permite activar o desactivar instituciones de forma rápida:
Recibo mediante GET el ID de la institución y el nuevo estado. Verifico que ambos parámetros existan usando isset() con múltiples argumentos (sintaxis compacta).
Llamo al método cambiarEstado() del modelo que actualiza únicamente este campo en la base de datos. Esto me permite inhabilitar una institución temporalmente sin perder su información histórica (clientes vinculados, enlaces generados, etc.). Registro la acción en la trazabilidad y redirijo al listado. Este método es muy útil cuando un convenio corporativo se suspende temporalmente.//

private function validarPlan(int $planId): bool
{
    $plan = $this->planModel->obtenerPorId($planId);
    return $plan && ($plan['estado'] ?? '') === 'activo';
}

//El método privado validarPlan() es una herramienta interna que me permite verificar si un plan es válido y está activo:
Este método lo creé para no repetir la lógica de validación de planes en los métodos guardar() y actualizar(). Recibe el ID del plan como parámetro (con tipado fuerte int) y retorna un booleano.
Obtengo el plan desde el modelo usando obtenerPorId(). Luego verifico dos condiciones: que el plan exista (no sea null o false) y que su estado sea 'activo'. Uso el operador de fusión null (??) para manejar el caso donde el campo 'estado' no exista en el array.
Si ambas condiciones se cumplen, retorno true; si alguna falla, retorno false. Este método me permite mantener el código DRY (Don't Repeat Yourself) y centralizar la lógica de validación de planes.//

private function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

//El método privado flash() es una herramienta interna que me permite almacenar mensajes temporales en la sesión:
Este método lo creé para no repetir código en cada función del controlador. Recibe dos parámetros con tipado fuerte: el tipo de mensaje (string) y el mensaje en sí (string). Retorna void porque solo almacena el valor sin devolver nada.
Almacena ambos en la variable de sesión $_SESSION['flash'] que luego la vista puede leer y mostrar al usuario. La ventaja de usar este sistema es que los mensajes persisten a través de redirecciones, permitiendo mostrar feedback al usuario después de una acción. Una vez que la vista muestra el mensaje, normalmente se limpia la variable de sesión.//

private function registrarTrazabilidad(string $accion): void
{
    $adminId = $_SESSION['usuario_id'] ?? null;
    $this->institucionModel->registrarTrazabilidad($adminId, $accion);
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Institución registrada con plan #5", "Cliente vinculado a institución").
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién hizo qué cambio y cuándo, lo cual es fundamental para la seguridad y el control administrativo.//

private function validarAdministrador(): void
{
    $rol = strtolower($_SESSION['rol'] ?? '');
    if ($rol !== 'admin' && $rol !== 'administrador') {
        header('Location: ../../views/auth/accesoDenegado.php');
        exit;
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método lo implementé directamente en el controlador. Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive.
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente.//

$controller = new InstitucionController();
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
institucionController.php?accion=index (listado principal)
institucionController.php?accion=guardar (crear nueva institución - vía POST)
institucionController.php?accion=actualizar (editar institución - vía POST)
institucionController.php?accion=vincularCliente (vincular cliente a institución - vía POST)
institucionController.php?accion=cambiarEstado&id=5&estado=inactivo (activar/desactivar institución)//

//Este controlador es el corazón de la gestión de instituciones y convenios corporativos en StayFitMVC. Centraliza toda la lógica de negocio relacionada con instituciones, desde su creación con generación automática de enlaces de registro, hasta la actualización con sincronización de enlaces y vinculación de clientes existentes. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo, las validaciones y la preparación de datos; los modelos se encargan de la base de datos; y las vistas presentan la información al usuario. Una característica importante es el sistema de sincronización de enlaces que permite mantener coherencia entre instituciones, planes y enlaces de acceso, con opción de regenerar tokens cuando sea necesario por seguridad.//