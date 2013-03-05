Última actualización: 05/03/2013

GENERAL
------------------------

- El código es más limpio, ordenado y esta comentado.
- Se han arreglado varios problemas con la sintaxis y malas traducciones.
- La carpeta /Kernel/ ahora puede estar dentro de la carpeta de la aplicación o uno/dos niveles antes de la carpeta de la aplicación sin afectar el funcionamiento de las aplicaciones que lo requieran.
- La carpeta /Kernel/BitRock/ y /Kernel/Languages/ ahora estan dentro de /App/
- Se ha agregado la carpeta "App" que contiene todos los archivos relacionados a la aplicación tratando de optar el patrón "Modelo Vista Controlador" o MVC.
- Los ayudantes (Erroneamente llamados "Modulos" o "Controladores") ahora estan dentro de la carpeta /Helpers/
- Se han agregado los ayudantes "StaticBase" y "Base" que contienen las funciones principales para la creación de un ayudante.
- La función "Error()" ahora solo toma 2 parametros ($code y $message)
- La función "Error()" ahora detecta de forma automática el archivo y la línea donde surgió el error. (FIXME: No siempre...)
- Las variables $P y $G ahora son cadenas inteligentes (objetos Str), es posible pasar distintas funciones de procesamiento de cadenas, por ejemplo: $P['username']->valid(USERNAME) [Core::Valid($P['username', USERNAME])] o $P['username']->upper(); [strtoupper($P['username'])]

BIT
------------------------

- Se agrego la variable $config['beatrock'] en el archivo de configuración para configurar ciertos aspectos del Kernel.
- Se agrego la variable $config['beatrock']['helpers'] en el archivo de configuración para establecer las rutas de busqueda para la carga de los ayudantes.

CORE
------------------------

- La función Core::Valid(str, type) ahora acepta una constante número de tipo EMAIL, USERNAME, IP, CREDIT_CARD, URL, PASSWORD, SUBDOMAIN y DOMAIN en su parametro type
- La función Core::Valid(str, type) ahora puede ser llamada desde la instancia de una cadena inteligente (Str) usando la función valid(type)

SQL
------------------------

- Se agrego el ayudante "SQLBase" que contiene las funciones principales para la creación de un ayudante relacionado a las consultas de datos.
- Se ha removido el prefijo "query_" de las funciones de procesamiento de datos. (Row, Rows, Assoc, Object, Array, etc)
- La función "query()" ahora usa la función "Keys()" (Anteriormente "Short()") para reemplazar llaves de tipo {KEY} por su constante en PHP.
	- {DP} es lo mismo que usar {DB_PREFIX}
	- {DA} es lo mismo que usar {DB_PREFIX} (Legacy)

View
------------------------

- Se agrego el ayudante "View" para cargar vistas de forma individual.

Str
-------------------------

- Se agrego el ayudante "Str" que permite la creación de cadenas inteligentes o de uso con POO.
- Str ahora detecta funciones de PHP de forma automática para pasar sobre la cadena iniciada.

Zip
------------------------

- Ahora se usa [ZipArchive](http://www.php.net/manual/es/class.ziparchive.php "ZipArchive")

Tpl
------------------------

- La función "jQuery" ahora se llama "AddjQuery"
- La función "Meta" ahora se llama "AddMeta"
- La función "AddStyle" ahora se llama "AddLink"
- La función "Style" ahora se llama "AddLocalStyle"
- La función "Script" ahora se llama "AddLocalScript"
- La función "Stuff" ahora se llama "AddStuff"
- La función "MoreHTML" ahora se llama "AttrHTML"
- La función "MoreHead" ahora se llama "AttrHead"
- La función "JavaAction" ahora se llama "JSAction" (Si, hay personas que les molesta "Java")

- La función "Process" ahora puede ser usada con "new View()"
- La función "SetParams", "SetLang", "Compress" ahora son parte del ayudante "View"


Futuras implementaciones
------------------------

- Espacio de nombres (Iván: No lo considero necesario... encerio.)
- PostgreSQL.
- Controladores (Iván: Si, esos que sirven para acceder a ciertas partes de la aplicación usando funciones [¿Quien invento esto?])
- Jade. (Dudo mucho que la implementación actual siga funcionando)