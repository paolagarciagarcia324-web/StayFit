Controlador encargado de gestionar el flujo de pagos dentro de la plataforma StayFitMVC, permitiendo su consulta, aprobación, rechazo con observaciones y visualización de comprobantes.

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa el modelo de pagos
require_once __DIR__ . '/../../models/pago/comprobanteModel.php'; // Importa comprobantes

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Posteriormente cargo los modelos pagoModel.php y comprobanteModel.php que gestionan la información de los pagos y sus respectivos comprobantes de pago. Estas dependencias me permiten acceder a la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión financiera de los clientes.//

class PagoController
{
    private $pagoModel; // Modelo de pagos
    private $comprobanteModel; // Modelo de comprobantes

//En este bloque declaro la clase PagoController y sus propiedades privadas:
Declaro las propiedades $pagoModel y $comprobanteModel que almacenarán las instancias de los modelos. Gracias a ellas puedo acceder a las funciones encargadas de consultar, aprobar y rechazar pagos, así como manejar los comprobantes adjuntos, sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->pagoModel = new PagoModel(); // Instancia pagos
    $this->comprobanteModel = new ComprobanteModel(); // Instancia comprobantes
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades financieras. Finalmente, instancio los modelos de pagos y comprobantes, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador.//

public function index()
{
    $pagos = $this->pagoModel->obtenerTodos(); // Obtiene todos los pagos
    $pendientes = $this->pagoModel->obtenerPendientes(); // Obtiene pagos pendientes
    require_once __DIR__ . '/../../views/admin/pagos.php'; // Carga la vista
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de pagos:
Utilizo el modelo para obtener todos los pagos registrados en la base de datos mediante el método obtenerTodos(). Adicionalmente, obtengo específicamente los pagos pendientes mediante obtenerPendientes(), lo cual me permite mostrar al administrador un resumen rápido de las acciones que requieren su atención inmediata. Posteriormente cargo la vista pagos.php que renderizará esta información.//

public function detalle()
{
    $id = $_GET['id'] ?? null; // Obtiene ID del pago
    if (!$id) { // Valida ID
        header('Location: pagoController.php'); // Redirige al listado
        exit; // Detiene ejecución
    }
    $pago = $this->pagoModel->obtenerPorId($id); // Obtiene pago
    $comprobante = $this->comprobanteModel->obtenerPorPago($id); // Obtiene comprobante
    require_once __DIR__ . '/../../views/admin/pagos.php'; // Carga vista
}

//El método detalle() proporciona una vista completa de un pago específico y su comprobante adjunto:
Este método lo diseñé para mostrar toda la información relevante de una transacción. Primero obtengo el ID del pago desde los parámetros GET y valido que exista. Si no hay ID, redirijo inmediatamente al listado para evitar errores.
Luego realizo dos consultas al modelo: obtengo los datos detallados del pago mediante obtenerPorId() y busco el comprobante de pago asociado mediante obtenerPorPago(). Toda esta información se pone a disposición de la vista pagos.php que renderizará una ficha detallada de la transacción y mostrará la imagen o archivo del comprobante si existe.//

public function aprobar()
{
    if (isset($_GET['id'])) { // Verifica ID recibido
        $id = $_GET['id']; // ID del pago
        $this->pagoModel->aprobar($id, $_SESSION['usuario_id'] ?? null); // Aprueba pago
        $this->registrarTrazabilidad('Pago aprobado'); // Guarda trazabilidad
    }
    header('Location: pagoController.php'); // Redirige al panel
    exit; // Detiene ejecución
}

//El método aprobar() me permite confirmar un pago pendiente de forma rápida:
Recibo mediante GET el ID del pago que debe ser aprobado. Verifico que el parámetro exista antes de proceder.
Llamo al método aprobar() del modelo, pasándole el ID del pago y el ID del administrador actual desde la sesión para registrar quién realizó la aprobación. Esto es importante para la auditoría financiera. Luego registro la acción en la trazabilidad y redirijo al listado. Este método es muy útil para aprobar pagos de forma ágil desde el panel principal.//

public function rechazar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica formulario
        $datos = [
            'id' => $_POST['id'], // ID del pago
            'observacion' => trim($_POST['observacion'] ?? '') // Motivo del rechazo
        ];
        $this->pagoModel->rechazar($datos); // Rechaza pago
        $this->registrarTrazabilidad('Pago rechazado'); // Guarda trazabilidad
    }
    header('Location: pagoController.php'); // Redirige al panel
    exit; // Detiene ejecución
}

//El método rechazar() me permite denegar un pago, requiriendo obligatoriamente una observación o motivo:
A diferencia del método de aprobación, este método lo diseñé para recibir los datos mediante POST, ya que necesito capturar el motivo del rechazo. Verifico que la solicitud sea POST y recopilo el ID del pago y la observación ingresada por el administrador.
Llamo al método rechazar() del modelo pasando estos datos. El modelo se encargará de actualizar el estado del pago y guardar la observación para que el cliente pueda ver por qué fue rechazado su pago. Registro la acción en la trazabilidad y redirijo al listado. Exigir un motivo es una buena práctica administrativa para mantener la transparencia con los clientes.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->pagoModel->registrarTrazabilidad($adminId, $accion); // Registra historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones financieras realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Pago aprobado", "Pago rechazado").
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién aprobó o rechazó cada pago y cuándo, lo cual es fundamental para la seguridad financiera.//

private function validarAdministrador()
{
    validarAccesoAdministrador(); // Valida sesión admin
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas.//

$controller = new PagoController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Acción por defecto
if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga principal
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente. Si no existe, cargo la vista principal como medida de seguridad. Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
pagoController.php?accion=index (listado de pagos)
pagoController.php?accion=detalle&id=5 (ver detalle y comprobante)
pagoController.php?accion=aprobar&id=5 (aprobar pago)
pagoController.php?accion=rechazar (rechazar pago - vía POST)//

//Este controlador es el corazón de la gestión financiera en StayFitMVC. Centraliza toda la lógica de negocio relacionada con pagos, desde su consulta y visualización de comprobantes, hasta la aprobación y el rechazo con observaciones. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, los modelos se encargan de la base de datos, y las vistas presentan la información al usuario. Una característica importante es la diferencia de diseño entre aprobar (vía GET para agilidad) y rechazar (vía POST para exigir un motivo).//