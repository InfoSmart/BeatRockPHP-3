GENERAL
------------------------

- Se han arreglado varios problemas con la sintaxis y malas traducciones.
- Se ha agregado la carpeta "App" que contiene todos los archivos relacionados a la aplicación tratando de optar el patrón "Modelo Vista Controlador" o MVC.
- Se han agregado los controladores "StaticBase" y "Base" que contienen las funciones principales para la creación de un controlador.
- La función "Error()" ahora solo toma 2 parametros ($code y $message)
- La función "Error()" ahora detecta de forma automática el archivo y la línea donde surgió el error.

BIT
------------------------

- Se agrego la variable $config['beatrock'] en el archivo de configuración para configurar ciertos aspectos del Kernel.
- Se agrego la variable $config['beatrock']['controllers'] en el archivo de configuración para establecer las rutas de busqueda para la carga de los controladores.
- 

SQL
------------------------

- Se agrego el controlador "SQLBase" que contiene las funciones principales para la creación de un controlador relacionado a las consultas de datos.
- Se ha removido el prefijo "query_" de las funciones de procesamiento de datos. (Row, Rows, Assoc, Object, Array, etc)
- La función "query()" ahora usa la función "Keys()" (Anteriormente "Short()") para reemplazar llaves de tipo {KEY} por su constante en PHP.
	- {DP} es lo mismo que usar {DB_PREFIX}
	- {DA} es lo mismo que usar {DB_PREFIX} (Legacy)

View
------------------------

- Se agrego el controlador "View" para cargar vistas de forma individual.