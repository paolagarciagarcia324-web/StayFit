Controlador encargado de gestionar las notificaciones administrativas dentro de la plataforma StayFitMVC, permitiendo su consulta, marcado como leídas y eliminación.

//Este archivo notificacionController.php corresponde al controlador encargado de gestionar las notificaciones dentro de la plataforma StayFitMVC. Su función principal es administrar el ciclo de vida de las notificaciones dirigidas a los administradores: desde la consulta y visualización, hasta el marcado como leídas y eliminación. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (filtrado por rol, actualización de estado) y delega las operaciones de almacenamiento y consulta al modelo de notificaciones. Es un controlador relativamente simple pero esencial para mantener a los administradores informados sobre eventos importantes del sistema.//

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/comunicacion/notificacionModel.php'; // Importa notificaciones

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Es importante notar que esta línea está duplicada (líneas 2 y 3), lo cual es un error menor que no afecta el funcionamiento pero podría limpiarse en una refactorización futura. Posteriormente cargo el modelo notificacionModel.php que gestiona las notificaciones en la base de datos. Este modelo encapsula toda la lógica de consulta, actualización y eliminación de notificaciones, manteniendo el controlador limpio y enfocado solo en el flujo de la aplicación.//

class NotificacionController
{
    private $notificacionModel; // Modelo de notificaciones

//En este bloque declaro la clase NotificacionController y su propiedad privada:
Declaro la propiedad $notificacionModel que almacenará la instancia del modelo de notificaciones. Gracias a esta propiedad puedo acceder a las funciones encargadas de consultar, actualizar y eliminar notificaciones sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y el modelo siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->notificacionModel = new NotificacionModel(); // Instancia el modelo
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas notificaciones.
Finalmente, instancio el modelo de notificaciones, dejándolo listo para ser utilizado en cualquiera de los métodos del controlador. Al crear la instancia en el constructor, mantengo el código organizado y evito crear objetos repetidamente en cada método.//

public function index()
{
    $notificaciones = $this->notificacionModel->obtenerPorRol('admin'); // Obtiene notificaciones admin
    require_once __DIR__ . '/../../views/admin/notificaciones.php'; // Carga la vista
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de notificaciones:
Utilizo el modelo para obtener todas las notificaciones dirigidas al rol 'admin' mediante el método obtenerPorRol('admin'). Este método filtra las notificaciones por el rol del destinatario, asegurando que cada usuario vea solo las notificaciones que le corresponden.
Los datos se almacenan en la variable $notificaciones que estará disponible en la vista. Posteriormente cargo la vista notificaciones.php ubicada en views/admin/, que es la encargada de mostrar visualmente las notificaciones al administrador, probablemente con indicadores visuales de cuáles han sido leídas y cuáles son nuevas.

public function marcarLeida()
{
    if (isset($_GET['id'])) { // Verifica ID recibido
        $id = $_GET['id']; // ID de notificación
        $this->notificacionModel->marcarLeida($id); // Marca como leída
    }
    header('Location: notificacionController.php'); // Redirige al panel
    exit; // Detiene ejecución
}

//El método marcarLeida() me permite marcar una notificación específica como leída:
Recibo mediante GET el ID de la notificación que debe ser marcada. Verifico que el parámetro exista usando isset() antes de proceder.
Llamo al método marcarLeida() del modelo que actualiza el estado de la notificación en la base de datos, cambiando probablemente un campo como leida de 0 a 1 o actualizando una fecha de lectura. Esto me permite mantener un registro de qué notificaciones ya han sido vistas por el administrador.
Redirijo de vuelta al listado de notificaciones y detengo la ejecución. Este método es muy útil cuando el administrador hace clic en una notificación para ver sus detalles o simplemente quiere marcarla como procesada.//

public function eliminar()
{
    if (isset($_GET['id'])) { // Verifica ID recibido
        $id = $_GET['id']; // ID de notificación
        $this->notificacionModel->eliminar($id); // Elimina notificación
    }
    header('Location: notificacionController.php'); // Redirige al panel
    exit; // Detiene ejecución
}

//El método eliminar() me permite eliminar una notificación de forma permanente:
Recibo mediante GET el ID de la notificación que debe ser eliminada. Verifico que el parámetro exista usando isset() antes de proceder.
Llamo al método eliminar() del modelo que borra el registro de la base de datos. Esta acción es irreversible, por lo que es importante que el administrador esté seguro de querer eliminar la notificación. En una versión futura podría implementar un sistema de papelera o confirmación antes de eliminar.
Redirijo de vuelta al listado de notificaciones y detengo la ejecución. Este método es útil para limpiar notificaciones antiguas o que ya no son relevantes.//

private function validarAdministrador()
{
    validarAccesoAdministrador(); // Valida sesión admin
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php que importé al inicio. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador.
Si la validación falla, la función probablemente redirija al login o muestre un error de permisos. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente. Esto es especialmente importante para las notificaciones porque pueden contener información sensible del sistema.//

$controller = new NotificacionController(); // Crea el controlador
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
notificacionController.php?accion=index (listado de notificaciones)
notificacionController.php?accion=marcarLeida&id=5 (marcar notificación como leída)
notificacionController.php?accion=eliminar&id=5 (eliminar notificación)//

//Este controlador es el centro de gestión de notificaciones administrativas en StayFitMVC. Centraliza toda la lógica de negocio relacionada con notificaciones, desde su consulta filtrada por rol, hasta el marcado como leídas y eliminación. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, el modelo se encarga de la base de datos, y la vista presenta la información al usuario. Es un controlador simple pero esencial para mantener a los administradores informados sobre eventos importantes del sistema. Una mejora futura podría ser agregar notificaciones en tiempo real o un sistema de confirmación antes de eliminar.//