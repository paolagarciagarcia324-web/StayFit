Controlador encargado de consolidar y presentar las estadísticas clave del negocio en la plataforma StayFitMVC, incluyendo métricas de clientes, pagos, planes y progreso virtual.

//Este archivo estadisticasController.php corresponde al controlador encargado de gestionar las estadísticas y reportes dentro de la plataforma StayFitMVC. Su función principal es consolidar métricas clave del negocio desde múltiples modelos (clientes, pagos, planes y progreso de videos) para presentar una visión analítica del estado de la plataforma. A diferencia del dashboard que muestra conteos rápidos, este controlador se enfoca en estadísticas más específicas como clientes por modalidad, avance promedio en contenido virtual y planes vendidos. Dentro de la arquitectura MVC, este controlador actúa como un agregador de información, delegando las consultas a los respectivos modelos y presentando los resultados en la vista de reportes.//

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/cliente/clienteModel.php'; // Importa clientes
require_once __DIR__ . '/../../models/pago/pagoModel.php'; // Importa pagos
require_once __DIR__ . '/../../models/plan/planModel.php'; // Importa planes
require_once __DIR__ . '/../../models/contenidoVirtual/progresoVideoModel.php'; // Importa progreso de videos

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Luego cargo los cuatro modelos que necesito para obtener las estadísticas: clienteModel.php para métricas de clientes (activos y por modalidad), pagoModel.php para conteo de pagos aprobados, planModel.php para planes vendidos, y progresoVideoModel.php para el promedio de avance en contenido virtual. Cada modelo me proporciona métodos específicos de conteo que encapsulan la lógica de consulta, manteniendo el controlador limpio y enfocado solo en la presentación de datos.//

class EstadisticasController
{
    private $clienteModel; // Modelo de clientes
    private $pagoModel; // Modelo de pagos
    private $planModel; // Modelo de planes
    private $progresoVideoModel; // Modelo de progreso virtual

//En este bloque declaro la clase EstadisticasController y sus propiedades privadas:
Declaro las cuatro propiedades que almacenarán las instancias de los modelos necesarios. Cada una representa una fuente de datos diferente para las estadísticas. Gracias a estas propiedades puedo acceder a los métodos de conteo de cada modelo sin necesidad de crear nuevas instancias en cada método. Esta clase funciona como un punto de consolidación de información estadística, siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->clienteModel = new ClienteModel(); // Instancia clientes
    $this->pagoModel = new PagoModel(); // Instancia pagos
    $this->planModel = new PlanModel(); // Instancia planes
    $this->progresoVideoModel = new ProgresoVideoModel(); // Instancia progreso videos
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas estadísticas sensibles del negocio.
Finalmente, instancio los cuatro modelos que necesito, dejándolos listos para ser utilizados. Al crear todas las instancias en el constructor, mantengo el código organizado y evito crear objetos repetidamente. Esto es especialmente importante aquí porque las estadísticas requieren consultar múltiples fuentes de datos simultáneamente.//

public function index()
{
    $estadisticas = [
        'clientesActivos' => $this->clienteModel->contarActivos(), // Total clientes activos
        'pagosAprobados' => $this->pagoModel->contarAprobados(), // Total pagos aprobados
        'planesVendidos' => $this->planModel->contarVendidos(), // Total planes vendidos
        'clientesVirtuales' => $this->clienteModel->contarPorModalidad('virtual'), // Clientes virtuales
        'avanceVirtual' => $this->progresoVideoModel->promedioAvance() // Promedio avance virtual
    ];
    require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista existente
}

//El método index() es el punto de entrada principal del controlador y se encarga de consolidar todas las estadísticas:
Este método lo diseñé para ser simple pero poderoso. Construyo un array $estadisticas que contiene cinco métricas clave del negocio, cada una obtenida de un método específico del modelo correspondiente:
clientesActivos: Total de clientes con estado activo, obtenido mediante contarActivos() del modelo de clientes.
pagosAprobados: Total de pagos que han sido aprobados, obtenido mediante contarAprobados() del modelo de pagos.
planesVendidos: Total de planes que han sido vendidos, obtenido mediante contarVendidos() del modelo de planes.
clientesVirtuales: Cantidad de clientes que usan la modalidad virtual, obtenido mediante contarPorModalidad('virtual') del modelo de clientes. Este método me permite filtrar por modalidad de forma flexible.
avanceVirtual: Promedio de avance en el contenido virtual de todos los clientes, obtenido mediante promedioAvance() del modelo de progreso de videos. Esta métrica me indica qué tan comprometidos están los clientes con el contenido.
Una vez construido el array, cargo la vista reportes.php que se encargará de presentar estas estadísticas de forma visual al administrador. Es importante notar que reutilizo la vista de reportes, lo cual me permite mantener consistencia en la presentación de datos analíticos.//

private function validarAdministrador()
{
    $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión
    if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
        header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
        exit; // Detiene ejecución
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para las estadísticas:
Este método lo implementé directamente en el controlador (a diferencia de otros que usan la función externa validarAccesoAdministrador()). Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive.
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad con diferentes versiones del sistema). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución con exit.
Esta validación es crucial porque las estadísticas contienen información sensible del negocio que solo debe ser accesible para administradores. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas.//

$controller = new EstadisticasController(); // Crea el controlador
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
estadisticasController.php?accion=index (estadísticas principales)
Aunque actualmente solo tengo el método index(), esta estructura me permite agregar fácilmente más métodos en el futuro (como estadísticas por período, por coach, etc.) sin cambiar la lógica de enrutamiento.//

//Este controlador es el centro de análisis estadístico en StayFitMVC. Centraliza la consulta de métricas clave del negocio desde múltiples modelos, presentando una visión analítica del estado de la plataforma. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: cada modelo se encarga de sus propios conteos y cálculos, mientras el controlador solo consolida y presenta los datos. Las cinco métricas presentadas (clientes activos, pagos aprobados, planes vendidos, clientes virtuales y avance virtual) proporcionan al administrador información valiosa para la toma de decisiones sobre el negocio. La validación de administrador en el constructor garantiza que solo usuarios autorizados puedan acceder a estas estadísticas sensibles.//
