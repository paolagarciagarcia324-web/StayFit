Partiendo de el archivo roles.php tiene la responsabilidad de gestionar la validación y control de roles dentro de la plataforma StayFitMVC. Su función principal es determinar qué permisos tiene cada usuario según el rol asignado durante el proceso de autenticación. Además, proporciona mecanismos de seguridad que permiten restringir el acceso a determinadas funcionalidades del sistema, garantizando que cada usuario únicamente pueda interactuar con los módulos autorizados para su perfil.

function normalizarRol($rol)

//Esta función fue desarrollada para estandarizar los nombres de los roles provenientes de la base de datos o de la sesión del usuario. Su objetivo es evitar inconsistencias ocasionadas por diferencias de escritura, mayúsculas, espacios o formatos alternativos. Gracias a esta normalización, todas las validaciones de permisos pueden realizarse utilizando una única nomenclatura interna.//

$rol = strtolower(trim((string) $rol));

//En esta línea convierto el valor recibido en una cadena de texto, elimino espacios innecesarios al inicio y al final, y posteriormente transformo todos los caracteres a minúsculas. Esto garantiza que la comparación de roles sea uniforme independientemente de cómo hayan sido almacenados o ingresados.//

$rol = str_replace([' ', '-'], '_', $rol);

//Después reemplazo espacios y guiones por guiones bajos. Esta transformación permite unificar distintos formatos de escritura y mantener una nomenclatura consistente dentro de la aplicación.//

$mapa = [
    'administrador' => 'administrador',
    'admin' => 'administrador',
    'coach' => 'coach',
    'cliente' => 'cliente',
    'cliente_institucional' => 'cliente_institucional',
    'clienteins' => 'cliente_institucional',
];

//En este bloque defino un arreglo asociativo que contiene las equivalencias válidas de cada rol. Gracias a este mapeo puedo convertir diferentes variantes de escritura en una única representación oficial utilizada por el sistema. Por ejemplo, tanto admin como administrador serán interpretados como administrador, mientras que clienteins será convertido a cliente_institucional.//

return $mapa[$rol] ?? $rol;

//Finalmente retorno el valor normalizado utilizando el mapa de equivalencias. Si el rol existe dentro del arreglo, retorno la versión oficial; en caso contrario devuelvo el mismo valor recibido. Esto permite mantener compatibilidad con posibles roles futuros.//

function esAdministrador()

//Esta función se encarga de verificar si el usuario autenticado posee el rol de administrador. Su finalidad es facilitar la validación de permisos en los diferentes módulos del sistema mediante una única función reutilizable.//

$rol = normalizarRol($_SESSION['rol'] ?? '');

//En esta línea obtengo el rol almacenado en la variable de sesión y lo envío a la función normalizarRol() para garantizar que la comparación se realice utilizando el formato oficial definido por el sistema.//

return $rol === 'administrador';

//Finalmente comparo el rol normalizado con el valor administrador. Si ambos coinciden, la función retorna true; de lo contrario retorna false. Esta validación es utilizada para controlar el acceso a los módulos administrativos de la plataforma.//

function esCoach()

//Esta función tiene como propósito verificar si el usuario autenticado pertenece al rol de entrenador o coach. Su utilización permite habilitar funcionalidades específicas asociadas a la gestión de entrenamientos, seguimiento de clientes y administración de contenidos deportivos.//

return normalizarRol($_SESSION['rol'] ?? '') === 'coach';

//Obtengo el rol almacenado en sesión, lo normalizo y posteriormente verifico si corresponde al rol coach. El resultado se devuelve como un valor booleano.//

function esCliente()

//Esta función verifica si el usuario autenticado corresponde a un cliente individual de la plataforma. Su finalidad es controlar el acceso a funcionalidades relacionadas con planes de entrenamiento, seguimiento personal y gestión de información propia.//

return normalizarRol($_SESSION['rol'] ?? '') === 'cliente';

//El proceso consiste en obtener el rol almacenado en sesión, normalizarlo y compararlo con el valor oficial cliente. El resultado determina si el usuario pertenece o no a esta categoría.//

function esClienteInstitucional()

//Esta función permite identificar si el usuario autenticado pertenece a una institución registrada dentro de la plataforma. Este tipo de usuario cuenta con funcionalidades específicas relacionadas con la gestión de grupos, empleados o miembros asociados a una organización.//

return normalizarRol($_SESSION['rol'] ?? '') === 'cliente_institucional';

//Obtengo el rol almacenado en la sesión, lo normalizo utilizando la función correspondiente y verifico si coincide con el valor oficial cliente_institucional. El resultado se utiliza para habilitar o restringir funcionalidades específicas de este perfil.//

function validarAccesoAdministrador()

//Esta función constituye uno de los mecanismos de seguridad más importantes del sistema. Su propósito es garantizar que únicamente los usuarios autenticados con rol de administrador puedan acceder a determinadas secciones protegidas de la aplicación. Esta validación es utilizada principalmente en controladores y módulos administrativos.//

!isset($_SESSION['usuario_id'])

//En esta condición verifico que exista una sesión activa asociada a un usuario autenticado. Si la variable usuario_id no se encuentra definida, significa que no existe una sesión válida dentro del sistema.//

!esAdministrador()

//Además de validar la existencia de una sesión activa, compruebo que el usuario posea efectivamente el rol de administrador mediante la función esAdministrador(). Esto evita que usuarios con otros perfiles accedan a funcionalidades restringidas.//

header('Location: ../../views/auth/accesoDenegado.php');

//Si alguna de las validaciones falla, redirecciono automáticamente al usuario hacia la vista accesoDenegado.php, ubicada dentro del módulo de autenticación. Esta vista informa que el usuario no posee los permisos necesarios para acceder al recurso solicitado.//


Ruta solicitada
views/
└── auth/
    └── accesoDenegado.php

Finalizacon de la ejecucion
exit;

//Después de realizar la redirección, utilizo la instrucción exit para detener completamente la ejecución del script. Esto garantiza que ningún bloque de código posterior pueda ejecutarse después de que el acceso haya sido denegado.//


//Dentro de la arquitectura MVC implementada en StayFitMVC, este archivo se encuentra ubicado en la carpeta config porque contiene configuraciones y reglas relacionadas con la gestión de permisos y roles de usuario. Las funciones definidas aquí son utilizadas principalmente por los controladores para validar accesos antes de ejecutar procesos sensibles. Gracias a este enfoque se centraliza la lógica de autorización, se evita la duplicación de código y se fortalece la seguridad general de la aplicación, garantizando que cada usuario acceda únicamente a los módulos correspondientes a su perfil.//