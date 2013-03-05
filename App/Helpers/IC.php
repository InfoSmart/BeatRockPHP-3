<?
// Acción ilegal
if(!defined('BEATROCK'))
	exit;

class IC
{
	## Ubicaciones
	private $PATH = array(
		'main' 	=> 'http://localhost/accounts',
		'api'	=> 'http://localhost/accounts/connect/api'
	);

	## Llave pública
	private $publicKey;
	## Llave privada.
	private $privateKey;

	## Llave de autorización.
	private $authorizeKey;

	## ID del usuario que ha iniciado sesión.
	private $userId;
	## ID de la aplicación.
	private $appId;
	## Información de la aplicación.
	private $app;

	## Constructor
	## $data - array con la información de preparación.
	function __construct($data)
	{
		# Ajustamos los datos a la clase.
		$this->SetPublicKey($data['public']);
		$this->SetPrivateKey($data['private']);
	}

	## Ajustar la llave pública de la aplicación.
	function SetPublicKey($key)
	{
		$this->publicKey = $key;
	}

	## Ajustar la llave privada de la aplicación.
	function SetPrivateKey($key)
	{
		$this->privateKey = $key;
	}

	## Ajustar la llave de autorización.
	function SetAuthKey($key = '')
	{
		if( empty($key) )
			$key = _SESSION('acc_authorize');

		$this->authorizeKey = $key;
	}

	# Ajustar la ID del usuario y aplicación.
	function SetMain($userId, $app)
	{
		$this->userId 	= $userId;
		$this->appId 	= $app['id'];
		$this->app 		= $app;
	}

	## Obtener la llave de autorización.
	function GetAuthorizeKey()
	{
		return $this->authorizeKey;
	}

	## Obtener la ID del usuario.
	function GetUser()
	{
		return $this->userId;
	}

	## Obtener la ID de la aplicacion.
	function GetAppId()
	{
		return $this->appId;
	}

	## Obtener la información de la aplicación.
	function GetApp()
	{
		return $this->app;
	}

	## Checar si la llave de autorización se ha proporcionado.
	function Check()
	{
		global $R;

		# Guardar en una sesión.
		if(!empty($R['authorize']))
			_SESSION('acc_authorize', $R['authorize']);
		
		# Ajustar
		$this->SetAuthKey();
		$this->GetMainInfo();
	}

	## Obtener la información esencial.
	function GetMainInfo()
	{
		$result = $this->api('/main', true);
		$this->SetMain($result['userId'], $result['app']);
	}

	## ¿Estamos listos?
	function Ready()
	{
		# Hacemos un chequeo.
		$this->Check();

		return (empty($this->authorizeKey)) ? false : true;
	}

	## Devolver la dirección para iniciar sesión / confirmar.
	function LoginUrl()
	{
		# Juntamos la información necesaria.
		$params['public'] = $this->publicKey;
		$params['return'] = PATH_NOW;

		return $this->PATH['main'] . '/connect/authorize?' . http_build_query($params);
	}

	## Cerrar sesión.
	function Logout()
	{
		_DELSESSION('acc_authorize');
	}

	## Hacer llamadas a la API.
	## - $page: Página a solicitar.
	## - $data: array con los datos a enviar.
	## - $method: Metodo de solicitud (GET o POST)
	function api()
	{
		# Argumentos disponibles.
		$args = func_get_args();

		# Cada uno en su lugar.
		$page 	= $args[0];
		$data 	= ( is_array($args[1]) ) ? $args[1] : $args[2];
		$method = ( is_string($args[1]) ) ? strtoupper($args[1]) : $args[2];

		if( is_bool($args[1]) )
			$json = $args[1];

		if( is_bool($args[2]) )
			$json = $args[2];

		if( is_bool($args[3]) )
			$json = $args[3];

		# Debe haber un metodo.
		if( empty($method) )
			$method = 'GET';

		# Petición por GET
		if($method == 'GET')
			$return = $this->Get($page, $data);

		# Petición por POST
		if($method == 'POST')
			$return = $this->Get($page, $data);

		# Descodificar el JSON desde ya
		if($json == true)
			return json_decode($return, true);

		return $return;
	}

	## Hacer una llamada a la API por medio de GET
	function Get($page, $data = array(), $params = array())
	{
		# La API necesita la clave privada de la app y la llave de autorización.
		$data['authorize'] 	= $this->authorizeKey;
		$data['private'] 	= $this->privateKey;

		$params = http_build_query($data, null, '&');

		$curl = new Curl($this->PATH['api'] . $page . '?' . $params, array(
			'agent' => 'AccountsAPI 1.0'
		));

		$result = $curl->Get();

		if($curl->errno == 60)
		{
			$dirname 				= dirname(__FILE__);
			$params[CURLOPT_CAINFO] = $dirname . '/accounts_ca.crt';

			return $this->Get($page, $data, $params);
		}

		return $result;
	}

	## Hacer una llamada a la API por medio de POST
	function Post($page, $data = array(), $params = array())
	{
		# La API necesita la clave privada de la app y la llave de autorización.
		$data['authorize'] 	= $this->authorizeKey;
		$data['private'] 	= $this->privateKey;

		$curl = new Curl($this->PATH['api'] . $page . $params, array(
			'agent' => 'AccountsAPI 1.0'
		));

		$result = $curl->Post($data);

		if($curl->errno == 60)
		{
			$dirname 				= dirname(__FILE__);
			$params[CURLOPT_CAINFO] = $dirname . '/accounts_ca.crt';

			return $this->Post($page, $data, $params);
		}

		return $result;
	}
}
?>