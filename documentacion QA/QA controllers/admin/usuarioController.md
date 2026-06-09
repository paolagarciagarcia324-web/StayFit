Controlador encargado de gestionar los usuarios del sistema (administradores, coaches y clientes) dentro de la plataforma StayFitMVC, permitiendo su registro, actualización, cambio de estado y eliminación.

require_once __DIR__ . '/../../config/roles.php'; // Validación de roles
require_once __DIR__ . '/../../models/usuario/usuarioModel.php'; // Importa el modelo de usuarios
require_once __DIR__ . '/../../models/usuario/rolModel.php'; // Importa el modelo de roles

//En este bloque importo los archivos necesarios para el funcionamiento del controlador:
El archivo roles.php me permite validar permisos administrativos. Posteriormente cargo los modelos usuarioModel.php y rolModel.php que gestionan la información de los usuarios y los roles disponibles en el sistema respectivamente. Estas dependencias me permiten acceder a la información almacenada en la base de datos y ejecutar las operaciones necesarias para la gestión de usuarios.//

class UsuarioController
{
    private $usuarioModel; // Modelo de usuarios
    private $rolModel; // Modelo de roles

//En este bloque declaro la clase UsuarioController y sus propiedades privadas:
Declaro las propiedades $usuarioModel y $rolModel que almacenarán las instancias de los modelos. Gracias a ellas puedo acceder a las funciones encargadas de consultar, crear, actualizar y eliminar usuarios, así como gestionar los roles disponibles, sin necesidad de crear nuevas instancias cada vez que se ejecuta una acción. Esta clase funciona como intermediaria entre las vistas y los modelos siguiendo la estructura MVC implementada en StayFitMVC.//

public function __construct()
{
    session_start(); // Inicia la sesión
    $this->validarAdministrador(); // Valida acceso del administrador
    $this->usuarioModel = new UsuarioModel(); // Instancia el modelo de usuarios
    $this->rolModel = new RolModel(); // Instancia el modelo de roles
}

//El constructor se ejecuta automáticamente cuando se crea una instancia del controlador. Su función es preparar el entorno de trabajo:
Primero inicio la sesión con session_start() para poder acceder a las variables de sesión del usuario autenticado. Luego llamo al método privado validarAdministrador() que se encarga de verificar que solo los administradores puedan acceder a estas funcionalidades. Finalmente, instancio los modelos de usuario y rol, dejándolos listos para ser utilizados en cualquiera de los métodos del controlador.//

public function index()
{
    $usuarios = $this->usuarioModel->obtenerTodos(); // Obtiene todos los usuarios
    $roles = $this->rolModel->obtenerTodos(); // Obtiene todos los roles
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    require_once __DIR__ . '/../../views/admin/usuarios.php'; // Carga la vista de usuarios
}

//El método index() es el punto de entrada principal del controlador y se encarga de mostrar el listado de usuarios:
Utilizo el modelo para obtener todos los usuarios registrados en la base de datos mediante el método obtenerTodos(). Adicionalmente, obtengo todos los roles disponibles mediante el modelo de roles, lo cual es necesario para el formulario de creación y edición donde el administrador puede asignar o cambiar el rol de un usuario.
Recupero cualquier mensaje flash almacenado en la sesión (de acciones anteriores como creaciones, actualizaciones o eliminaciones) y lo limpio inmediatamente con unset() para que no se muestre dos veces. Posteriormente cargo la vista usuarios.php que renderizará esta información.//

public function guardar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío por POST
        $datos = [
            'nombre' => trim($_POST['nombre']), // Nombre del usuario
            'correo' => trim($_POST['correo']), // Correo del usuario
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), // Contraseña cifrada
            'rol_id' => $_POST['rol_id'], // Rol asignado
            'estado' => 'activo' // Estado inicial
        ];
        $this->usuarioModel->crear($datos); // Guarda el usuario
    }
    header('Location: usuarioController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método guardar() se encarga del registro completo de un nuevo usuario en el sistema:
Este método lo diseñé para manejar la creación de usuarios con diferentes roles (administrador, coach, cliente). Primero verifico que la solicitud sea de tipo POST para asegurarme de que los datos vienen del formulario.
Preparo un array con todos los datos del usuario. Lo más importante aquí es el manejo de la contraseña: uso la función password_hash() con el algoritmo PASSWORD_DEFAULT para cifrar la contraseña antes de guardarla en la base de datos. Esto es una práctica de seguridad fundamental que nunca debo omitir.
Los datos incluyen el nombre, correo, contraseña ya cifrada, el rol asignado (que viene del formulario) y el estado inicial 'activo'. Luego llamo al método crear() del modelo que inserta el registro en la base de datos. Finalmente redirijo al listado de usuarios.//

public function actualizar()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Verifica envío por POST
        $datos = [
            'id' => $_POST['id'], // ID del usuario
            'nombre' => trim($_POST['nombre']), // Nombre actualizado
            'correo' => trim($_POST['correo']), // Correo actualizado
            'rol_id' => $_POST['rol_id'], // Rol actualizado
            'estado' => $_POST['estado'] // Estado actualizado
        ];
        $this->usuarioModel->actualizar($datos); // Actualiza el usuario
    }
    header('Location: usuarioController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método actualizar() me permite modificar la información de un usuario existente:
Este método recibe los datos actualizados mediante POST y los organiza en un array $datos. Aquí incluyo el ID del usuario, el nombre, correo, rol y estado actualizados.
Es importante notar que en este método no manejo la contraseña, ya que la actualización de contraseña generalmente se maneja en un formulario separado por seguridad. Una vez recopilados los datos, llamo al método actualizar() del modelo que se encarga de persistir los cambios en la base de datos. Finalmente redirijo al listado principal.//

public function cambiarEstado()
{
    if (isset($_GET['id']) && isset($_GET['estado'])) { // Verifica datos recibidos
        $id = $_GET['id']; // ID del usuario
        $estado = $_GET['estado']; // Nuevo estado
        $this->usuarioModel->cambiarEstado($id, $estado); // Cambia el estado
    }
    header('Location: usuarioController.php'); // Redirige al listado
    exit; // Detiene la ejecución
}

//El método cambiarEstado() me permite activar o desactivar usuarios de forma rápida:
Recibo mediante GET el ID del usuario y el nuevo estado que debe tener. Verifico que ambos parámetros existan antes de proceder.
Llamo al método cambiarEstado() del modelo que actualiza únicamente este campo en la base de datos. Esto me permite inhabilitar el acceso de un usuario temporalmente sin perder su información histórica. Redirijo al listado. Este método es muy útil cuando necesito suspender el acceso de un usuario por alguna razón administrativa.//

public function eliminar()
{
    $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
    $adminId = (int) ($_SESSION['usuario_id'] ?? 0);
    if ($id < 1) {
        $this->flash('error', 'Usuario no válido.');
    } elseif ($id === $adminId) {
        $this->flash('error', 'No puedes eliminar tu propia cuenta de administrador.');
    } else {
        try {
            $this->usuarioModel->eliminar($id);
            $this->usuarioModel->registrarTrazabilidad($adminId ?: null, 'Usuario eliminado (ID ' . $id . ')');
            $this->flash('success', 'Usuario eliminado correctamente.');
        } catch (PDOException $e) {
            $this->flash(
                'error',
                'No se pudo eliminar: el usuario tiene registros vinculados (planes, pagos, mensajes). Inactívalo en su lugar.'
            );
        }
    }
    header('Location: usuarioController.php');
    exit;
}

//El método eliminar() es uno de los más complejos del controlador, ya que incluye múltiples validaciones de seguridad y manejo de errores:
Este método lo diseñé con varias capas de protección para evitar eliminaciones accidentales o problemáticas.
Primero obtengo el ID del usuario tanto de GET como de POST (por flexibilidad) y lo convierto a entero. También obtengo el ID del administrador actual desde la sesión.
Luego realizo tres validaciones importantes:
Validación de ID válido: Si el ID es menor a 1, muestro un error indicando que el usuario no es válido.
Protección de auto-eliminación: Si el ID del usuario a eliminar es igual al ID del administrador actual, muestro un error específico indicando que no puede eliminar su propia cuenta. Esta es una protección crítica para evitar que un administrador se bloquee a sí mismo del sistema.
Intento de eliminación: Si pasa las validaciones anteriores, intento eliminar el usuario dentro de un bloque try-catch.
Dentro del bloque try, llamo al método eliminar() del modelo y registro la acción en la trazabilidad. Si la eliminación tiene éxito, muestro un mensaje de confirmación.
Si ocurre un error de base de datos (capturado por PDOException), significa que el usuario tiene registros vinculados en otras tablas (planes, pagos, mensajes, etc.) que impiden su eliminación por restricciones de integridad referencial. En este caso, muestro un mensaje específico sugiriendo inactivar el usuario en lugar de eliminarlo. Esta es una buena práctica que mantiene la integridad de los datos históricos.//

private function flash($tipo, $mensaje)
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

//El método privado flash() es una herramienta interna que me permite almacenar mensajes temporales en la sesión:
Este método lo creé para no repetir código en cada función del controlador. Recibe dos parámetros: el tipo de mensaje (success, error, warning) y el mensaje en sí. Almacena ambos en la variable de sesión $_SESSION['flash'] que luego la vista puede leer y mostrar al usuario.
La ventaja de usar este sistema es que los mensajes persisten a través de redirecciones, permitiendo mostrar feedback al usuario después de una acción. Una vez que la vista muestra el mensaje, normalmente se limpia la variable de sesión.//

private function validarAdministrador()
{
    $rol = strtolower($_SESSION['rol'] ?? ''); // Obtiene el rol de sesión
    if ($rol !== 'admin' && $rol !== 'administrador') { // Valida permisos
        header('Location: ../../views/auth/accesoDenegado.php'); // Redirige si no tiene acceso
        exit; // Detiene la ejecución
    }
}

//El método privado validarAdministrador() actúa como un guardián de seguridad para todo el controlador:
Este método lo implementé directamente en el controlador. Obtengo el rol del usuario desde la sesión y lo convierto a minúsculas para hacer la comparación case-insensitive.
Verifico que el rol sea 'admin' o 'administrador' (acepto ambas variantes por compatibilidad). Si el usuario no tiene el rol adecuado, lo redirijo a la página de acceso denegado y detengo la ejecución. Al llamar este método en el constructor, me aseguro de que todas las acciones del controlador estén protegidas sin tener que validarlo en cada método individualmente. Esto es especialmente importante para la gestión de usuarios porque es una funcionalidad sensible que solo debe estar disponible para administradores.//

$controller = new UsuarioController(); // Crea el controlador
$accion = $_GET['accion'] ?? 'index'; // Acción por defecto
if (method_exists($controller, $accion)) { // Verifica si existe el método
    $controller->$accion(); // Ejecuta la acción
} else {
    $controller->index(); // Carga listado por defecto
}
?>

//En este bloque final implemento el enrutamiento básico del controlador:
Una vez cerrada la clase, instancio el controlador y determino qué método debo ejecutar basándome en el parámetro accion que viene por URL (GET). Si no se especifica ninguna acción, por defecto ejecuto el método index().
Verifico que el método solicitado exista usando method_exists() para evitar errores. Si existe, lo ejecuto dinámicamente usando la sintaxis $controller->$accion(). Si no existe, cargo la vista principal como medida de seguridad.
Este patrón me permite tener un controlador versátil donde puedo acceder a diferentes funcionalidades mediante URLs como:
usuarioController.php?accion=index (listado de usuarios)
usuarioController.php?accion=guardar (crear nuevo usuario - vía POST)
usuarioController.php?accion=actualizar (editar usuario - vía POST)
usuarioController.php?accion=cambiarEstado&id=5&estado=inactivo (activar/desactivar usuario)
usuarioController.php?accion=eliminar&id=5 (eliminar usuario)//

//Este controlador es el centro de gestión de usuarios en StayFitMVC. Centraliza toda la lógica de negocio relacionada con usuarios del sistema, desde su creación con cifrado de contraseñas, hasta la actualización, cambio de estado y eliminación con protecciones de seguridad. La arquitectura que implementé sigue los principios MVC manteniendo separadas las responsabilidades: el controlador maneja el flujo y la validación, los modelos se encargan de la base de datos, y las vistas presentan la información al usuario. Una característica importante es la protección contra auto-eliminación y el manejo robusto de errores de integridad referencial al eliminar usuarios con registros vinculados.//