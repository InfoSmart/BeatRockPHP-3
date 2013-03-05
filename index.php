<?
require 'Init.php';

# Variables de prueba usados en la vista.
$hello 					= 'Hola querido mundo';
$hell['test']           = 'Esto es una prueba';

# Aquí definimos la vista que usaremos.
# Las vistas estan ubicadas en: /App/Views/
# Por lo tanto aquí le dicemos que usaremos la vista: /App/Views/index.html
$page['id'] 			= 'index';

# Aquí definimos el nombre de la página actual.
# Esta se mostrará al lado del nombre de nuestra aplicación.
# En este caso se mostraría: Mi página web - Inicio
$page['name'] 			= 'Inicio';

echo $G['web']->upper();

# Aquí definimos la configuración de traducción para esta página.
# [lang] = El lenguaje que mostraremos, en este ejemplo tomaría lo que este en ?lang=
# o directamente el lenguaje del navegador del usuario.
# Recomendación: Puede poner esta variable en el archivo: /App/Setup.php para que lo tome en
# cuenta en todas las páginas de nuestra aplicación.
$page['lang'] 			= ( !empty($G['lang']) ) ? $G['lang'] : LANG;

# [lang.sections] = Un array con las secciones de traducción usadas para esta página.
# Puede obtener más información de esto en: /App/Languages/es/welcome.json
$page['lang.sections']  = array('page.welcome');

# [lang.live] = Establece si esta página tendrá activado la "traducción en tiempo real"
# Para hacer que funcione necesita tener un código JavaScript en la página, puede obtener
# ese código en: /resources/app/js/functions.page.js
$page['lang.live']		= true;
?>