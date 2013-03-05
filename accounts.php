<?
# ¡BeatRock!
require 'Init.php';

# Inicializamos la SDK.
$accounts = new IC([
	'public'	=> '4o6tslz8kgoqrpquf0kmj7gpmf3oh',
	'private'	=> 'xetfahqhplyil1svmslhrkalje7axtx4u2oalut02103dr475p30258rmka'
]);

# Cerramos sesión
# Solo testing, esto no debe estar aquí...
$accounts->Logout();

# No hay ninguna llave de autorización, solicitar una.
# Es decir... redireccionar al usuario a la página de inicio de sesión / confirmación de InfoSmart Cuentas.
if(!$accounts->Ready())
	Core::Redirect($accounts->LoginUrl());

# Mostrar la ID del usuario que ha iniciado sesión (y que ha aceptado que usemos su informacion).
# ¡SOLO LA ID! La información se obtiene con otra función (Más abajo)
echo $accounts->GetUser();

# Mostrar el Array con la información de la aplicación.
# Nota: _r() en BeatRock es lo mismo a poner <pre> print_r() </pre>
_r($accounts->GetApp());
# también podemos solo mostrar la ID de la aplicación:
echo $accounts->GetAppId();

# Con esto obtendremos la respuesta JSON de la información del usuario.
# Es decir... {"id":"1","username":"Kolesias123","firstname":"Iván","lastname":"Bravo Bravo","name":"Iván Bravo Bravo" ... }
echo $accounts->api('/me');

# Con esto obtendremos la respuesta en Array de la información del usuario.
# Nota: _r() en BeatRock es lo mismo a poner <pre> print_r() </pre>
_r($accounts->api('/me', true));

# Con esto (algún día) podríamos publicar un estado.
# ES SOLO UN EJEMPLO, LOS ESTADOS NO ESTARÁN EN INFOSMART CUENTAS (POR AHORA)
$accounts->api('/status', 'POST', ['message' => 'Hoy me siento feliz']);
# también funciona si lo pones así:
$accounts->api('/status', ['message' => 'Hoy me siento feliz'], 'POST');
?>