Controlador encargado de consolidar y presentar los reportes generales del negocio en la plataforma StayFitMVC, agrupando información de clientes, pagos, planes y progreso de entrenamiento.

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/progreso/progresoModel.php'; // Importa progreso

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Posteriormente cargo los cuatro modelos que necesito para generar los diferentes reportes: clienteModel.php para métricas de clientes, pagoModel.php para el estado financiero, planModel.php para la oferta comercial, y progresoModel.php para el seguimiento del entrenamiento. Cada modelo me proporciona un método reporteGeneral() que encapsula la lógica de consulta, manteniendo el controlador limpio y enfocado solo en la orquestación de datos.//

class ReporteController
{
    private $clienteModel; // Modelo de clientes
    private $pagoModel; // Modelo de pagos
    private $planModel; // Modelo de planes
    private $progresoModel; // Modelo de progreso

//En este bloque declaro la clase ReporteController y sus propiedades privadas:
Declaro las cuatro propiedades que almacenarán las instancias de los modelos necesarios. Cada una representa una fuente de datos diferente para los reportes. Gracias a estas propiedades puedo acceder a los métodos de reporte de cada modelo sin necesidad de crear nuevas instancias en cada método. Esta clase funciona como un punto de consolidación de información analítica, siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->clienteModel = new ClienteModel(); // Instancia clientes
    $this->pagoModel = new PagoModel(); // Instancia pagos
    $this->planModel = new PlanModel(); // Instancia planes
    $this->progresoModel = new ProgresoModel(); // Instancia progreso
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estos reportes sensibles del negocio.
Finalmente, instancio los cuatro modelos que necesito, dejándolos listos para ser utilizados. Al crear todas las instancias en el constructor, mantengo el código organizado y evito crear objetos repetidamente. Esto es especialmente importante aquí porque los reportes requieren consultar múltiples fuentes de datos simultáneamente.//

public function index()
{
    $reporteClientes = $this->clienteModel->reporteGeneral(); // Reporte de clientes
    $reportePagos = $this->pagoModel->reporteGeneral(); // Reporte de pagos
    $reportePlanes = $this->planModel->reporteGeneral(); // Reporte de planes
    $reporteProgreso = $this->progresoModel->reporteGeneral(); // Reporte de progreso
    require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga la vista
}

//El método index() es el punto de entrada principal del controlador y se encarga de consolidar todos los reportes generales:
Este método lo diseñé para ser un dashboard analítico completo. Construyo cuatro variables distintas, cada una obtenida llamando al método reporteGeneral() del modelo correspondiente. Esto me permite tener datos detallados de clientes, pagos, planes y progreso en un solo lugar.
Una vez obtenidos los datos, cargo la vista reportes.php que se encargará de presentar esta información de forma visual al administrador. Es importante notar que esta vista es bastante flexible y puede renderizar diferentes secciones según las variables que le pasemos.//

public function pagos()
{
    $reportePagos = $this->pagoModel->reporteGeneral(); // Obtiene reporte de pagos
    require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista
}

//El método pagos() me permite mostrar una vista enfocada exclusivamente en el reporte financiero:
En lugar de cargar todos los reportes como en el index(), aquí solo obtengo la información de pagos mediante reporteGeneral() del modelo de pagos. Luego cargo la misma vista reportes.php. La vista se encargará de detectar qué variables están disponibles y mostrará únicamente la sección de pagos, lo cual me permite reutilizar código de vista sin duplicar archivos.//

public function clientes()
{
    $reporteClientes = $this->clienteModel->reporteGeneral(); // Obtiene reporte de clientes
    require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista
}

//El método clientes() me permite mostrar una vista enfocada exclusivamente en el reporte de clientes:
Sigo el mismo patrón que en el método anterior. Obtengo solo la información de clientes mediante el modelo correspondiente y cargo la vista reportes.php. Esta modularidad me permite tener pestañas o secciones específicas dentro del módulo de reportes sin complicar la lógica del controlador.//

public function progreso()
{
    $reporteProgreso = $this->progresoModel->reporteGeneral(); // Obtiene reporte de progreso
    require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista
}

//El método progreso() me permite mostrar una vista enfocada exclusivamente en el reporte de progreso de entrenamiento:
Obtengo la información de progreso mediante reporteGeneral() del modelo de progreso y cargo la vista reportes.php. Esto le permite al administrador analizar qué tan comprometidos están los clientes con sus rutinas y programas virtuales de forma aislada.//

private function validarAdministrador()
{
    $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión
    if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
        header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
        exit; // Detiene ejecución
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para los reportes:
Este método lo implementé directamente en el controlador. Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive.
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas, ya que los reportes contienen información sensible del negocio.//

$controller = new ReporteController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Acción por defecto
if (method_exists($controller, $accion)) { // Verifica acción
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga principal
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
reporteController.php?accion=index (reporte general completo)
reporteController.php?accion=pagos (reporte financiero)
reporteController.php?accion=clientes (reporte de clientes)
reporteController.php?accion=progreso (reporte de entrenamiento)//

//Este controlador es el centro de análisis y reportes en StayFitMVC. Centraliza la consulta de información detallada desde múltiples modelos, presentando una visión analítica del estado del negocio. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: cada modelo se encarga de sus propios reportes, mientras el controlador solo orquesta y presenta los datos. Una característica importante es la reutilización inteligente de la vista reportes.php, lo que me permite tener diferentes secciones de reportes sin duplicar código de presentación.//
