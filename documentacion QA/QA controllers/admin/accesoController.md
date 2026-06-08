El archivo accesoController.php corresponde al controlador encargado de administrar los accesos y permisos de los clientes dentro de la plataforma StayFitMVC. Su función principal es actuar como intermediario entre la vista y el modelo, gestionando las solicitudes relacionadas con la activación, bloqueo, vencimiento y actualización de módulos de acceso. Además, implementa mecanismos de seguridad para garantizar que únicamente los usuarios con privilegios de administrador puedan ejecutar estas acciones. Dentro de la arquitectura MVC, este controlador recibe las solicitudes del usuario, procesa la lógica necesaria y delega las operaciones de datos al modelo AccesoModel.

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../models/plan/accesoModel.php';

//En este bloque incluyo los archivos necesarios para el funcionamiento del controlador. Primero importo roles.php, el cual contiene las funciones relacionadas con la validación de permisos y roles del sistema. Posteriormente incluyo accesoModel.php, que corresponde al modelo encargado de interactuar con la base de datos para gestionar los accesos de los clientes. Estas dependencias son fundamentales para que el controlador pueda aplicar reglas de seguridad y realizar operaciones sobre la información almacenada.//

class AccesoController
{

//En este bloque declaro la clase AccesoController, la cual centraliza todas las operaciones relacionadas con la administración de accesos dentro del sistema. Esta clase sigue el patrón MVC, actuando como intermediaria entre las vistas y el modelo encargado de gestionar la información.//

private $accesoModel;

//Esta propiedad almacena una instancia del modelo AccesoModel. Gracias a esta referencia puedo reutilizar los métodos del modelo desde cualquier función del controlador, evitando crear múltiples instancias y manteniendo una estructura más organizada.//

public function __construct()

//El constructor se ejecuta automáticamente cada vez que se crea una instancia del controlador. Su función es preparar el entorno de trabajo necesario para la ejecución de las acciones disponibles dentro del módulo de accesos.//

session_start();

//En esta línea inicio o recupero la sesión activa del usuario. Esto me permite acceder a la información almacenada en variables de sesión, como el identificador del usuario autenticado y su rol dentro del sistema.//

$this->validarAdministrador();

//Antes de permitir cualquier operación relacionada con la administración de accesos, ejecuto una validación de permisos. De esta manera garantizo que únicamente los administradores puedan ingresar a este módulo y realizar modificaciones sobre los accesos de los clientes.//

$this->accesoModel = new AccesoModel();

//Finalmente creo una instancia del modelo AccesoModel, que será utilizada para consultar, actualizar y registrar información relacionada con los accesos dentro de la base de datos.//

public function index()

//Este método representa la acción principal del controlador. Su función es cargar la información necesaria para mostrar la vista administrativa relacionada con los accesos de los clientes.//

$accesos = $this->accesoModel->obtenerTodos();

//En esta línea solicito al modelo la lista completa de accesos registrados en el sistema. La información obtenida será utilizada posteriormente por la vista para mostrar los datos al administrador.//

require_once __DIR__ . '/../../views/admin/pagos.php';

//Una vez obtenidos los datos, cargo la vista encargada de presentar la información al usuario administrador. Esta vista se encuentra ubicada dentro del módulo administrativo y muestra los registros gestionados por el controlador.//

Ruta utilizada
views/
└── admin/
    └── pagos.php

public function activar()

//Este método permite habilitar nuevamente el acceso de un cliente dentro de la plataforma. Su utilización es común cuando un usuario recupera permisos después de haber sido bloqueado o suspendido.//

if (isset($_GET['id']))

//Antes de realizar cualquier modificación verifico que exista un identificador válido recibido mediante la URL. Este valor corresponde al acceso que será actualizado.//

$id = $_GET['id'];

//Almaceno el identificador recibido para utilizarlo posteriormente durante la actualización del estado.//

$this->accesoModel->cambiarEstado($id, 'activo');

//Solicito al modelo que modifique el estado del acceso seleccionado, asignándole el valor activo. Esto permite que el cliente vuelva a utilizar los servicios habilitados dentro del sistema.//

$this->registrarTrazabilidad('Acceso activado');

//Después de realizar el cambio registro una entrada en el historial de acciones administrativas. Esto facilita el seguimiento de las actividades realizadas dentro del sistema.//

header('Location: accesoController.php');
exit;

//Una vez finalizado el proceso redirecciono al usuario nuevamente al controlador principal y detengo la ejecución del script para evitar acciones adicionales.//

public function bloquear()

//Este método permite suspender el acceso de un cliente dentro de la plataforma. Se utiliza cuando es necesario restringir temporalmente el uso de los servicios.//

$this->accesoModel->cambiarEstado($id, 'bloqueado');

//Solicito al modelo actualizar el estado del acceso a bloqueado, impidiendo que el usuario continúe utilizando los módulos asociados.//

$this->registrarTrazabilidad('Acceso bloqueado');

//Registro la acción realizada para mantener un historial de las decisiones administrativas ejecutadas dentro del sistema.//

public function vencer()

//Este método se encarga de marcar un acceso como vencido. Generalmente se utiliza cuando un plan o suscripción ha alcanzado su fecha de expiración.//

$this->accesoModel->cambiarEstado($id, 'vencido');

//Actualizo el estado del acceso a vencido, indicando que el usuario ya no posee permisos activos para utilizar los servicios correspondientes.//

$this->registrarTrazabilidad('Acceso vencido');

//Registro el evento en el historial administrativo para conservar evidencia de la acción ejecutada.//

public function actualizarModulos()

//Este método permite administrar los módulos específicos a los que puede acceder un cliente. Gracias a esta funcionalidad es posible habilitar o deshabilitar características de forma individual.//

if ($_SERVER['REQUEST_METHOD'] === 'POST')

//Antes de procesar la información verifico que la solicitud haya sido enviada mediante el método POST, garantizando que los datos provienen de un formulario válido.//

$datos = [

//En este bloque recopilo toda la información enviada desde el formulario para construir un arreglo que posteriormente será enviado al modelo.//

'cliente_id' => $_POST['cliente_id']

//Almaceno el identificador del cliente cuyos permisos serán modificados.//

'entrenamiento' => isset($_POST['entrenamiento']) ? 1 : 0
'nutricion' => isset($_POST['nutricion']) ? 1 : 0
'contenido_virtual' => isset($_POST['contenido_virtual']) ? 1 : 0
'sesiones' => isset($_POST['sesiones']) ? 1 : 0
'acompanamiento' => isset($_POST['acompanamiento']) ? 1 : 0

//Para cada módulo verifico si fue seleccionado dentro del formulario. Si el módulo se encuentra marcado asigno el valor 1, indicando que el acceso está habilitado; de lo contrario asigno 0, representando un acceso desactivado.//

$this->accesoModel->actualizarModulos($datos);

//Una vez recopilados los datos, envío la información al modelo para que actualice los permisos correspondientes dentro de la base de datos.//

$this->registrarTrazabilidad('Módulos de acceso actualizados');

//Registro la modificación realizada para mantener trazabilidad sobre los cambios efectuados por los administradores.//

private function registrarTrazabilidad($accion)

//Este método privado se encarga de almacenar en el historial administrativo todas las acciones importantes ejecutadas desde el controlador.//

$adminId = $_SESSION['usuario_id'] ?? null;

//Obtengo el identificador del administrador autenticado para asociar la acción realizada con el usuario responsable.//

$this->accesoModel->registrarTrazabilidad($adminId, $accion);

//Envío al modelo la información necesaria para almacenar el evento dentro de la bitácora o historial de acciones.//

private function validarAdministrador()

//Este método constituye una medida de seguridad que protege todas las funcionalidades del controlador. Su propósito es garantizar que únicamente usuarios con privilegios administrativos puedan acceder al módulo.//

$rol = strtolower($_SESSION['rol'] ?? '');

//Recupero el rol almacenado en la sesión y lo convierto a minúsculas para evitar inconsistencias durante la comparación.//

if ($rol !== 'admin' && $rol !== 'administrador')

//Verifico que el usuario posea alguno de los roles autorizados. Si no cumple esta condición, considero que no tiene permisos suficientes para acceder al módulo.//

header('Location: ../../views/auth/accesoDenegado.php');
exit;

//Cuando el usuario no posee permisos administrativos, lo redirecciono a la vista de acceso denegado y detengo la ejecución del script para impedir cualquier acceso no autorizado.//

$controller = new AccesoController();

//En esta línea creo una instancia del controlador para que pueda comenzar a procesar las solicitudes recibidas desde el navegador.//

$accion = $_GET['accion'] ?? 'index';

//Obtengo la acción solicitada mediante el parámetro accion enviado por la URL. Si no se especifica ninguna acción, utilizo index como opción predeterminada.//

if (method_exists($controller, $accion))

//Verifico que el método solicitado realmente exista dentro del controlador antes de ejecutarlo. Esta validación evita errores ocasionados por llamadas a métodos inexistentes.//

$controller->$accion();

//Si la acción existe, la ejecuto dinámicamente permitiendo que el controlador responda a diferentes solicitudes utilizando un único punto de entrada.//

$controller->index();

//Si la acción solicitada no existe, cargo automáticamente el método index() para mostrar la vista principal del módulo.


Dentro de la arquitectura MVC de StayFitMVC, el archivo accesoController.php cumple el rol de controlador. Su responsabilidad consiste en recibir solicitudes, validar permisos, procesar acciones administrativas y comunicarse con el modelo AccesoModel para manipular los datos. Posteriormente envía la información necesaria a las vistas para que sea presentada al usuario. Gracias a esta separación de responsabilidades, se mantiene una estructura organizada, escalable y fácil de mantener dentro del proyecto.//