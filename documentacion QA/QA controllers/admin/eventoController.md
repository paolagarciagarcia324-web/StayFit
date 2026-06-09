 Controlador encargado de gestionar los eventos y sesiones dentro de la plataforma StayFitMVC, permitiendo su creación, consulta y cambio de estado.

//Este archivo eventoController.php corresponde al controlador encargado de gestionar los eventos y sesiones dentro de la plataforma StayFitMVC. Su función principal es administrar el ciclo de vida de los eventos: desde su creación con toda la información relevante (título, descripción, fecha, hora, modalidad, coach asignado), hasta la consulta y gestión de su estado. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (validaciones de datos, control de estados) y delega las operaciones de almacenamiento y consulta al modelo de sesiones. Es importante notar que este controlador reutiliza la vista de reportes existente, lo cual es una decisión de diseño para mantener la consistencia visual sin crear vistas adicionales.//

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/agenda/sesionModel.php'; // Importa el modelo de sesiones

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Luego cargo el modelo sesionModel.php que gestiona tanto las sesiones como los eventos en la agenda del sistema. Este modelo encapsula toda la lógica de base de datos relacionada con eventos, manteniendo el controlador limpio y enfocado solo en el flujo de la aplicación.//

class EventoController
{
    private $sesionModel; // Modelo para sesiones y eventos

//En este bloque declaro la clase EventoController y su propiedad privada:
Declaro la propiedad $sesionModel que almacenará la instancia del modelo de sesiones. Gracias a esta propiedad puedo acceder a las funciones encargadas de crear, consultar y actualizar eventos sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y el modelo siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->sesionModel = new SesionModel(); // Instancia el modelo
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades.
Finalmente, instancio el modelo de sesiones, dejándolo listo para ser utilizado en cualquiera de los métodos del controlador. Al crear la instancia en el constructor, mantengo el código organizado y evito crear objetos repetidamente en cada método.//

public function index()
{
    $eventos = $this->sesionModel->obtenerTodos(); // Obtiene eventos y sesiones
    require_once __DIR__ . '/../../views/admin/reportes.php'; // Carga vista existente
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de eventos:
Utilizo el modelo para obtener todos los eventos y sesiones registrados en la base de datos mediante el método obtenerTodos(). Los datos se almacenan en la variable $eventos que estará disponible en la vista.
Posteriormente cargo la vista reportes.php ubicada en views/admin/. Es importante notar que reutilizo esta vista existente en lugar de crear una nueva vista específica para eventos. Esta decisión de diseño me permite mantener consistencia visual con otras secciones de reportes y evitar duplicación de código en las vistas. La vista se encargará de mostrar visualmente la información al administrador, probablemente en un formato de calendario o listado.//

public function guardar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'titulo' => trim($_POST['titulo']), // Título del evento
            'descripcion' => trim($_POST['descripcion']), // Descripción del evento
            'fecha' => $_POST['fecha'], // Fecha del evento
            'hora' => $_POST['hora'], // Hora del evento
            'modalidad' => $_POST['modalidad'], // Presencial, virtual o mixta
            'coach_id' => $_POST['coach_id'] ?? null, // Coach asignado
            'estado' => 'activo' // Estado inicial
        ];
        $this->sesionModel->crearEvento($datos); // Guarda el evento
        $this->registrarTrazabilidad('Evento registrado'); // Guarda trazabilidad
    }
    header('Location: eventoController.php'); // Redirige al panel
    exit; // Detiene la ejecución
}

//El método guardar() se encarga del registro completo de un nuevo evento:
Este método lo diseñé para manejar todo el proceso de creación de eventos. Primero verifico que la solicitud sea de tipo POST para asegurarme de que los datos vienen del formulario.
Luego recojo y organizo todos los campos del formulario en un array $datos:
titulo: Título del evento, limpio de espacios en blanco con trim().
descripcion: Descripción detallada del evento.
fecha: Fecha del evento (viene directamente del formulario en formato YYYY-MM-DD).
hora: Hora del evento (viene directamente del formulario en formato HH:MM).
modalidad: Tipo de modalidad (presencial, virtual o mixta), importante para saber cómo se desarrollará el evento.
coach_id: ID del coach asignado al evento, usando el operador de fusión null (??) para manejar el caso donde no se asigna ningún coach (valor null).
estado: Estado inicial del evento, siempre 'activo' para que esté disponible inmediatamente.
Una vez recopilados los datos, llamo al método crearEvento() del modelo que se encarga de insertar el registro en la base de datos. Después registro la acción en la trazabilidad para mantener un historial de auditoría.
Finalmente, redirijo al panel principal del controlador y detengo la ejecución con exit para evitar que se ejecute código adicional.//

public function cambiarEstado()
{
    if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos
        $id = $_GET['id']; // ID del evento
        $estado = $_GET['estado']; // Nuevo estado
        $this->sesionModel->cambiarEstado($id, $estado); // Cambia estado
        $this->registrarTrazabilidad('Estado de evento actualizado'); // Guarda trazabilidad
    }
    header('Location: eventoController.php'); // Redirige al panel
    exit; // Detiene la ejecución
}

//El método cambiarEstado() me permite activar o desactivar eventos sin eliminar sus registros:
Recibo mediante GET el ID del evento y el nuevo estado que debe tener. Verifico que ambos parámetros existan antes de proceder usando isset().
Llamo al método cambiarEstado() del modelo que actualiza únicamente este campo en la base de datos. Esto me permite inhabilitar un evento temporalmente sin perder su información histórica. Por ejemplo, si un evento se cancela o pospone, puedo cambiar su estado a 'inactivo' o 'cancelado' sin eliminar el registro.
Registro la acción en la trazabilidad para mantener un historial de modificaciones y redirijo al panel principal. Este método es muy útil para gestionar la agenda de eventos de forma flexible.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->sesionModel->registrarTrazabilidad($adminId, $accion); // Registra historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Evento registrado", "Estado de evento actualizado").
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién hizo qué cambio y cuándo, lo cual es fundamental para la seguridad y el control administrativo.//

private function validarAdministrador()
{
    $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene rol de sesión
    if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permiso
        header('Location: ../../views/auth/accesoDenegado.php'); // Redirige acceso denegado
        exit; // Detiene la ejecución
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método lo implementé directamente en el controlador (a diferencia de otros que usan la función externa validarAccesoAdministrador()). Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive.
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente.//

$controller = new EventoController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Acción por defecto
if (method_exists($controller, $accion)) { // Verifica método
    $controller->$accion(); // Ejecuta acción
} else {
    $controller->index(); // Carga vista principal
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
eventoController.php?accion=index (listado de eventos)
eventoController.php?accion=guardar (crear nuevo evento - vía POST)
eventoController.php?accion=cambiarEstado&id=5&estado=inactivo (activar/desactivar evento)//

//Este controlador es el centro de gestión de eventos y sesiones en StayFitMVC. Centraliza toda la lógica de negocio relacionada con eventos, desde su creación con información completa (título, descripción, fecha, hora, modalidad, coach asignado), hasta la gestión de su estado. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, el modelo se encarga de la base de datos, y la vista (reutilizada de reportes) presenta la información al usuario. Una característica importante es la reutilización de la vista de reportes, lo cual mantiene la consistencia visual y evita duplicación de código. El sistema de trazabilidad integrado permite mantener un historial completo de todas las acciones realizadas sobre los eventos.//