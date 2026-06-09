Este archivo clienteController.php corresponde al controlador encargado de gestionar todas las operaciones relacionadas con los clientes dentro de la plataforma StayFitMVC. Su función principal es administrar el ciclo de vida completo de los clientes: desde el registro, consulta, actualización, hasta la gestión de su estado y visualización de detalles. Dentro de la arquitectura MVC, este controlador recibe las solicitudes provenientes de las vistas, procesa la lógica de negocio necesaria (como validaciones de datos, verificación de correos duplicados, generación de contraseñas) y delega las operaciones de almacenamiento y consulta al modelo de cliente y usuario.

require_once __DIR__ . '/../../config/roles.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../models/cliente/clienteModel.php';
require_once __DIR__ . '/../../models/usuario/usuarioModel.php';

//En este bloque importo todos los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos, helpers.php me proporciona funciones auxiliares (como la conversión de edad a fecha de nacimiento). Posteriormente cargo los modelos clienteModel.php y usuarioModel.php que gestionan la información de clientes y usuarios respectivamente. Estas dependencias me permiten acceder a la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión de clientes.//

class ClienteController
{
    private $clienteModel; // Modelo de clientes
    private $usuarioModel; // Modelo de usuarios

//En este bloque declaro la clase ClienteController y sus propiedades privadas:
Declaro las propiedades $clienteModel y $usuarioModel que almacenarán las instancias de los modelos. Gracias a ellas puedo acceder a las funciones encargadas de consultar, crear y actualizar clientes y usuarios sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso de administrador
    $this->clienteModel = new ClienteModel(); // Instancia el modelo cliente
    $this->usuarioModel = new UsuarioModel(); // Instancia el modelo usuario
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades. Finalmente, instancio los modelos de cliente y usuario, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador. Esto me evita tener que crear las instancias repetidamente en cada método.

public function index()
{
    $clientes = $this->clienteModel->obtenerTodos(); // Obtiene todos los clientes
    require_once __DIR__ . '/../../views/admin/clientes.php'; // Carga la vista
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de clientes:
Utilizo el modelo para obtener todos los clientes registrados en la base de datos mediante el método obtenerTodos(). Los datos se almacenan en la variable $clientes que estará disponible en la vista. Posteriormente cargo la vista clientes.php ubicada en views/admin/, que es la encargada de mostrar visualmente la información al administrador. Esta vista puede renderizar tanto el listado completo como los detalles cuando se solicita.//

public function guardarClienteFijo()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $celular = trim($_POST['celular'] ?? '');
        $edad = (int) ($_POST['edad'] ?? 0);
        $contrasena = trim($_POST['contrasena'] ?? '');

//El método guardarClienteFijo() es uno de los más importantes del controlador, ya que se encarga del registro completo de un nuevo cliente. Este método lo diseñé para manejar todo el proceso de creación tanto del usuario como del cliente:
Primero verifico que la solicitud sea de tipo POST para asegurarme de que los datos vienen del formulario. Luego recojo y limpio cada campo del formulario usando trim() para eliminar espacios en blanco innecesarios. Convierto la edad a entero para garantizar que sea un número válido.//

if ($nombre === '' || $apellido === '' || $correo === '' || $identificacion === '' || $celular === '' || $edad < 12) {
    $_SESSION['alert'] = [
        'icon' => 'warning',
        'title' => 'Datos incompletos',
        'text' => 'Complete nombre, apellido, correo, identificación, edad y celular.',
    ];
    header('Location: clienteController.php');
    exit;
}

//En este bloque realizo la validación de datos obligatorios antes de proceder con el registro:
Verifico que todos los campos requeridos (nombre, apellido, correo, identificación, celular) no estén vacíos y que la edad sea mayor o igual a 12 años (edad mínima para ser cliente en la plataforma). Si alguna validación falla, creo un mensaje de alerta tipo "warning" que se almacenará en la sesión y será mostrado en la vista. Luego redirijo de vuelta al listado de clientes y detengo la ejecución con exit para evitar que el código continúe.//

if ($this->usuarioModel->obtenerPorCorreo($correo)) {
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => 'Correo en uso',
        'text' => 'Ya existe un usuario con ese correo.',
    ];
    header('Location: clienteController.php');
    exit;
}

//Aquí valido que el correo electrónico no esté registrado previamente en el sistema:
Utilizo el método obtenerPorCorreo() del modelo de usuario para verificar si ya existe un registro con ese email. Si el correo ya está en uso, genero una alerta de tipo "error" informando al administrador que debe usar un correo diferente. Esta validación es crucial para mantener la integridad de los datos y evitar duplicados en el sistema.//

if ($contrasena === '') {
    $contrasena = $identificacion;
}

//Este bloque maneja la generación de contraseña por defecto:
Si el administrador no ingresó una contraseña específica en el formulario, asigno automáticamente la identificación del cliente como contraseña temporal. Esto facilita el registro inicial y permite que el cliente pueda acceder al sistema inmediatamente. Posteriormente, el cliente podrá cambiar su contraseña desde su perfil.//

$usuario = [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'correo' => $correo,
    'password' => $contrasena,
    'telefono' => $celular,
    'documento_identidad' => $identificacion,
    'origen_registro' => 'ADMINISTRATIVO',
    'estado' => 'ACTIVO',
];
$usuarioId = $this->usuarioModel->crear($usuario);
$this->usuarioModel->asignarRol($usuarioId, 3);

//En este bloque creo el registro de usuario en la base de datos:
Preparo un array con todos los datos del usuario, incluyendo información personal y metadatos importantes. El campo origen_registro lo establezco como 'ADMINISTRATIVO' para indicar que este usuario fue creado por un admin (no por registro web). El estado inicial es 'ACTIVO' para que pueda ingresar inmediatamente.
Luego llamo al método crear() del modelo de usuario que inserta el registro y me devuelve el ID del nuevo usuario. Inmediatamente después, uso el método asignarRol() para otorgarle el rol de cliente (ID 3) al usuario recién creado. Esto es fundamental para que el sistema de permisos funcione correctamente.//

$cliente = [
    'usuario_id' => $usuarioId,
    'edad' => $edad,
    'tipo_cliente' => $_POST['tipo_cliente'] ?? 'individual',
    'fecha_nacimiento' => edadAFechaNacimiento($edad),
];
$this->clienteModel->crearClienteFijo($cliente);

//Una vez creado el usuario, procedo a crear el registro específico del cliente:
Preparo un array con los datos particulares del cliente que no forman parte del usuario general. El campo usuario_id establece la relación entre el cliente y el usuario creado anteriormente (clave foránea). Calculo la fecha de nacimiento usando la función auxiliar edadAFechaNacimiento() que convierte la edad proporcionada en una fecha aproximada de nacimiento.
El campo tipo_cliente me permite diferenciar entre clientes individuales, institucionales, etc. Finalmente, llamo al método crearClienteFijo() del modelo para insertar este registro en la tabla de clientes.//

$_SESSION['alert'] = [
    'icon' => 'success',
    'title' => 'Cliente registrado',
    'text' => 'La clienta puede ingresar con su correo y la contraseña definida.',
];
$this->registrarTrazabilidad('Cliente fijo registrado');

//Al finalizar el registro exitoso, informo al administrador y dejo constancia de la acción:
Creo un mensaje de éxito que se mostrará en la vista, indicando que el cliente fue registrado correctamente y proporcionando información sobre las credenciales de acceso. Luego llamo al método privado registrarTrazabilidad() para guardar un registro de esta acción en el historial de trazabilidad, lo cual es importante para auditorías y control de cambios.//

header('Location: clienteController.php');
exit;
}

//Finalizo el método redirigiendo al listado de clientes:
Independientemente del resultado (éxito o error), redirijo al usuario de vuelta a la página principal del controlador. El exit es crucial aquí para detener la ejecución del script inmediatamente después del redireccionamiento, evitando que se ejecute código adicional.//

public function actualizar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío del formulario
        $datos = [
            'id' => $_POST['id'], // ID del cliente
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'correo' => trim($_POST['correo']), // Correo actualizado
            'identificacion' => trim($_POST['identificacion']), // Documento actualizado
            'edad' => $_POST['edad'], // Edad actualizada
            'celular' => trim($_POST['celular']), // Celular actualizado
            'tipo_cliente' => $_POST['tipo_cliente'], // Tipo de cliente
            'estado' => $_POST['estado'] // Estado actualizado
        ];
        $this->clienteModel->actualizar($datos); // Actualiza el cliente
        $this->registrarTrazabilidad('Cliente actualizado'); // Registra acción
    }
    header('Location: clienteController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método actualizar() me permite modificar la información de un cliente existente:
Este método recibe los datos actualizados mediante POST y los organiza en un array $datos que incluye todos los campos modificables: información personal (nombre, apellido, correo, identificación), datos de contacto (celular), información demográfica (edad), tipo de cliente y estado.
Una vez recopilados los datos, llamo al método actualizar() del modelo que se encarga de persistir los cambios en la base de datos. Después registro la acción en la trazabilidad para mantener un historial de modificaciones. Finalmente, redirijo al listado principal. A diferencia del método de creación, aquí confío en que los datos ya fueron validados previamente y el ID existe en la base de datos.//

public function cambiarEstado()
{
    if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos
        $id = $_GET['id']; // ID del cliente
        $estado = $_GET['estado']; // Nuevo estado
        $this->clienteModel->cambiarEstado($id, $estado); // Cambia estado del cliente
        $this->registrarTrazabilidad('Estado de cliente cambiado'); // Registra acción
    }
    header('Location: clienteController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método cambiarEstado() es una funcionalidad específica para activar o desactivar clientes sin eliminar sus registros:
Recibo mediante GET el ID del cliente y el nuevo estado que debe tener (generalmente 'ACTIVO' o 'INACTIVO'). Verifico que ambos parámetros existan antes de proceder.
Llamo al método cambiarEstado() del modelo que actualiza únicamente este campo en la base de datos. Esto me permite inhabilitar el acceso de un cliente temporalmente sin perder su información histórica (pagos, asignaciones, etc.). Registro la acción en la trazabilidad y redirijo al listado. Este método es muy útil cuando un cliente suspende su membresía temporalmente.//

public function detalle()
{
    $id = $_GET['id'] ?? null; // Obtiene ID del cliente
    if (!$id) { // Valida existencia del ID
        header('Location: clienteController.php'); // Redirige si no hay ID
        exit; // Detiene la ejecución
    }
    $cliente = $this->clienteModel->obtenerPorId($id); // Obtiene detalle del cliente
    $pagos = $this->clienteModel->obtenerPagos($id); // Obtiene pagos del cliente
    $plan = $this->clienteModel->obtenerPlanActivo($id); // Obtiene plan activo
    $coach = $this->clienteModel->obtenerCoachAsignado($id); // Obtiene coach asignado
    require_once __DIR__ . '/../../views/admin/clientes.php'; // Carga la vista
}

//El método detalle() proporciona una vista completa e integral de un cliente específico:
Este método lo diseñé para mostrar toda la información relevante de un cliente en una sola vista. Primero obtengo el ID del cliente desde los parámetros GET y valido que exista. Si no hay ID, redirijo inmediatamente al listado para evitar errores.
Luego realizo múltiples consultas al modelo para obtener información completa:
Los datos básicos del cliente mediante obtenerPorId()
El historial de pagos con obtenerPagos()
El plan activo actual con obtenerPlanActivo()
El coach asignado con obtenerCoachAsignado()
Toda esta información se pone a disposición de la vista clientes.php que renderizará una ficha detallada del cliente. Es importante notar que reutilizo la misma vista del método index(), pero la vista se encargará de mostrar diferente contenido según los datos disponibles.//

private function registrarTrazabilidad($accion)
{
    $adminId = $_SESSION['usuario_id'] ?? null; // ID del administrador
    $this->clienteModel->registrarTrazabilidad($adminId, $accion); // Guarda historial
}

//El método privado registrarTrazabilidad() es una herramienta interna que me permite mantener un registro de todas las acciones realizadas:
Este método lo creo para no repetir código en cada función del controlador. Recibe como parámetro una descripción de la acción realizada (por ejemplo: "Cliente registrado", "Cliente actualizado", etc.).
Obtengo el ID del administrador actualmente autenticado desde la sesión y delego al modelo la tarea de guardar este registro en la base de datos de trazabilidad. Esto me permite tener un auditoría completa de quién hizo qué cambio y cuándo, lo cual es fundamental para la seguridad y el control administrativo.//

private function validarAdministrador()
{
    validarAccesoAdministrador(); // Valida sesión admin
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método es muy simple pero crucial: llama a la función validarAccesoAdministrador() que está definida en el archivo roles.php que importé al inicio. Esta función se encarga de verificar que el usuario tenga una sesión activa y que posea el rol de administrador.
Si la validación falla, la función probablemente redirija al login o muestre un error de permisos. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente.//

}
$controller = new ClienteController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Acción por defecto
if (method_exists($controller, $accion)) { // Verifica si existe la acción
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga vista principal
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
clienteController.php?accion=index (listado)
clienteController.php?accion=detalle&id=5 (ver detalle)
clienteController.php?accion=cambiarEstado&id=5&estado=INACTIVO

Este controlador es el corazón de la gestión de clientes en StayFitMVC. Centraliza toda la lógica de negocio relacionada con clientes, desde su creación (que involucra tanto la tabla de usuarios como la de clientes), hasta consultas complejas que combinan información de pagos, planes y coaches asignados. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, los modelos se encargan de la base de datos, y las vistas presentan la información al usuario.//
