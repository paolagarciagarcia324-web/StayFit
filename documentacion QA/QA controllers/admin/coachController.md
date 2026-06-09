Controlador encargado de gestionar todas las operaciones relacionadas con los coaches (entrenadores) de la plataforma StayFitMVC, permitiendo su registro, actualización, cambio de estado y visualización de detalles.

//Este archivo coachController.php corresponde al controlador encargado de gestionar todas las operaciones relacionadas con los coaches dentro de la plataforma StayFitMVC. Su función principal es administrar el ciclo de vida completo de los entrenadores: desde el registro, consulta, actualización, hasta la gestión de su estado y visualización de detalles con sus clientes asignados. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (como validaciones de datos, división de nombres completos, asignación de roles) y delega las operaciones de almacenamiento y consulta al modelo de coach y usuario.//

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php'; // Validación de roles
require_once __DIR__ . '/../../models/coach/coachModel.php'; // Importa el modelo de coach
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo de usuario

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos, helpers.php me proporciona funciones auxiliares como dividirNombreCompleto() que usaré más adelante. Posteriormente cargo los modelos coachModel.php y usuarioModel.php que gestionan la información de coaches y usuarios respectivamente. Estas dependencias me permiten acceder a la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión de coaches.//

class CoachController
{
    private $coachModel; // Modelo de coach
    private $usuarioModel; // Modelo de usuario

//En este bloque declaro la clase CoachController y sus propiedades privadas:
Declaro las propiedades $coachModel y $usuarioModel que almacenarán las instancias de los modelos. Gracias a ellas puedo acceder a las funciones encargadas de consultar, crear y actualizar coaches y usuarios sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->coachModel = new CoachModel(); // Instancia el modelo coach
    $this->usuarioModel = new UsuarioModel(); // Instancia el modelo usuario
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades. Finalmente, instancio los modelos de coach y usuario, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador. Esto me evita tener que crear las instancias repetidamente en cada método.//

public function index()
{
    $coaches = $this->coachModel->obtenerTodos(); // Obtiene todos los coaches
    require_once __DIR__ . '/../../views/admin/coaches.php'; // Carga la vista
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de coaches:
Utilizo el modelo para obtener todos los coaches registrados en la base de datos mediante el método obtenerTodos(). Los datos se almacenan en la variable $coaches que estará disponible en la vista. Posteriormente cargo la vista coaches.php ubicada en views/admin/, que es la encargada de mostrar visualmente la información al administrador. Esta vista puede renderizar tanto el listado completo como los detalles cuando se solicita.//

public function guardar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $partes = dividirNombreCompleto(trim($_POST['nombre'] ?? ''));
        $identificacion = trim($_POST['identificacion'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '') ?: $identificacion;
        $usuario = [
            'nombre' => $partes['nombre'],
            'apellido' => $partes['apellido'],
            'correo' => trim($_POST['correo']),
            'password' => $contrasena,
            'telefono' => trim($_POST['celular'] ?? ''),
            'documento_identidad' => $identificacion,
            'origen_registro' => 'ADMINISTRATIVO',
            'estado' => 'ACTIVO',
        ];
        $usuarioId = $this->usuarioModel->crear($usuario);
        $this->usuarioModel->asignarRol($usuarioId, 2); // Rol Coach
        $coach = [
            'id_coach' => $usuarioId, // ID del usuario creado
            'especialidad' => trim($_POST['especialidad']), // Especialidad fitness
            'credencial' => trim($_POST['identificacion'] ?? ''), // Credencial
            'biografia' => trim($_POST['biografia'] ?? '') // Descripción profesional
        ];
        $this->coachModel->crear($coach); // Registra el coach
        $this->registrarTrazabilidad('Coach registrado'); // Guarda trazabilidad
    }
    header('Location: coachController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método guardar() es uno de los más importantes del controlador, ya que se encarga del registro completo de un nuevo coach. Este método lo diseñé para manejar todo el proceso de creación tanto del usuario como del coach:
Primero verifico que la solicitud sea de tipo POST para asegurarme de que los datos vienen del formulario. Luego uso la función auxiliar dividirNombreCompleto() que viene del archivo helpers.php para separar el nombre completo ingresado en nombre y apellido. Esta función me ahorra tener que hacer esa lógica manualmente.
Obtengo la identificación y preparo la contraseña: si el administrador no ingresó una contraseña específica, uso la identificación como contraseña temporal (igual que en el controlador de clientes). Esto facilita el registro inicial.
Preparo un array con todos los datos del usuario, incluyendo información personal y metadatos importantes. El campo origen_registro lo establezco como 'ADMINISTRATIVO' para indicar que este usuario fue creado por un admin. El estado inicial es 'ACTIVO' para que pueda ingresar inmediatamente.
Llamo al método crear() del modelo de usuario que inserta el registro y me devuelve el ID del nuevo usuario. Inmediatamente después, uso el método asignarRol() para otorgarle el rol de coach (ID 2) al usuario recién creado. Esto es fundamental para que el sistema de permisos funcione correctamente.
Luego preparo un array con los datos específicos del coach: el id_coach que es el mismo ID del usuario creado (relación uno a uno), la especialidad fitness, la credencial y la biografía profesional. Finalmente llamo al método crear() del modelo de coach para insertar este registro y registro la acción en la trazabilidad.
Redirijo al listado de coaches con un mensaje de confirmación.//

public function actualizar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'id_coach' => $_POST['id'], // ID del coach
            'especialidad' => trim($_POST['especialidad']), // Especialidad actualizada
            'credencial' => trim($_POST['identificacion'] ?? ''), // Credencial
            'biografia' => trim($_POST['biografia'] ?? '') // Biografía actualizada
        ];
        $this->coachModel->actualizar($datos); // Actualiza el coach
        if (!empty($_POST['estado'])) { // Cambia estado usuario
            $this->coachModel->cambiarEstado($_POST['id'], strtoupper($_POST['estado'])); // ACTIVO/INACTIVO
        }
        $this->registrarTrazabilidad('Coach actualizado'); // Guarda trazabilidad
    }
    header('Location: coachController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método actualizar() me permite modificar la información de un coach existente:
Este método recibe los datos actualizados mediante POST y los organiza en un array $datos que incluye los campos modificables del coach: especialidad, credencial y biografía. El ID del coach viene en el campo id del formulario.
Una vez recopilados los datos, llamo al método actualizar() del modelo que se encarga de persistir los cambios en la base de datos. Luego verifico si se envió un nuevo estado (ACTIVO/INACTIVO) y si es así, llamo al método cambiarEstado() del modelo para actualizarlo. Uso strtoupper() para asegurar que el estado esté en mayúsculas.
Después registro la acción en la trazabilidad para mantener un historial de modificaciones. Finalmente, redirijo al listado principal.//

public function cambiarEstado()
{
    if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos
        $id = $_GET['id']; // ID del coach
        $estado = $_GET['estado']; // Nuevo estado
        $this->coachModel->cambiarEstado($id, $estado); // Cambia estado del coach
        $this->registrarTrazabilidad('Estado de coach cambiado'); // Guarda trazabilidad
    }
    header('Location: coachController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método cambiarEstado() es una funcionalidad específica para activar o desactivar coaches sin eliminar sus registros:
Recibo mediante GET el ID del coach y el nuevo estado que debe tener (generalmente 'ACTIVO' o 'INACTIVO'). Verifico que ambos parámetros existan antes de proceder.
Llamo al método cambiarEstado() del modelo que actualiza únicamente este campo en la base de datos. Esto me permite inhabilitar el acceso de un coach temporalmente sin perder su información histórica (clientes asignados, especialidades, etc.). Registro la acción en la trazabilidad y redirijo al listado. Este método es muy útil cuando un coach suspende su contrato temporalmente.//

public function detalle()
{
    $id = $_GET['id'] ?? null; // Obtiene ID del coach
    if (!$id) { // Valida si existe ID
        header('Location: coachController.php'); // Redirige si no hay ID
        exit; // Detiene la ejecución
    }
    $coach = $this->coachModel->obtenerPorId($id); // Obtiene datos del coach
    $clientes = $this->coachModel->obtenerClientesAsignados($id); // Obtiene clientes asignados
    require_once __DIR__ . '/../../views/admin/coaches.php'; // Carga la vista
}

//El método detalle() proporciona una vista completa de un coach específico con sus clientes asignados:
Este método lo diseñé para mostrar toda la información relevante de un coach en una sola vista. Primero obtengo el ID del coach desde los parámetros GET y valido que exista. Si no hay ID, redirijo inmediatamente al listado para evitar errores.
Luego realizo dos consultas al modelo para obtener información completa:
Los datos básicos del coach mediante obtenerPorId()
La lista de clientes asignados a ese coach con obtenerClientesAsignados()
Toda esta información se pone a disposición de la vista coaches.php que renderizará una ficha detallada del coach. Es importante notar que reutilizo la misma vista del método index(), pero la vista se encargará de mostrar diferente contenido según los datos disponibles.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->coachModel->registrarTrazabilidad($adminId, $accion); // Registra historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Coach registrado", "Coach actualizado", etc.).
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener una auditoría completa de quién hizo qué cambio y cuándo, lo cual es fundamental para la seguridad y el control administrativo.//

private function validarAdministrador()
{
    validarAccesoAdministrador(); // Valida sesión admin
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php que importé al inicio. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador.
Si la validación falla, la función probablemente redirija al login o muestre un error de permisos. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente.//

$controller = new CoachController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Define acción por defecto
if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Muestra vista principal
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
coachController.php?accion=index (listado)
coachController.php?accion=detalle&id=5 (ver detalle)
coachController.php?accion=cambiarEstado&id=5&estado=INACTIVO//

//Este controlador es el corazón de la gestión de coaches en StayFitMVC. Centraliza toda la lógica de negocio relacionada con entrenadores, desde su creación (que involucra tanto la tabla de usuarios como la de coaches), hasta consultas que muestran los clientes asignados a cada coach. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, los modelos se encargan de la base de datos, y las vistas presentan la información al usuario. Una característica importante es el uso de la función auxiliar dividirNombreCompleto() que simplifica el registro al permitir ingresar el nombre completo en un solo campo.//