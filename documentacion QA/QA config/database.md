<?php

class Database
{

//En este bloque declaro la clase Database, la cual tiene la responsabilidad de gestionar la conexión entre la aplicación StayFitMVC y la base de datos MySQL. Decidí encapsular toda la lógica de conexión dentro de una clase para centralizar la configuración y facilitar su reutilización desde los diferentes modelos del sistema. Gracias a esta estructura, cualquier módulo que necesite acceder a la base de datos puede hacerlo utilizando una única fuente de conexión.//

private $host = "127.0.0.1";
private $port = "3320";
private $db_name = "stayfit";
private $username = "root";
private $password = "";

//En este bloque defino las propiedades privadas que almacenan los parámetros necesarios para establecer la conexión con la base de datos. La variable $host indica la dirección del servidor donde se encuentra alojada la base de datos, mientras que $port especifica el puerto de comunicación utilizado por MySQL. La variable $db_name contiene el nombre de la base de datos principal del proyecto, denominada stayfit. Por su parte, $username y $password almacenan las credenciales de acceso. Estas propiedades se declaran como privadas para evitar que sean modificadas directamente desde otras partes de la aplicación, garantizando un mayor control sobre la configuración de conexión.//

public $conn;

//En esta línea declaro la variable pública $conn, cuya función es almacenar el objeto de conexión generado mediante PDO. Una vez establecida la conexión, esta variable permitirá que los modelos y demás componentes del sistema puedan ejecutar consultas SQL sobre la base de datos. La propiedad es pública para facilitar el acceso al objeto de conexión cuando sea retornado por el método correspondiente.//

public function conectar()
{

//En este bloque declaro el método conectar(), encargado de crear y devolver una conexión activa con la base de datos. Este método constituye el punto central de acceso a la información almacenada en MySQL y será utilizado por los modelos para realizar operaciones de consulta, inserción, actualización y eliminación de datos. Implementé este método para evitar repetir el mismo código de conexión en múltiples archivos del proyecto.//

$this->conn = null;

//Antes de intentar crear una nueva conexión, reinicio el valor de la variable $conn asignándole null. Esto garantiza que no existan referencias anteriores a conexiones obsoletas y permite iniciar el proceso de conexión desde un estado limpio y controlado.//

try {

Utilizo una estructura try para controlar posibles errores durante el proceso de conexión. Esta práctica mejora la robustez de la aplicación, ya que cualquier excepción generada por PDO podrá ser capturada y gestionada adecuadamente mediante el bloque catch, evitando fallos inesperados en el sistema.//

$dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

//En esta línea construyo el DSN (Data Source Name), que es una cadena de configuración utilizada por PDO para identificar el tipo de base de datos y los parámetros necesarios para conectarse. En este caso especifico que la base de datos es MySQL, indicando el servidor, el puerto, el nombre de la base de datos y el conjunto de caracteres utf8mb4. La utilización de utf8mb4 garantiza la compatibilidad con caracteres especiales, acentos, símbolos y emojis, mejorando la integridad de los datos almacenados.//

$this->conn = new PDO($dsn, $this->username, $this->password);

//En este bloque instancio un objeto PDO utilizando el DSN previamente configurado y las credenciales de acceso definidas en la clase. PDO fue seleccionado porque proporciona una capa de abstracción para el acceso a bases de datos, mejora la seguridad mediante consultas preparadas y facilita la portabilidad del código. El objeto generado queda almacenado en la variable $conn para su posterior utilización.//

$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Después de establecer la conexión, configuro el atributo PDO::ATTR_ERRMODE con el valor PDO::ERRMODE_EXCEPTION. Esto permite que cualquier error relacionado con la base de datos sea tratado como una excepción, facilitando su detección, depuración y manejo dentro de la aplicación. Gracias a esta configuración, los errores pueden ser identificados de forma más clara durante el desarrollo y mantenimiento del sistema.//

$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

//En esta línea establezco el modo de recuperación de datos por defecto como PDO::FETCH_ASSOC. Esto significa que los resultados obtenidos de las consultas SQL serán devueltos en forma de arreglos asociativos, utilizando los nombres de las columnas como claves. Esta configuración facilita la lectura de los datos dentro de los modelos y controladores, mejorando la claridad y organización del código.//

return $this->conn;

//Una vez creada y configurada correctamente la conexión, retorno el objeto PDO almacenado en la variable $conn. De esta manera, cualquier modelo que invoque el método conectar() podrá utilizar la misma conexión para interactuar con la base de datos y ejecutar operaciones relacionadas con la gestión de información del sistema.//

catch (PDOException $e) {

//Este bloque se ejecuta únicamente cuando ocurre una excepción durante el proceso de conexión. Su función es capturar cualquier error generado por PDO y evitar que la aplicación continúe ejecutándose con una conexión inválida. Esto contribuye a la estabilidad y confiabilidad del sistema.//

die("Error de conexión: " . $e->getMessage());

//Si la conexión falla, finalizo la ejecución del programa mediante la función die() y muestro el mensaje de error proporcionado por la excepción capturada. Esta implementación resulta útil durante el desarrollo porque permite identificar rápidamente la causa del problema. Sin embargo, en un entorno de producción sería recomendable registrar el error en un archivo de logs y mostrar un mensaje genérico al usuario por razones de seguridad.//

}

//En conclusion, dentro de la arquitectura MVC implementada en StayFitMVC, este archivo se encuentra ubicado en la carpeta config porque contiene toda la configuración relacionada con la conexión a la base de datos. Los modelos del sistema dependen de esta clase para obtener acceso a la información almacenada en MySQL. De esta manera, se mantiene una separación adecuada de responsabilidades: la configuración permanece en config, los modelos gestionan los datos, los controladores procesan la lógica de negocio y las vistas presentan la información al usuario.//