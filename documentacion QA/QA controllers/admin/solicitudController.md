Controlador encargado de gestionar las solicitudes de ingreso y compra dentro de la plataforma StayFitMVC, permitiendo su revisión, aprobación, rechazo y seguimiento.

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/solicitud/solicitudIngresoModel.php'; // Importa el modelo de solicitudes

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Posteriormente cargo el modelo solicitudIngresoModel.php que gestiona las solicitudes de ingreso y compra en la base de datos. Este modelo encapsula toda la lógica de consulta, actualización de estado y trazabilidad de las solicitudes, manteniendo el controlador limpio y enfocado solo en el flujo de la aplicación.//

class SolicitudController
{
    private $solicitudModel; // Modelo de solicitudes

//En este bloque declaro la clase SolicitudController y su propiedad privada:
Declaro la propiedad $solicitudModel que almacenará la instancia del modelo de solicitudes. Gracias a esta propiedad puedo acceder a las funciones encargadas de consultar, revisar y aprobar o rechazar solicitudes sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y el modelo siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->solicitudModel = new SolicitudIngresoModel(); // Instancia el modelo
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades.
Finalmente, instancio el modelo de solicitudes, dejándolo listo para ser utilizado en cualquiera de los métodos del controlador. Al crear la instancia en el constructor, mantengo el código organizado y evito crear objetos repetidamente en cada método.//

public function index()
{
    $solicitudes = $this->solicitudModel->obtenerTodas(); // Obtiene todas las solicitudes
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    require_once __DIR__ . '/../../views/admin/solicitudes.php'; // Carga la vista
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de solicitudes:
Utilizo el modelo para obtener todas las solicitudes registradas en la base de datos mediante el método obtenerTodas(). Los datos se almacenan en la variable $solicitudes que estará disponible en la vista.
Adicionalmente, recupero cualquier mensaje flash almacenado en la sesión (de acciones anteriores como aprobaciones o rechazos) y lo limpio inmediatamente con unset() para que no se muestre dos veces. Posteriormente cargo la vista solicitudes.php que renderizará esta información.//

public function pendientes()
{
    $solicitudes = $this->solicitudModel->obtenerPorEstado('pendiente'); // Obtiene solicitudes pendientes
    require_once __DIR__ . '/../../views/admin/solicitudes.php'; // Carga la vista
}

//El método pendientes() me permite filtrar y mostrar únicamente las solicitudes que requieren atención inmediata:
En lugar de cargar todas las solicitudes, utilizo el método obtenerPorEstado('pendiente') del modelo para obtener solo aquellas que están esperando revisión. Luego cargo la misma vista solicitudes.php. Esta modularidad me permite tener pestañas o vistas rápidas para el administrador sin complicar la lógica del controlador.//

public function detalle()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        header('Location: solicitudController.php');
        exit;
    }
    $solicitudes = $this->solicitudModel->obtenerTodas();
    $solicitud = $this->solicitudModel->obtenerPorId($id);
    $abrirModal = true;
    require_once __DIR__ . '/../../views/admin/solicitudes.php';
}

//El método detalle() proporciona una vista completa de una solicitud específica y prepara la interfaz para mostrar un modal:
Este método lo diseñé para manejar la visualización detallada sin salir del listado principal. Primero obtengo el ID de la solicitud desde los parámetros GET y valido que exista. Si no hay ID, redirijo inmediatamente al listado para evitar errores.
Luego obtengo todas las solicitudes (para mantener el listado de fondo) y la solicitud específica mediante obtenerPorId(). La clave aquí es la variable $abrirModal = true, que le indica a la vista solicitudes.php que debe renderizar automáticamente un modal con los detalles de la solicitud al cargar la página. Esto mejora la experiencia de usuario al no requerir clics adicionales.//

public function detalleFragment()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(404);
        exit('Solicitud no encontrada.');
    }
    $solicitud = $this->solicitudModel->obtenerPorId($id);
    if (!$solicitud) {
        http_response_code(404);
        exit('Solicitud no encontrada.');
    }
    require __DIR__ . '/../../views/admin/partials/solicitudDetalleContenido.php';
    exit;
}

//El método detalleFragment() está diseñado específicamente para cargar contenido dinámico mediante AJAX:
Este método lo creé para soportar peticiones asíncronas desde el frontend. Recibo el ID de la solicitud y valido su existencia. Si no hay ID o la solicitud no existe en la base de datos, establezco un código de respuesta HTTP 404 (Not Found) y detengo la ejecución con un mensaje de error.
Si la solicitud existe, cargo únicamente el archivo parcial solicitudDetalleContenido.php ubicado en views/admin/partials/. Este archivo contiene solo el HTML interno del modal, sin cabeceras ni pies de página. Finalmente uso exit para detener la ejecución inmediatamente, asegurando que solo se devuelva el fragmento de HTML limpio para ser inyectado en el DOM por JavaScript.//

public function marcarRevision()
{
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        try {
            $this->solicitudModel->marcarRevision($id, $_SESSION['usuario_id'] ?? null);
            $this->registrarTrazabilidad('Solicitud marcada en revisión');
            $this->flash('success', 'Solicitud marcada en revisión.');
        } catch (Throwable $e) {
            $this->flash('error', 'No se pudo marcar en revisión: ' . $e->getMessage());
        }
    }
    header('Location: solicitudController.php');
    exit;
}

//El método marcarRevision() me permite cambiar el estado de una solicitud a "en revisión" de forma segura:
Recibo el ID de la solicitud mediante GET. Lo más destacable de este método es que utilicé un bloque try-catch para manejar posibles errores de base de datos o lógica interna.
Dentro del bloque try, llamo al método marcarRevision() del modelo, pasando el ID y el ID del administrador actual desde la sesión. Luego registro la acción en la trazabilidad y establezco un mensaje flash de éxito.
Si ocurre algún error (capturado por Throwable $e), establezco un mensaje flash de error que incluye el mensaje de la excepción para facilitar la depuración. Finalmente redirijo al listado principal. Esta robustez es importante porque cambiar el estado de una solicitud es una acción crítica en el flujo de negocio.//

public function rechazar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'id' => $_POST['id'], // ID de la solicitud
            'estado' => 'rechazada', // Estado final
            'observacion' => trim($_POST['observacion']) // Motivo del rechazo
        ];
        $this->solicitudModel->rechazar($datos); // Rechaza la solicitud
        $this->registrarTrazabilidad('Solicitud rechazada'); // Guarda trazabilidad
    }
    header('Location: solicitudController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método rechazar() me permite denegar una solicitud, requiriendo obligatoriamente una observación o motivo:
Verifico que la solicitud sea POST, ya que es una acción que modifica datos y necesito capturar el motivo del rechazo. Recopilo el ID de la solicitud, establezco el estado final como 'rechazada' y obtengo la observación ingresada por el administrador.
Llamo al método rechazar() del modelo pasando estos datos. El modelo se encargará de actualizar el estado de la solicitud y guardar la observación para que el solicitante pueda ver por qué fue rechazada. Registro la acción en la trazabilidad y redirijo al listado. Exigir un motivo es una buena práctica administrativa para mantener la transparencia con los usuarios.//

private function flash($tipo, $mensaje)
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

//El método privado flash() es una herramienta interna que me permite almacenar mensajes temporales en la sesión:
Este método lo creé para no repetir código en cada función del controlador. Recibe dos parámetros: el tipo de mensaje (success, error, warning) y el mensaje en sí. Almacena ambos en la variable de sesión $_SESSION['flash'] que luego la vista puede leer y mostrar al usuario.
La ventaja de usar este sistema es que los mensajes persisten a través de redirecciones, permitiendo mostrar feedback al usuario después de una acción. Una vez que la vista muestra el mensaje, normalmente se limpia la variable de sesión.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->solicitudModel->registrarTrazabilidad($adminId, $accion); // Registra historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Solicitud marcada en revisión", "Solicitud rechazada").
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién revisó o rechazó cada solicitud y cuándo, lo cual es fundamental para la seguridad y el control administrativo.//

private function validarAdministrador()
{
    validarAccesoAdministrador(); // Valida sesión admin
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas.//

$controller = new SolicitudController(); // Crea el controlador
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
solicitudController.php?accion=index (listado completo)
solicitudController.php?accion=pendientes (solo pendientes)
solicitudController.php?accion=detalle&id=5 (ver detalle con modal)
solicitudController.php?accion=detalleFragment&id=5 (carga AJAX del detalle)
solicitudController.php?accion=marcarRevision&id=5 (marcar en revisión)
solicitudController.php?accion=rechazar (rechazar solicitud - vía POST)//

//Este controlador es el centro de gestión de solicitudes de ingreso y compra en StayFitMVC. Centraliza toda la lógica de negocio relacionada con el flujo de aprobación de usuarios, desde la visualización y filtrado, hasta la revisión detallada y el rechazo con observaciones. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, el modelo se encarga de la base de datos, y las vistas presentan la información al usuario. Una característica importante es el soporte para carga dinámica mediante el método detalleFragment(), que permite una experiencia de usuario más fluida sin recargar toda la página, y el manejo robusto de errores con try-catch en acciones críticas como marcarRevision().//
