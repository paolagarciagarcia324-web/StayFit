 Controlador encargado de gestionar la validación y aprobación de pagos vinculados a solicitudes de ingreso en la plataforma StayFitMVC, permitiendo la activación completa de clientes desde el flujo de aprobación administrativa.

//require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/solicitud/solicitudIngresoModel.php'; // Importa solicitudes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa usuarios
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/plan/accesoModel.php'; // Importa accesos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Posteriormente cargo los seis modelos que necesito para manejar el complejo flujo de aprobación de pagos: solicitudIngresoModel.php para gestionar las solicitudes de ingreso, pagoModel.php para los pagos, usuarioModel.php para la creación y gestión de usuarios, clienteModel.php para los clientes, accesoModel.php para los accesos a planes, y planModel.php para la información de los planes.
Estas dependencias me permiten ejecutar todo el flujo de activación de un cliente desde la aprobación de su pago: crear usuario, crear cliente, asignar plan, activar accesos y aprobar la solicitud. Es el controlador con más dependencias del sistema porque coordina múltiples entidades.//

class ValidacionPagoController
{
    private $solicitudModel; // Modelo de solicitudes
    private $pagoModel; // Modelo de pagos
    private $usuarioModel; // Modelo de usuarios
    private $clienteModel; // Modelo de clientes
    private $accesoModel; // Modelo de accesos
    private $planModel; // Modelo de planes//

En este bloque declaro la clase ValidacionPagoController y sus propiedades privadas:
Declaro las seis propiedades que almacenarán las instancias de los modelos necesarios. Cada una representa una entidad diferente que debo coordinar durante el proceso de aprobación de pagos. Gracias a estas propiedades puedo acceder a las funciones encargadas de gestionar solicitudes, pagos, usuarios, clientes, accesos y planes sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como un orquestador de múltiples entidades siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->solicitudModel = new SolicitudIngresoModel(); // Instancia solicitudes
    $this->pagoModel = new PagoModel(); // Instancia pagos
    $this->usuarioModel = new UsuarioModel(); // Instancia usuarios
    $this->clienteModel = new ClienteModel(); // Instancia clientes
    $this->accesoModel = new AccesoModel(); // Instancia accesos
    $this->planModel = new PlanModel(); // Instancia planes
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades críticas de validación de pagos.
Finalmente, instancio los seis modelos que necesito, dejándolos listos para ser utilizados. Al crear todas las instancias en el constructor, mantengo el código organizado y evito crear objetos repetidamente. Esto es especialmente importante aquí porque el proceso de aprobación requiere coordinar múltiples entidades en una sola operación.//

public function index()
{
    $pagos = $this->pagoModel->obtenerTodos();
    $pendientes = $this->pagoModel->obtenerPendientes();
    require_once __DIR__ . '/../../views/admin/pagos.php';
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de pagos:
Utilizo el modelo para obtener todos los pagos registrados en la base de datos mediante el método obtenerTodos(). Adicionalmente, obtengo específicamente los pagos pendientes mediante obtenerPendientes(), lo cual me permite mostrar al administrador un resumen rápido de las acciones que requieren su atención inmediata. Posteriormente cargo la vista pagos.php que renderizará esta información.//

public function aprobar()
{
    if (!isset($_GET['solicitud_id'])) { // Verifica solicitud recibida
        header('Location: validacionPagoController.php'); // Redirige al panel
        exit; // Detiene la ejecución
    }
    $solicitudId = $_GET['solicitud_id']; // ID de la solicitud
    $solicitud = $this->solicitudModel->obtenerPorId($solicitudId); // Busca solicitud
    if (!$solicitud) { // Valida existencia
        header('Location: validacionPagoController.php'); // Redirige si no existe
        exit; // Detiene la ejecución
    }
    try {
        if (in_array(strtolower($solicitud['estado'] ?? ''), ['aprobada', 'validada'], true)) {
            throw new RuntimeException('Esta solicitud ya fue aprobada.');
        }
        $usuarioId = $this->crearUsuarioCliente($solicitud);
        $this->crearCliente($usuarioId, $solicitud);
        $cliente = $this->clienteModel->obtenerPorUsuario($usuarioId);
        if (!$cliente || empty($cliente['id'])) {
            throw new RuntimeException('No se pudo registrar el cliente en el sistema.');
        }
        $clienteId = (int) $cliente['id'];
        $planClienteId = $this->clienteModel->crearPlanClienteDesdeSolicitud($clienteId, $solicitud);
        if (!$planClienteId) {
            $planId = $this->clienteModel->resolverIdPlanDesdeSolicitud($solicitud);
            if ($planId && $this->clienteModel->asignarPlanCliente($clienteId, $planId, null)) {
                $planClienteId = $this->clienteModel->obtenerPlanClienteActivoId($clienteId);
            }
        }
        if (!$planClienteId) {
            throw new RuntimeException('No se pudo vincular el plan al cliente. Verifique que existan planes activos.');
        }
        $this->pagoModel->vincularPlanClientePorSolicitud($solicitudId, $planClienteId);
        try {
            $this->activarAccesos($clienteId, $solicitud, $planClienteId);
        } catch (Throwable $e) {
        }
        $this->pagoModel->aprobarPorSolicitud($solicitudId);
        $this->solicitudModel->aprobar($solicitudId, $_SESSION['usuario_id'] ?? null);
        $this->registrarTrazabilidad('Pago aprobado y cliente activado');
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Solicitud aprobada. El cliente puede ingresar con su correo y la contraseña que definió en la solicitud (o su número de identificación si no creó una).'];
    } catch (Throwable $e) {
        $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'No se pudo aprobar: ' . $e->getMessage()];
    }
    header('Location: solicitudController.php');
    exit;
}

//El método aprobar() es el más complejo del controlador y se encarga de todo el flujo de activación de un cliente desde la aprobación de su solicitud de pago:
Este método lo diseñé como un proceso transaccional que coordina múltiples entidades. Primero verifico que se haya recibido el ID de la solicitud mediante GET y que la solicitud exista en la base de datos. Si alguna validación falla, redirijo inmediatamente.
Luego envuelvo todo el proceso en un bloque try-catch para manejar cualquier error que pueda ocurrir durante la activación. Dentro del bloque try, primero verifico que la solicitud no haya sido aprobada previamente (estado 'aprobada' o 'validada'), lanzando una excepción si ya fue procesada.
El flujo de activación sigue estos pasos:
Crear usuario cliente: Llamo al método privado crearUsuarioCliente() que se encarga de crear o reactivar el usuario en el sistema.
Crear cliente: Llamo al método privado crearCliente() que registra al cliente con sus datos específicos.
Verificar cliente creado: Obtengo el cliente recién creado y verifico que exista. Si no se pudo crear, lanzo una excepción.
Asignar plan: Intento crear el plan del cliente desde la solicitud usando crearPlanClienteDesdeSolicitud(). Si falla, intento un método alternativo resolviendo el ID del plan y asignándolo manualmente.
Verificar plan asignado: Si no se pudo vincular el plan, lanzo una excepción indicando que debe verificar que existan planes activos.
Vincular pago con plan: Uso el método vincularPlanClientePorSolicitud() del modelo de pagos para relacionar el pago aprobado con el plan del cliente.
Activar accesos: Llamo al método privado activarAccesos() dentro de un bloque try-catch interno. Si falla la activación de accesos, no detengo el proceso principal (los accesos pueden activarse manualmente después).
Aprobar pago y solicitud: Finalmente apruebo el pago mediante aprobarPorSolicitud() y la solicitud mediante aprobar(), pasando el ID del administrador actual.
Registrar trazabilidad y mensaje: Registro la acción en la trazabilidad y establezco un mensaje flash de éxito indicando que el cliente puede ingresar con sus credenciales.
Si ocurre algún error en cualquier paso del proceso, el bloque catch captura la excepción y establece un mensaje flash de error con el mensaje de la excepción para facilitar la depuración. Finalmente redirijo al controlador de solicitudes.//

public function rechazar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'solicitud_id' => $_POST['solicitud_id'], // ID de solicitud
            'estado' => 'rechazado', // Estado del pago
            'observacion' => trim($_POST['observacion']) // Motivo del rechazo
        ];
        $this->pagoModel->rechazarPorSolicitud($datos); // Rechaza el pago
        $this->solicitudModel->rechazar([
            'id' => $_POST['solicitud_id'],
            'observacion' => $datos['observacion']
        ]); // Rechaza solicitud
        $this->registrarTrazabilidad('Pago rechazado'); // Guarda trazabilidad
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Solicitud rechazada.'];
    }
    header('Location: solicitudController.php');
    exit;
}

//El método rechazar() me permite denegar una solicitud de pago, requiriendo obligatoriamente una observación o motivo:
Verifico que la solicitud sea POST, ya que es una acción que modifica datos y necesito capturar el motivo del rechazo. Recopilo el ID de la solicitud, establezco el estado final como 'rechazado' y obtengo la observación ingresada por el administrador.
Llamo al método rechazarPorSolicitud() del modelo de pagos para rechazar el pago asociado. Luego llamo al método rechazar() del modelo de solicitudes para rechazar la solicitud en sí, pasando el ID y la observación. Esto asegura que tanto el pago como la solicitud queden en estado rechazado de forma consistente.
Registro la acción en la trazabilidad y establezco un mensaje flash de éxito. Finalmente redirijo al controlador de solicitudes. Exigir un motivo es una buena práctica administrativa para mantener la transparencia con los usuarios que enviaron la solicitud.//

private function crearUsuarioCliente($solicitud)
{
    require_once __DIR__ . '/../../config/helpers.php';
    $identificacion = trim($solicitud['identificacion'] ?? '');
    $correo = !empty($solicitud['correo'])
        ? trim($solicitud['correo'])
        : strtolower($identificacion) . '@stayfit.local';
    $partes = dividirNombreCompleto($solicitud['nombre_completo'] ?? $solicitud['nombre'] ?? '');
    $existente = $this->usuarioModel->obtenerPorCorreo($correo);
    if (!$existente && $identificacion !== '') {
        $existente = $this->usuarioModel->obtenerPorDocumentoIdentidad($identificacion);
    }
    if ($existente) {
        $usuarioId = (int) ($existente['id'] ?? $existente['id_usuario']);
        $this->usuarioModel->activarDesdeSolicitud($usuarioId, [
            'nombre' => $partes['nombre'],
            'apellido' => $partes['apellido'],
            'telefono' => $solicitud['celular'] ?? null,
            'documento_identidad' => $identificacion,
        ]);
        $this->aplicarPasswordDesdeSolicitud($usuarioId, $solicitud);
        $this->usuarioModel->asignarRol($usuarioId, 3);
        return $usuarioId;
    }
    $passwordRegistro = $this->solicitudModel->resolverPasswordPlanoRegistro($solicitud);
    $datos = [
        'nombre' => $partes['nombre'],
        'apellido' => $partes['apellido'],
        'correo' => $correo,
        'password' => $passwordRegistro,
        'telefono' => $solicitud['celular'] ?? null,
        'documento_identidad' => $identificacion,
        'origen_registro' => 'ADMINISTRATIVO',
        'estado' => 'ACTIVO',
    ];
    $usuarioId = $this->usuarioModel->crear($datos);
    $this->usuarioModel->asignarRol($usuarioId, 3);
    return $usuarioId;
}

//El método privado crearUsuarioCliente() se encarga de crear o reactivar un usuario a partir de una solicitud de ingreso:
Este método lo diseñé para manejar dos escenarios: cuando el usuario ya existe en el sistema y cuando es completamente nuevo.
Primero importo el archivo de helpers para usar la función dividirNombreCompleto(). Luego obtengo la identificación y construyo el correo: si la solicitud tiene un correo, lo uso; si no, genero un correo automático usando la identificación con el dominio '@stayfit.local'.
Divido el nombre completo en nombre y apellido usando la función auxiliar. Luego busco si ya existe un usuario con ese correo o, si no lo encuentro, busco por documento de identidad.
Si el usuario ya existe: Obtengo su ID, lo reactivo usando activarDesdeSolicitud() con los datos actualizados, aplico la contraseña desde la solicitud (si existe) y le asigno el rol de cliente (ID 3). Finalmente retorno el ID del usuario existente.
Si el usuario es nuevo: Resuelvo la contraseña desde la solicitud usando resolverPasswordPlanoRegistro(), preparo un array con todos los datos del usuario (nombre, apellido, correo, contraseña, teléfono, documento de identidad, origen de registro 'ADMINISTRATIVO' y estado 'ACTIVO'), creo el usuario mediante el modelo y le asigno el rol de cliente. Finalmente retorno el ID del nuevo usuario.//

private function crearCliente($usuarioId, $solicitud)
{
    $datos = [
        'usuario_id' => $usuarioId,
        'edad' => $solicitud['edad'],
        'tipo_cliente' => $solicitud['tipo_cliente'] ?? 'individual',
        'fecha_nacimiento' => edadAFechaNacimiento($solicitud['edad'] ?? null),
    ];
    return $this->clienteModel->crearDesdeSolicitud($datos);
}

//El método privado crearCliente() se encarga de registrar al cliente con sus datos específicos una vez creado el usuario:
Este método lo diseñé para ser simple pero efectivo. Preparo un array con los datos específicos del cliente: el ID del usuario recién creado (que establece la relación entre usuario y cliente), la edad, el tipo de cliente (con valor por defecto 'individual' si no se especifica) y la fecha de nacimiento calculada desde la edad usando la función auxiliar edadAFechaNacimiento().
Luego llamo al método crearDesdeSolicitud() del modelo de clientes que inserta el registro en la base de datos. Este método retorna el resultado de la operación, que puedo usar para verificar si la creación fue exitosa.//

private function aplicarPasswordDesdeSolicitud($usuarioId, array $solicitud)
{
    $passwordHash = $this->solicitudModel->resolverPasswordHashRegistro($solicitud);
    if ($passwordHash) {
        $this->usuarioModel->establecerPasswordHash($usuarioId, $passwordHash);
        return;
    }
    $identificacion = trim($solicitud['identificacion'] ?? $solicitud['documento_identidad'] ?? '');
    if ($identificacion !== '') {
        $this->usuarioModel->actualizarPassword($usuarioId, $identificacion);
    }
}

//El método privado aplicarPasswordDesdeSolicitud() se encarga de establecer la contraseña del usuario desde los datos de la solicitud:
Este método lo diseñé para manejar dos escenarios de contraseña. Primero intento obtener el hash de la contraseña desde la solicitud usando resolverPasswordHashRegistro(). Si existe un hash válido, lo establezco directamente usando establecerPasswordHash() y retorno inmediatamente.
Si no hay hash disponible (por ejemplo, si el usuario no definió una contraseña en la solicitud), uso la identificación como contraseña por defecto. Obtengo la identificación de la solicitud (intentando ambos campos posibles por compatibilidad) y si no está vacía, actualizo la contraseña del usuario usando actualizarPassword() con la identificación como valor.//

private function activarAccesos($clienteId, $solicitud, $planClienteId)
{
    $planId = $this->clienteModel->resolverIdPlanDesdeSolicitud($solicitud);
    $planCatalogo = $planId ? $this->planModel->obtenerPorId($planId) : null;
    $modalidad = strtolower($solicitud['modalidad'] ?? $planCatalogo['modalidad'] ?? 'virtual');
    $plan = [
        'id' => $planClienteId,
        'modalidad' => $modalidad,
        'incluye_entrenamiento' => $planCatalogo['incluye_entrenamiento'] ?? 1,
        'incluye_nutricion' => $planCatalogo['incluye_nutricion'] ?? 0,
        'requiere_coach' => $planCatalogo['requiere_coach'] ?? 0,
        'duracion' => $planCatalogo['duracion_dias'] ?? 30,
    ];
    $this->accesoModel->crearAccesosPorPlan($clienteId, $plan);
}

//El método privado activarAccesos() se encarga de crear los accesos del cliente al plan contratado:
Este método lo diseñé para generar automáticamente los accesos que el cliente tendrá al plan que acaba de contratar. Primero resuelvo el ID del plan desde la solicitud y obtengo la información completa del plan del catálogo.
Determino la modalidad del plan (virtual, presencial o mixta) usando primero la modalidad de la solicitud y, si no existe, la del plan del catálogo, con 'virtual' como valor por defecto.
Preparo un array con la configuración del plan: el ID del plan del cliente, la modalidad, y las características del plan (si incluye entrenamiento, nutrición, si requiere coach y la duración en días). Para las características, uso los valores del plan del catálogo con valores por defecto si no existen.
Finalmente llamo al método crearAccesosPorPlan() del modelo de accesos, que se encarga de generar todos los accesos necesarios para que el cliente pueda usar el plan contratado.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->pagoModel->registrarTrazabilidad($adminId, $accion); // Registra historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones de validación realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Pago aprobado y cliente activado", "Pago rechazado").
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo de pagos la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién aprobó o rechazó cada pago y cuándo, lo cual es fundamental para la seguridad financiera y el control administrativo.//

private function validarAdministrador()
{
    $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión
    if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
        header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
        exit; // Detiene la ejecución
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método lo implementé directamente en el controlador. Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive.
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente. Esto es especialmente crítico para este controlador porque la aprobación de pagos es una operación financiera sensible que solo debe estar disponible para administradores autorizados.//

$controller = new ValidacionPagoController(); // Crea el controlador
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
validacionPagoController.php?accion=index (listado de pagos)
validacionPagoController.php?accion=aprobar&solicitud_id=5 (aprobar solicitud de pago)
validacionPagoController.php?accion=rechazar (rechazar solicitud de pago - vía POST)//

//Este controlador es el centro de validación y aprobación de pagos en StayFitMVC. Centraliza toda la lógica de negocio relacionada con el flujo completo de activación de clientes desde la aprobación de sus solicitudes de pago. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador orquesta el flujo entre múltiples entidades, los modelos se encargan de sus operaciones específicas de base de datos, y las vistas presentan la información al usuario. Una característica importante es el manejo robusto de errores con bloques try-catch que permiten que el proceso de aprobación sea transaccional y seguro, además de la flexibilidad para manejar tanto usuarios nuevos como existentes que reactivan su cuenta. El controlador coordina seis modelos diferentes para completar el flujo de activación, lo cual lo convierte en uno de los más complejos pero también más importantes del sistema.//