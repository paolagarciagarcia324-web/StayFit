Controlador encargado de mostrar el panel principal (dashboard) administrativo de la plataforma StayFitMVC, presentando estadísticas clave y métricas importantes para la toma de decisiones.

//Este archivo dashboardController.php corresponde al controlador encargado de gestionar el panel principal administrativo de la plataforma StayFitMVC. Su función principal es consolidar y presentar estadísticas clave del sistema: clientes activos, solicitudes pendientes, pagos pendientes, planes virtuales activos y accesos vencidos. A diferencia de otros controladores que gestionan entidades específicas, este se enfoca en agregar información de múltiples fuentes para dar una visión general del estado del negocio. Dentro de la arquitectura MVC, este controlador consulta directamente la base de datos (sin pasar por modelos específicos) para obtener conteos rápidos, lo cual es una decisión de diseño válida para un dashboard donde la performance es importante.//

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/roles.php';

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo database.php me proporciona la clase Database que necesito para conectarme directamente a la base de datos y ejecutar consultas SQL personalizadas. El archivo roles.php me permite validar permisos administrativos. A diferencia de otros controladores que importan modelos específicos, aquí importo directamente la conexión a la base de datos porque el dashboard necesita hacer consultas de conteo que no están encapsuladas en modelos individuales.//

class DashboardController
{
    private $db;

//En este bloque declaro la clase DashboardController y su propiedad privada:
Declaro la propiedad $db que almacenará la conexión a la base de datos. Esta conexión la usaré en todos los métodos privados de conteo para ejecutar las consultas SQL directamente. Mantener la conexión como propiedad privada me permite reutilizarla sin tener que crear nuevas conexiones en cada método.//

public function __construct()
{
    session_start();
    $database = new Database();
    $this->db = $database->conectar();
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar la conexión a la base de datos:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego creo una instancia de la clase Database y llamo al método conectar() para obtener la conexión PDO. Esta conexión la almaceno en la propiedad $db para usarla en todos los métodos de conteo.
Es importante notar que aquí no valido el acceso de administrador en el constructor, sino que lo hago en el método index() directamente. Esto es una decisión de diseño que me permite tener más control sobre cuándo se realiza la validación.//

public function index()
{
    $this->validarAdministrador();
    $datos = [
        'clientesActivos' => $this->contarClientesActivos(),
        'solicitudesPendientes' => $this->contarSolicitudesPendientes(),
        'pagosPendientes' => $this->contarPagosPendientes(),
        'planesVirtuales' => $this->contarPlanesVirtuales(),
        'accesosVencidos' => $this->contarAccesosVencidos()
    ];
    require_once __DIR__ . '/../../views/admin/dashboard.php';
}

//El método index() es el punto de entrada principal del controlador y se encarga de consolidar todas las estadísticas del dashboard:
Primero valido que el usuario sea administrador llamando al método privado validarAdministrador(). Si no tiene permisos, será redirigido automáticamente.
Luego construyo un array $datos que contiene todas las métricas clave del dashboard. Cada métrica se obtiene llamando a un método privado específico que se encarga de ejecutar la consulta SQL correspondiente. Esta estructura me permite tener el código organizado y fácil de mantener: si necesito cambiar cómo se calcula alguna métrica, solo modifico el método correspondiente sin tocar el resto del código.
Las cinco métricas que calculo son:
clientesActivos: número de clientes con estado activo
solicitudesPendientes: número de solicitudes de ingreso pendientes de aprobación
pagosPendientes: número de pagos que aún no han sido procesados
planesVirtuales: número de planes virtuales activos
accesosVencidos: número de planes que han vencido
Finalmente cargo la vista dashboard.php pasando el array $datos que contiene todas las estadísticas. La vista se encargará de presentar estas métricas de forma visual al administrador.//

private function validarAdministrador()
{
    validarAccesoAdministrador();
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para el dashboard:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador.
Si la validación falla, la función redirige al login o muestra un error de permisos. Al llamar este método al inicio del index(), me aseguro de que solo los administradores puedan ver el dashboard con las estadísticas sensibles del negocio.//

private function ejecutarConteo($sql)
{
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

//El método privado ejecutarConteo() es una herramienta interna que me permite ejecutar consultas de conteo de forma segura y consistente:
Este método lo creé para no repetir el código de ejecución de consultas en cada método de conteo. Recibe una consulta SQL como parámetro, la prepara y ejecuta usando PDO (lo cual me protege contra inyección SQL, aunque en este caso las consultas son estáticas).
Luego obtengo el resultado usando fetchColumn() que me devuelve el valor de la primera columna de la primera fila (perfecto para consultas COUNT). Lo convierto a entero para garantizar que siempre retorne un número.
Lo más importante es el manejo de errores: si la consulta falla por cualquier razón (tabla no existe, error de sintaxis, etc.), capturo la excepción PDOException y retorno 0. Esto me permite que el dashboard siga funcionando incluso si alguna tabla no existe o hay problemas de base de datos, mostrando simplemente un conteo de cero en lugar de romper toda la página.//

private function tablaExiste($tabla)
{
    $stmt = $this->db->query('SHOW TABLES LIKE ' . $this->db->quote($tabla));
    return (bool) $stmt->fetch();
}

//El método privado tablaExiste() me permite verificar si una tabla existe en la base de datos antes de consultarla:
Este método lo diseñé para manejar la compatibilidad entre diferentes versiones del esquema de base de datos. Uso la consulta SHOW TABLES LIKE que es específica de MySQL para verificar si la tabla existe.
El método quote() de PDO me permite escapar correctamente el nombre de la tabla para evitar problemas con caracteres especiales. Luego convierto el resultado a booleano: si hay resultados, la tabla existe (true); si no, no existe (false).
Esta verificación es crucial porque el sistema ha evolucionado y algunas tablas han cambiado de nombre (por ejemplo, de clientes a cliente, de pagos a pago, etc.). Con este método puedo detectar qué versión del esquema está usando la base de datos y ejecutar la consulta apropiada.//

private function contarClientesActivos()
{
    if ($this->tablaExiste('clientes')) {
        $sql = "SELECT COUNT(*)
                FROM clientes c
                INNER JOIN user u ON u.id_user = c.id_user
                WHERE c.estado_cliente IN ('ACTIVA', 'ACTIVO') AND u.estado = 'ACTIVO'";
        return $this->ejecutarConteo($sql);
    }
    $sql = "SELECT COUNT(*)
            FROM cliente c
            INNER JOIN users u ON u.id_usuario = c.id_cliente
            WHERE u.estado = 'ACTIVO'";
    return $this->ejecutarConteo($sql);
}

//El método privado contarClientesActivos() calcula el número de clientes que tienen estado activo en el sistema:
Este método lo diseñé con compatibilidad para dos versiones del esquema de base de datos. Primero verifico si existe la tabla clientes (versión nueva del esquema). Si existe, ejecuto una consulta que hace JOIN entre la tabla clientes y user, filtrando por estado de cliente ('ACTIVA' o 'ACTIVO') y estado de usuario ('ACTIVO').
Si la tabla clientes no existe (versión antigua del esquema), ejecuto una consulta alternativa que usa las tablas cliente y users con los nombres de columnas correspondientes a esa versión.
En ambos casos uso el método ejecutarConteo() que ya maneja los errores y me devuelve un número entero. Esta flexibilidad me permite que el dashboard funcione correctamente sin importar qué versión del esquema de base de datos esté usando la instalación.//

private function contarSolicitudesPendientes()
{
    if ($this->tablaExiste('solicitudes_compra')) {
        $sql = "SELECT COUNT(*)
                FROM solicitudes_compra
                WHERE estado_solicitud = 'PENDIENTE'";
        return $this->ejecutarConteo($sql);
    }
    $sql = "SELECT COUNT(*) FROM solicitud_ingreso WHERE estado = 'PENDIENTE'";
    return $this->ejecutarConteo($sql);
}

//El método privado contarSolicitudesPendientes() calcula el número de solicitudes que están pendientes de aprobación:
Este método sigue el mismo patrón de compatibilidad que el anterior. Verifico si existe la tabla solicitudes_compra (versión nueva). Si existe, cuento las solicitudes con estado 'PENDIENTE'.
Si no existe, uso la tabla alternativa solicitud_ingreso con el nombre de columna estado. En ambos casos filtro por el estado 'PENDIENTE' para mostrar solo las solicitudes que requieren atención del administrador.
Esta métrica es importante porque le indica al administrador cuántas solicitudes están esperando su revisión y aprobación.//

private function contarPagosPendientes()
{
    if ($this->tablaExiste('pagos')) {
        $sql = "SELECT COUNT(*) FROM pagos WHERE estado_pago = 'PENDIENTE'";
        return $this->ejecutarConteo($sql);
    }
    $sql = "SELECT COUNT(*) FROM pago WHERE estado_pago = 'PENDIENTE'";
    return $this->ejecutarConteo($sql);
}

//El método privado contarPagosPendientes() calcula el número de pagos que aún no han sido procesados:
Este método es más simple que los anteriores porque la estructura de las tablas es similar en ambas versiones. Verifico si existe la tabla pagos (plural, versión nueva) y cuento los registros con estado 'PENDIENTE'.
Si no existe, uso la tabla pago (singular, versión antigua) con la misma lógica. Esta métrica es crucial para el administrador porque le indica cuántos pagos están esperando confirmación o procesamiento.//

private function contarPlanesVirtuales()
{
    if ($this->tablaExiste('planes_cliente')) {
        $sql = "SELECT COUNT(*)
                FROM planes_cliente pc
                INNER JOIN planes p ON p.id_plan = pc.id_plan
                WHERE pc.estado_plan_cliente = 'ACTIVO' AND p.modalidad = 'VIRTUAL'";
        return $this->ejecutarConteo($sql);
    }
    $sql = "SELECT COUNT(*) FROM plan_cliente WHERE estado = 'ACTIVO'";
    return $this->ejecutarConteo($sql);
}

//El método privado contarPlanesVirtuales() calcula el número de planes virtuales que están activos:
Este método lo diseñé para contar específicamente los planes de modalidad virtual. En la versión nueva del esquema, hago un JOIN entre planes_cliente y planes para filtrar por estado 'ACTIVO' del plan del cliente y modalidad 'VIRTUAL' del plan.
En la versión antigua, uso la tabla plan_cliente y filtro solo por estado 'ACTIVO' (asumiendo que todos los planes en esa tabla son virtuales o que no hay distinción de modalidad).
Esta métrica le indica al administrador cuántos clientes están actualmente usando los planes virtuales de la plataforma.//

private function contarAccesosVencidos()
{
    if ($this->tablaExiste('planes_cliente')) {
        $sql = "SELECT COUNT(*)
                FROM planes_cliente
                WHERE estado_plan_cliente = 'VENCIDO'
                OR (fecha_fin IS NOT NULL AND fecha_fin < CURDATE())";
        return $this->ejecutarConteo($sql);
    }
    $sql = "SELECT COUNT(*) FROM plan_cliente WHERE estado = 'VENCIDO'";
    return $this->ejecutarConteo($sql);
}

//El método privado contarAccesosVencidos() calcula el número de planes que han vencido y requieren atención:
Este método lo diseñé con una lógica más compleja en la versión nueva del esquema. No solo cuento los planes con estado 'VENCIDO' explícito, sino también aquellos cuya fecha de fin ya pasó (usando CURDATE() de MySQL para comparar con la fecha actual).
Esto me permite detectar planes que técnicamente no han sido marcados como vencidos pero cuya fecha de expiración ya pasó. Es una validación adicional que me ayuda a mantener los datos consistentes.
En la versión antigua, simplemente cuento los registros con estado 'VENCIDO'. Esta métrica es importante porque le indica al administrador cuántos clientes tienen planes vencidos que podrían necesitar renovación o seguimiento.//

$controller = new DashboardController();
$controller->index();
?>

//En este bloque final instancio el controlador y ejecuto directamente el método index:
A diferencia de otros controladores que tienen un sistema de enrutamiento basado en el parámetro accion, el dashboard es más simple porque solo tiene una acción principal: mostrar el panel. Por eso instancio el controlador y llamo directamente al método index() sin necesidad de verificar métodos dinámicos.
Esta simplificación es válida porque el dashboard no necesita múltiples acciones como los otros controladores (crear, actualizar, eliminar, etc.). Su única función es presentar las estadísticas consolidadas al administrador.//

//Este controlador es el punto de entrada visual para los administradores de StayFitMVC. Centraliza la consulta de métricas clave del negocio desde múltiples tablas de la base de datos, presentando una visión general del estado del sistema. La arquitectura que implementé prioriza la performance y la compatibilidad: uso consultas SQL directas en lugar de pasar por modelos para obtener conteos rápidos, y manejo dos versiones del esquema de base de datos para asegurar que el dashboard funcione sin importar la versión instalada. El manejo robusto de errores (try-catch en ejecutarConteo()) garantiza que el dashboard siempre se muestre, incluso si hay problemas con alguna tabla específica. Las cinco métricas presentadas (clientes activos, solicitudes pendientes, pagos pendientes, planes virtuales y accesos vencidos) proporcionan al administrador la información necesaria para tomar decisiones rápidas sobre el estado del negocio.//