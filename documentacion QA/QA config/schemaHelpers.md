Este archivo schemaHelper.php contiene la clase SchemaHelper, cuya función principal es gestionar la compatibilidad entre diferentes versiones del esquema de base de datos utilizadas por StayFitMVC. Esta clase fue diseñada para resolver automáticamente diferencias en los nombres de tablas entre estructuras antiguas (legacy) y estructuras nuevas de la base de datos. Gracias a este mecanismo, el sistema puede funcionar correctamente incluso cuando existen cambios en la nomenclatura de las tablas, evitando modificaciones constantes en los modelos y facilitando la migración entre versiones del proyecto.

class SchemaHelper
{

//En este bloque declaro la clase SchemaHelper, encargada de centralizar toda la lógica relacionada con la detección y resolución de diferencias entre esquemas de base de datos. Su implementación permite que el sistema identifique automáticamente qué tablas se encuentran disponibles y utilice la estructura adecuada durante la ejecución de consultas SQL.//

private PDO $db;

//Esta propiedad almacena la conexión activa a la base de datos utilizando el objeto PDO. Gracias a esta referencia, la clase puede ejecutar consultas necesarias para verificar la existencia de tablas y determinar qué esquema debe utilizar el sistema. La propiedad se declara como privada para evitar modificaciones externas y mantener el control interno de la conexión.//

private static array $cacheTablas = [];

//En este bloque declaro una propiedad estática utilizada como memoria temporal para almacenar los resultados de las verificaciones realizadas sobre las tablas de la base de datos. El objetivo de esta caché es mejorar el rendimiento, evitando consultas repetitivas cada vez que se necesita comprobar la existencia de una tabla. Al ser estática, la información permanece disponible durante toda la ejecución del script y puede ser compartida por todas las instancias de la clase.//

public function __construct(PDO $db)
{
    $this->db = $db;
}

//El constructor tiene la responsabilidad de recibir una conexión activa a la base de datos cuando se crea una instancia de la clase. Esta estrategia permite reutilizar la conexión ya establecida por la aplicación y evita la creación innecesaria de nuevas conexiones.//

$this->db = $db;

//En esta línea almaceno el objeto PDO recibido dentro de la propiedad interna de la clase. A partir de este momento, todos los métodos de SchemaHelper podrán utilizar dicha conexión para realizar consultas sobre la estructura de la base de datos.//

public function tablaExiste(string $nombre): bool

//Este método tiene como finalidad verificar si una tabla específica existe dentro de la base de datos. Su utilización resulta fundamental para determinar qué esquema se encuentra disponible y qué tablas deben utilizar los modelos durante la ejecución del sistema.//

if (array_key_exists($nombre, self::$cacheTablas))

//Antes de realizar una consulta a la base de datos, verifico si el resultado ya fue almacenado previamente en la memoria caché. Si existe información disponible, retorno dicho resultado inmediatamente, reduciendo el número de consultas necesarias y mejorando el rendimiento general de la aplicación.//

$stmt = $this->db->query(
    'SHOW TABLES LIKE ' . $this->db->quote($nombre)
);

//Si la tabla aún no ha sido verificada, ejecuto una consulta SQL utilizando la instrucción SHOW TABLES LIKE, la cual permite comprobar si existe una tabla cuyo nombre coincida con el valor recibido. Además utilizo el método quote() para proteger el valor y evitar problemas relacionados con caracteres especiales.//

self::$cacheTablas[$nombre] = (bool) $stmt->fetch(PDO::FETCH_NUM);

//Una vez obtenida la respuesta de la base de datos, convierto el resultado en un valor booleano y lo almaceno dentro de la caché. De esta forma, futuras consultas sobre la misma tabla podrán resolverse sin necesidad de acceder nuevamente a la base de datos.//

catch (PDOException $e)

//Implemento un bloque de captura de excepciones para gestionar posibles errores relacionados con la consulta SQL. Si ocurre algún problema durante la verificación, se asume que la tabla no existe y se almacena un valor negativo en la caché.//

return self::$cacheTablas[$nombre];

//Finalmente retorno el valor booleano almacenado en la caché, indicando si la tabla existe o no dentro de la base de datos.//

public function usaEsquemaNuevo(): bool

//Este método permite determinar si la aplicación está trabajando con el esquema moderno de la base de datos. Su finalidad es detectar automáticamente la estructura actual sin necesidad de configuraciones manuales.//

return $this->tablaExiste('clientes')
    || $this->tablaExiste('user');

//Para identificar el esquema nuevo, verifico la existencia de las tablas clientes o user, las cuales son características de la estructura actual utilizada por StayFitMVC. Si al menos una de ellas existe, considero que la aplicación está operando sobre el esquema moderno.//

public function tabla(string $nueva, string $legacy): string

//Este método tiene la responsabilidad de resolver automáticamente qué nombre de tabla debe utilizarse cuando existen diferencias entre versiones de la base de datos. Gracias a este mecanismo, los modelos pueden trabajar con una única llamada sin preocuparse por cambios en la nomenclatura de las tablas.//

if ($this->tablaExiste($nueva)) {
    return $nueva;
}

//Inicialmente verifico si existe la tabla correspondiente al esquema nuevo. Si se encuentra disponible, la retorno inmediatamente porque representa la estructura más actual y recomendada para el funcionamiento del sistema.//

if ($this->tablaExiste($legacy)) {
    return $legacy;
}

//Si la tabla moderna no existe, verifico si se encuentra disponible la versión antigua o legacy. Esto garantiza compatibilidad con bases de datos que aún no han sido migradas completamente al nuevo esquema.//

return $nueva;

//Si ninguna de las dos tablas existe, retorno el nombre de la tabla nueva como valor predeterminado. Esta decisión permite mantener consistencia en el código y facilita futuras migraciones del esquema de base de datos.//

$tablaUsuarios = $schemaHelper->tabla('clientes', 'usuarios');

//En este ejemplo solicito a SchemaHelper que determine automáticamente cuál tabla debe utilizarse. Si la base de datos contiene la tabla clientes, será seleccionada como opción principal. Si únicamente existe la tabla usuarios, se utilizará esta versión legacy. Gracias a esta lógica, el modelo puede seguir funcionando sin importar qué versión del esquema esté instalada.//


//Dentro de la arquitectura MVC implementada en StayFitMVC, la clase SchemaHelper actúa como un componente de apoyo para los modelos. Su función es abstraer las diferencias entre versiones de la base de datos, permitiendo que los modelos trabajen con nombres de tablas dinámicos y mantengan compatibilidad con distintos esquemas. Esto reduce el acoplamiento entre la capa de datos y la estructura física de la base de datos, facilita las migraciones y mejora la mantenibilidad general del sistema. Además, al encontrarse dentro de la carpeta config, centraliza configuraciones relacionadas con la estructura y organización del esquema de datos utilizado por la aplicación.//