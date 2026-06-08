Teniendo en cuenta que el archivo helpers.php contiene un conjunto de funciones auxiliares utilizadas en diferentes módulos de la aplicación StayFitMVC. Su objetivo es centralizar tareas repetitivas relacionadas con el procesamiento de datos, manejo de archivos, generación de rutas, validaciones, cálculo de edades, gestión de comprobantes, material virtual y registro de actividades dentro del sistema. Gracias a este archivo es posible reutilizar código en distintos controladores, modelos y vistas, evitando duplicidad y facilitando el mantenimiento de la aplicación.

function dividirNombreCompleto($nombreCompleto)
{
    $nombreCompleto = trim(preg_replace('/\s+/', ' ', (string) $nombreCompleto));
    $partes = explode(' ', $nombreCompleto, 2);

    return [
        'nombre' => $partes[0] ?? $nombreCompleto,
        'apellido' => $partes[1] ?? '',
    ];
}

//Esta función fue creada para separar un nombre completo en dos partes principales: nombre y apellido. Primero realizo una limpieza del texto eliminando espacios innecesarios al inicio, al final o espacios múltiples entre palabras. Posteriormente utilizo la función explode() para dividir la cadena tomando como referencia el primer espacio encontrado. Finalmente retorno un arreglo asociativo que contiene el nombre en la posición nombre y el resto del texto en la posición apellido. Esta función es útil cuando el sistema recibe un nombre completo en un solo campo y necesita almacenarlo o procesarlo por separado.//

function edadAFechaNacimiento($edad)
{
    $edad = (int) $edad;

    if ($edad < 1 || $edad > 120) {
        return null;
    }

    $anio = (int) date('Y') - $edad;

    return sprintf('%d-01-01', $anio);
}

//Esta función permite convertir una edad proporcionada por el usuario en una fecha de nacimiento aproximada. Inicialmente convierto el valor recibido a un número entero para asegurar que el cálculo sea válido. Después verifico que la edad se encuentre dentro de un rango razonable entre 1 y 120 años; en caso contrario retorno un valor nulo. Si la edad es válida, obtengo el año actual y le resto la edad ingresada para calcular el año estimado de nacimiento. Finalmente construyo una fecha utilizando el primer día del año calculado. Esta función resulta útil cuando el sistema requiere almacenar una fecha de nacimiento estimada a partir de la edad suministrada.//

function calcularEdadDesdeFecha($fechaNacimiento)
{
    if (empty($fechaNacimiento)) {
        return null;
    }

    try {
        $nacimiento = new DateTime($fechaNacimiento);
        $hoy = new DateTime('today');

        return $nacimiento->diff($hoy)->y;
    } catch (Exception $e) {
        return null;
    }
}

//Esta función tiene como propósito calcular la edad actual de una persona a partir de su fecha de nacimiento. Primero verifico que la fecha recibida no se encuentre vacía. Posteriormente utilizo objetos DateTime para representar tanto la fecha de nacimiento como la fecha actual. Mediante el método diff() calculo la diferencia entre ambas fechas y retorno únicamente la cantidad de años transcurridos. Además implementé un bloque try-catch para controlar posibles errores derivados de formatos de fecha inválidos. Esta funcionalidad es utilizada en diferentes módulos donde se requiere mostrar o validar la edad de los usuarios registrados en la plataforma.//

function contrasenaYaHasheada($valor)
{
    return is_string($valor) && preg_match('/^\$2[ayb]\$.{56}$/', $valor);
}

//Esta función se encarga de verificar si una contraseña ya se encuentra cifrada utilizando el algoritmo BCrypt. Para ello compruebo que el valor recibido sea una cadena de texto y posteriormente utilizo una expresión regular que identifica el formato característico de los hashes generados por BCrypt. La función retorna un valor booleano indicando si la contraseña ya está protegida o si aún se encuentra en texto plano. Esta validación ayuda a evitar procesos de cifrado duplicados y garantiza una gestión adecuada de la seguridad de las credenciales almacenadas en la base de datos.//

function registrarBitacora(PDO $db, ?int $usuarioId, string $modulo, string $accion): bool

//Esta función tiene como finalidad registrar las acciones realizadas por los usuarios dentro del sistema StayFitMVC. Su propósito es mantener un historial de actividades que permita realizar auditorías, seguimiento de procesos y control de eventos importantes. La función recibe la conexión a la base de datos, el identificador del usuario, el módulo donde se ejecutó la acción y una descripción detallada de la actividad realizada.//

static $usaBitacoraSistema = null;
$usaBitacoraSistema = (bool) $db->query("SHOW TABLES LIKE 'bitacora_sistema'")

//Antes de registrar cualquier actividad verifico si existe la tabla bitacora_sistema dentro de la base de datos. Utilizo una variable estática para realizar esta comprobación una sola vez durante la ejecución y mejorar el rendimiento. Si la tabla existe, el registro se almacenará allí; en caso contrario se utilizará una tabla alternativa denominada bitacora_busqueda.//

INSERT INTO bitacora_sistema
INSERT INTO bitacora_busqueda

//Una vez identificada la tabla disponible, preparo una consulta SQL parametrizada para registrar la actividad realizada. Se almacenan datos como el usuario responsable, el módulo involucrado, la acción ejecutada y la fecha y hora del evento. La utilización de consultas preparadas mejora la seguridad frente a ataques de inyección SQL y garantiza la integridad de la información registrada.//

return $stmt->execute();

//Finalmente ejecuto la consulta preparada y retorno un valor booleano indicando si el registro fue almacenado correctamente. Si ocurre alguna excepción durante el proceso, la función captura el error y retorna false, permitiendo que el sistema continúe funcionando sin interrupciones.//
//
//
function rutaBaseProyecto(): string

//Esta función fue desarrollada para identificar automáticamente la ruta base donde se encuentra instalado el proyecto StayFitMVC. Su principal objetivo es evitar que las rutas sean escritas manualmente en diferentes partes del sistema, permitiendo que la aplicación funcione correctamente incluso si se cambia de servidor o de carpeta de instalación.//

static $base = null;

//Utilizo una variable estática para almacenar temporalmente la ruta base calculada. Gracias a esta estrategia, el sistema evita recalcular la misma información cada vez que la función es invocada, mejorando el rendimiento general de la aplicación.//

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

//En esta línea obtengo la ruta del script que se encuentra ejecutándose actualmente. Además, reemplazo las barras invertidas utilizadas por Windows por barras normales para mantener compatibilidad entre diferentes sistemas operativos y asegurar que las rutas sean consistentes.//

if (preg_match('#^(.*?)/controllers/#', $script, $coincidencia))

//En este bloque verifico si el archivo ejecutado pertenece a la carpeta controllers. Si la condición se cumple, extraigo únicamente la parte de la ruta correspondiente al directorio principal del proyecto. El mismo procedimiento se realiza para las carpetas controller, public y views, permitiendo identificar correctamente la raíz del sistema independientemente del punto desde donde se ejecute.//

return $base;

//Finalmente retorno la ruta base calculada para que pueda ser utilizada por otras funciones encargadas de generar enlaces y direcciones internas dentro de la aplicación.//

function baseUrl(): string

//Esta función genera automáticamente la URL completa de acceso al sistema. Su propósito es construir direcciones absolutas que incluyan el protocolo de comunicación, el servidor y la ruta base del proyecto.//

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

//En esta línea verifico si la aplicación se está ejecutando mediante HTTPS. Si existe un certificado SSL activo utilizo el protocolo seguro https; de lo contrario utilizo http. Esto garantiza que los enlaces generados coincidan con la configuración real del servidor.//

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

//Aquí obtengo el nombre del servidor o dominio desde el cual se está ejecutando la aplicación. Si por alguna razón no existe esta información, utilizo localhost como valor predeterminado.//

return rtrim($scheme . '://' . $host . $base, '/');

//Finalmente combino el protocolo, el nombre del servidor y la ruta base del proyecto para construir una URL completa y funcional que puede ser utilizada en enlaces, redirecciones y recursos compartidos.//

function urlRegistroInstitucion(?string $token = null): string

//Esta función genera la URL absoluta utilizada para acceder al formulario de registro institucional. Además permite incluir opcionalmente un token de validación dentro de la URL, lo que facilita procesos de invitación, confirmación o registro controlado de instituciones dentro del sistema.//

return baseUrl() . '/public/registro-institucion.php'

//Utilizo la función baseUrl() para obtener la dirección principal del sistema y posteriormente agrego la ruta correspondiente al formulario de registro institucional ubicado dentro de la carpeta public.//

'?token=' . urlencode($token)

//Si se recibe un token válido, lo agrego como parámetro de consulta dentro de la URL. Además utilizo urlencode() para asegurar que cualquier carácter especial sea convertido correctamente y no genere errores durante la navegación.//

function urlRegistroInstitucionForm(?string $token = null): string

//Esta función cumple un propósito similar a la función anterior, pero en lugar de generar una URL absoluta genera una ruta relativa dentro del proyecto. Esto resulta útil cuando se necesita construir formularios internos o redirecciones que no dependen del dominio completo del servidor.//

$ruta = rutaBaseProyecto() . '/public/registro-institucion.php';

//Utilizo la función rutaBaseProyecto() para obtener la ubicación principal del sistema y posteriormente agrego la ruta correspondiente al formulario de registro institucional.//

$ruta .= '?token=' . urlencode($token);

//Si existe un token válido, lo agrego a la ruta generada para permitir procesos de validación o autenticación asociados al registro institucional.//

function urlDashboardClienteInstitucional(): string

//Esta función tiene la responsabilidad de generar la ruta de acceso al panel principal de los clientes institucionales dentro de la plataforma StayFitMVC. Su utilización permite centralizar la ubicación del dashboard y evitar la repetición de rutas en distintos controladores o vistas.//

return rutaBaseProyecto() . '/controllers/clienteIns/dashboardController.php';

//En esta línea construyo la dirección que apunta al controlador encargado de gestionar el panel principal de los clientes institucionales. Al utilizar la función rutaBaseProyecto() garantizo que la ruta sea generada correctamente independientemente del entorno donde se encuentre desplegada la aplicación.//

//Dentro de la arquitectura MVC implementada en StayFitMVC, este conjunto de funciones auxiliares actúa como una capa de apoyo para controladores, modelos y vistas. Su finalidad es centralizar tareas relacionadas con la generación de rutas y URLs, permitiendo que los diferentes módulos del sistema puedan acceder a recursos internos sin depender de rutas escritas manualmente. Esto mejora la mantenibilidad del proyecto, reduce errores y facilita futuras modificaciones en la estructura de directorios.//
//
//
function rutaFisicaComprobante(?string $rutaRelativa): ?string

//Esta función fue creada para obtener la ubicación física real de un comprobante almacenado dentro del servidor. Su objetivo principal es transformar una ruta relativa guardada en la base de datos en una ruta absoluta que permita acceder directamente al archivo dentro del sistema operativo. Esta funcionalidad es utilizada cuando el sistema necesita visualizar, descargar o validar la existencia de un comprobante de pago.//

$ruta = trim((string) $rutaRelativa);

//Inicialmente limpio la ruta recibida eliminando espacios innecesarios y garantizando que el valor sea tratado como una cadena de texto.//

if ($ruta === '') {
    return null;
}

//Antes de continuar verifico que la ruta no esté vacía. Si no existe información válida, retorno un valor nulo para evitar errores posteriores.//

if (preg_match('/^https?:\/\//i', $ruta)) {
    return null;
}

//Verifico que la ruta corresponda a un archivo local y no a una dirección web externa. Esta función únicamente trabaja con archivos almacenados dentro del servidor del proyecto.//

$carpeta = realpath(dirname(__DIR__) . '/public/uploads/comprobantes');

//Obtengo la ubicación física de la carpeta donde se almacenan todos los comprobantes cargados por los usuarios dentro del sistema.//

$archivo = realpath($carpeta . DIRECTORY_SEPARATOR . $nombre);

//Genero la ruta completa del archivo solicitado y verifico que realmente exista dentro de la carpeta autorizada. Esto ayuda a prevenir accesos indebidos mediante manipulación de rutas.//

return $archivo;

//Finalmente retorno la ubicación física del comprobante para que pueda ser utilizada por otros procesos del sistema.//

function urlPublicaComprobante(?string $rutaRelativa, ?int $solicitudId = null, ?int $pagoId = null): ?string

//Esta función genera una URL pública que permite visualizar o descargar un comprobante almacenado dentro del sistema. Dependiendo de la información disponible, la URL puede construirse utilizando un identificador de solicitud, un identificador de pago o directamente el nombre del archivo.//

$parametros = [];

//Creo un arreglo que almacenará los parámetros necesarios para construir la URL de acceso al comprobante.//

$parametros['solicitud_id'] = $solicitudId;
$parametros['pago_id'] = $pagoId;

//Si existe un identificador asociado a una solicitud o pago, lo utilizo para construir una URL más segura y controlada.//

return rutaBaseProyecto() . '/public/verComprobante.php?' . http_build_query($parametros);

//Finalmente construyo una URL que apunta al archivo verComprobante.php, encargado de servir el archivo solicitado al usuario.//

function esComprobanteImagen(?string $ruta): bool

//Esta función permite identificar si un comprobante corresponde a un archivo de imagen. Para ello analizo la extensión del archivo y verifico si pertenece a formatos permitidos como JPG, PNG, GIF, WEBP o BMP. El resultado se utiliza para determinar la forma en que debe visualizarse el comprobante dentro del sistema.//

return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);

//Comparo la extensión encontrada con una lista de formatos de imagen permitidos y retorno un valor booleano indicando el resultado.//

function esComprobantePdf(?string $ruta): bool

//Esta función verifica si el comprobante corresponde a un documento PDF. Su propósito es facilitar la selección del visor adecuado cuando un usuario consulta evidencias cargadas al sistema.//

strtolower(pathinfo($ruta, PATHINFO_EXTENSION)) === 'pdf'

//Obtengo la extensión del archivo y verifico si corresponde al formato PDF.//

function guardarComprobanteIngreso(array $archivo): ?string

//Esta función gestiona el proceso de almacenamiento de comprobantes de pago cargados por los usuarios. Su responsabilidad es validar el archivo recibido, controlar el tamaño permitido, generar un nombre seguro y mover el archivo a la carpeta oficial de almacenamiento.//

is_uploaded_file($archivo['tmp_name'])

//Verifico que el archivo haya sido cargado correctamente mediante un formulario HTTP antes de continuar con el procesamiento.//

$permitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

//Defino una lista de extensiones autorizadas para garantizar que únicamente puedan cargarse imágenes y documentos PDF.//

$pesoMaximo = 5 * 1024 * 1024;

//Establezco un límite máximo de 5 MB para evitar cargas excesivamente grandes que puedan afectar el rendimiento del servidor.//

$nombreSeguro = 'comprobante_' . time() . '_' . uniqid('', true);

//Genero un nombre único utilizando la fecha actual y un identificador aleatorio para evitar conflictos entre archivos con nombres iguales.//

move_uploaded_file($archivo['tmp_name'], $rutaCompleta)

//Una vez validadas todas las condiciones, traslado el archivo desde la ubicación temporal de PHP hasta la carpeta oficial de comprobantes.//

function rutaFisicaMaterialVirtual(?string $rutaRelativa): ?string

//Esta función tiene un comportamiento similar a rutaFisicaComprobante(), pero orientado a los materiales educativos y contenido virtual almacenado dentro del sistema. Su función es localizar físicamente archivos multimedia para permitir su reproducción o descarga.//

function guardarMaterialVirtual(array $archivo, string $prefijo = 'material'): ?string

//Esta función administra la carga y almacenamiento de contenido virtual utilizado en cursos, entrenamientos y materiales educativos. Su diseño permite almacenar videos, imágenes y documentos PDF asociados a diferentes módulos de aprendizaje.//

$permitidas = [
    'mp4',
    'webm',
    'mov',
    'avi',
    'mkv',
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
    'pdf'
];

//Defino los formatos multimedia permitidos por la plataforma, garantizando compatibilidad con distintos tipos de contenido educativo.//

$pesoMaximo = 100 * 1024 * 1024;

//Establezco un tamaño máximo de 100 MB debido a que los archivos multimedia suelen requerir mayor espacio que los comprobantes tradicionales.//

$prefijoSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefijo);

//Limpio el prefijo recibido para eliminar caracteres potencialmente problemáticos y garantizar nombres de archivo válidos.//

move_uploaded_file($archivo['tmp_name'], $rutaCompleta)

//Una vez superadas todas las validaciones, guardo el archivo en la carpeta oficial de contenido virtual del proyecto.//

function urlPublicaMaterialVirtual(?string $rutaRelativa, ?int $videoId = null): ?string

//Esta función genera una URL pública para acceder a materiales virtuales almacenados dentro de la plataforma. Dependiendo de la información disponible, la URL puede construirse utilizando el identificador del video o el nombre del archivo.//

return rutaBaseProyecto() . '/public/verMaterialVirtual.php?' . http_build_query($parametros);

//Construyo una URL que apunta al controlador encargado de servir el material virtual solicitado por el usuario.//

function esUrlExternaVideo(?string $url): bool

//Esta función verifica si una dirección corresponde a un recurso externo alojado en Internet. Su finalidad es diferenciar videos almacenados localmente de videos provenientes de plataformas externas.//

preg_match('/^https?:\/\//i', trim($url))

//Utilizo una expresión regular para determinar si la URL inicia con los protocolos HTTP o HTTPS.//

function embedUrlVideo(?string $url): ?string

//Esta función transforma enlaces de plataformas externas en URLs compatibles con reproductores embebidos dentro del sistema. Gracias a ella es posible visualizar videos de YouTube y Vimeo directamente desde la plataforma sin necesidad de abandonar la aplicación.//

https://www.youtube.com/embed/

//Si la URL pertenece a YouTube, extraigo el identificador del video y construyo la dirección de reproducción embebida compatible con etiquetas <iframe>.//

https://player.vimeo.com/video/

//Si la URL corresponde a Vimeo, genero el enlace necesario para reproducir el contenido dentro del sistema.//

function tipoMediaDesdeArchivo(string $nombreArchivo): string

//Esta función determina automáticamente el tipo de contenido asociado a un archivo según su extensión. Esta clasificación permite decidir cómo debe mostrarse el recurso dentro de la interfaz de usuario.//

return 'IMAGEN';

//Si el archivo corresponde a formatos gráficos comunes, retorno la categoría IMAGEN.//

return 'PDF';

//Si el archivo posee extensión PDF, retorno la categoría correspondiente para utilizar un visor documental.//

return 'VIDEO';

//Para cualquier otro formato multimedia permitido, retorno la categoría VIDEO, permitiendo utilizar los reproductores disponibles dentro de la plataforma.//

El archivo helpers.php actúa como una biblioteca de funciones auxiliares dentro de StayFitMVC. Su propósito es centralizar tareas comunes relacionadas con validación de datos, cálculo de edades, generación de rutas, gestión de comprobantes, almacenamiento de contenido virtual, procesamiento de archivos multimedia y registro de actividades del sistema. Gracias a este enfoque se evita la duplicación de código en controladores y modelos, mejorando la organización, reutilización y mantenibilidad de la arquitectura MVC implementada en el proyecto.