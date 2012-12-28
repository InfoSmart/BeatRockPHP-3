<?
#####################################################
## 					 BeatRock
#####################################################
## Framework avanzado de procesamiento para PHP.
#####################################################
## InfoSmart © 2012 Todos los derechos reservados.
## http://www.infosmart.mx/
#####################################################
## http://beatrock.infosmart.mx/
#####################################################

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;	

class Users
{
	// Obtener la edad dependiendo del año.
	// - $year: Año - Tiempo Unix - Fecha completa
	// - $pos (int): Posición desde 0 donde se encuentra el año en formatos de tipo 24/05/1995 o con -
	static function Age($year, $pos = 2)
	{
		$the = _l('%the%', 'global');

		if(Contains($year, '-'))
		{
			$split 	= explode('-', $year);
			$year 	= $year[$pos];
		}

		if(Contains($year, '/'))
		{
			$split 	= explode('/', $year);
			$year 	= $split[$pos];
		}

		if(Contains($year, $the))
		{
			$split 	= explode($the, $year);
			$year 	= trim($split[$pos]);
		}

		if($year > 10000)
			$year = date('Y', $year);

		$actual = date('Y');

		if(!is_numeric($year) || $year < 1990 || $year > $actual)
			return $year;
			
		return ($actual - $year);
	}
	
	// Obtener el número de usuarios registrados.
	// - $online (Bool): ¿Usuarios online?
	// - $online_time (Int): Tiempo de la última actividad para considerarse online.
	static function Count($online = false, $online_time = 0)
	{
		if($online)
		{
			if($online_time == 0)
				$online_time = (time() - (8 * 60));

			return Query('users')->Select('null')->Add('lastonline', $online_time, 'WHERE', '>')->Rows();
		}
		else
			return Query('users')->Select('null')->Rows();
	}
	
	// Obtener una lista de los usuarios online.
	// - $limit (Int): Limite de usuarios.
	// - $online_time (Int): Tiempo de la última actividad para considerarse online.
	static function OnlineUsers($limit = 0, $online_time = 0)
	{
		if($online_time == 0)
			$online_time = (time() - (8 * 60));
		
		$q = Query('users')->Select()->Add('lastonline', $online_time, 'WHERE', '>');
		
		if($limit > 0)
			$q->Order('RAND()')->Limit($limit);
			
		return $q->Run();
	}

	// Verificar si un usuario se encuentra online.
	// - $id: ID - Nombre de usuario - Correo electrónico.
	// - $t (Bool): ¿Devolver en texto?
	static function IsOnline($id = ME_ID, $text = false, $online_time = 0)
	{
		if($online_time == 0)
			$online_time = (time() - (8 * 60));

		$online = self::Data('lastonline', $id);
		
		if($online >= $online_time)
			return ($t == true) ? 'Online' : true;

		return ($t == true) ? 'Offline' : false;
	}
	
	// Verificar si cierta información ya existe en algún usuario.
	// Por ejemplo para válidar si un nombre de usuario ya esta registrado.
	// - $data: Información a verificar.
	// - $type (email, username, etc): Campo donde se hara la verificación.
	static function Exist($data, $type = 'email')
	{
		$q = Query('users')->Select('null')->Add($type, $data)->Limit()->Rows();
		return ($q > 0) ? true : false;
	}

	// Verificar si un usuario ya existe.
	// NOTA: Esto verifica si la ID existe en los campos: id, username y email
	// Opcionalmente puedes agregar otro campo con el parametro $row (Riesgo de seguridad)
	// - $id: Información a verificar.
	// - $row: Campo extra a verificar.
	static function UserExist($id, $row = '')
	{
		$q = Query('users')->Select('null')->Add('id', $id)->Add('username', $id, 'OR')->Add('email', $id, 'OR');

		if(!empty($row))
			$q = $q->Add($row, $id, 'OR');

		$q = $q->Limit()->Rows();
		return ($q > 0) ? true : false;
	}
	
	// Iniciar sesión.
	// Realiza los procesos necesarios para un inicio de sesión seguro.
	// - $id (Int): ID del usuario.
	// - $cookie (Bool): ¿Recordar el usuario en esta PC?
	static function Login($id, $cookie = true)
	{
		_SESSION('login_id', $id);
		
		if($cookie)
		{
			$secret 	= self::Data('secret', $id);
			$cookie 	= Core::Encrypt(md5($id . $secret . time()));
			
			self::UpdateData('cookie', $cookie, $id);
			_COOKIE('session', $cookie);
		}
		
		self::Enter($id);

		return ($cookie) ? $cookie : true;
	}
	
	// Desconectarse.
	// Realiza los procesos necesarios para cerrar sesión.
	// - $force (Bool) - ¿Destruir todas las sesiones?
	static function Logout($force = false)
	{
		if($force)
			session_destroy();
		else
			_DELSESSION('login_id');
		
		_DELCOOKIE('session');

		self::UpdateData('cookie', '');
		self::Out(ME_ID);
	}
	
	// Actualiza la información del usuario al iniciar sesión.
	// - $id: ID - Nombre de usuario - Correo electrónico.
	static function Enter($id)
	{
		self::Update(array(
			'lastaccess' => time(),
			'lastonline' => time(),
			'ip' => IP
		), $id);
	}
	
	// Actualiza la información del usuario al cerrar sesión.
	// - $id: ID - Nombre de usuario - Correo electrónico.
	static function Out($id)
	{
		self::Update(array(
			'lastonline' => '0',
			'ip' => IP
		), $id);
	}
	
	// Agregar un nuevo usuario.
	// - $username: Nombre de usuario.
	// - $password: Contraseña en texto plano.
	// - $name: Nombre real.
	// - $email: Correo electrónico.
	// - $birthday: Fecha de nacimiento.
	// - $photo (Dirección, Array): Foto de perfil.
	// - $auto (Bool): ¿Auto iniciar sesión?
	// - $params (Array): Otros campos a agregar en el registro.
	static function NewUser($username, $password, $name, $email, $birthday = '', $photo = '', $auto = true, $params = '')
	{
		if(is_array($photo))
		{
			if(!is_numeric($photo['size']))
				$photo['size'] 		= 80;
			
			if(empty($photo['rating']))
				$photo['rating'] 	= 'g';
				
			$photo = self::GetGravatar($email, '', $photo['size'], $photo['default'], $photo['rating']);
		}

		if(!empty($password))
			$password = Core::Encrypt($password);
		
		$data = array(
			'username' 			=> $username,
			'password' 			=> $password,
			'name' 				=> $name,
			'email' 			=> $email,
			'photo' 			=> $photo,
			'birthday' 			=> $birthday,
			'account_creation' 	=> time(),
			'ip' 				=> IP,
			'reg_ip'			=> IP,
			'browser' 			=> BROWSER,
			'agent' 			=> _F(AGENT),
			'os' 				=> OS,
			'country'			=> COUNTRY,
			'secret'			=> md5(sha1(time()))
		);
		
		if(is_array($params))
			$data = array_merge($params, $data);
		
		Insert('users', $data);
		$id = last_id();
		
		if($auto)
			self::Login($id);
			
		return $id;
	}
	
	// Agregar un nuevo servicio de conexión.
	// - $id: Identificación del usuario en el servicio.
	// - $service (facebook, twitter, google, steam): Código del servicio.
	// - $name: Nombre del usuario en el servicio.
	// - $service_hash: Hash de enlace con el usuario.
	// - $info: Información obtenida del servicio.
	static function NewService($id, $service, $name, $info = '')
	{
		$hash_ready = false;

		while(!$hash_ready)
		{
			$hash 	= Core::Random(80);
			$q 		= Query('users_services')->Select('null')->Add('service_hash', $hash)->Rows();

			if($q == 0)
				$hash_ready = true;
		}
		
		Insert('users_services', array(
			'identification' 	=> $id,
			'service' 			=> $service,
			'name' 				=> $name,
			'service_hash' 		=> $hash,
			'info' 				=> _F($info, false),
			'date' 				=> time()
		));
		
		return $hash;
	}
	
	// Verificar si un servicio ya ha sido registrado y tiene un enlace hacia algún usuario.
	// - $id: Identificación del usuario en el servicio.
	// - $service (facebook, twitter, google, steam): Servicio.
	static function ServiceExist($id, $service)
	{
		$q = Query('users_services')->Select('null')->Add('identification', $id)->Add('service', $service, 'AND')->Limit()->Rows();
		return ($q > 0) ? true : false;
	}
	
	// Actualizar información masiva de un servicio.
	// - $data (Array): Información a actualizar.
	// - $id: ID del servicio.
	static function UpdateService($data, $id)
	{
		Query('users_services')->Update($data)->Add('id', $id)->Limit()->Run();
	}
	
	// Obtener la información de un servicio.
	// - $id: Identificación del usuario en el servicio.
	// - $service (facebook, twitter, google, steam): Servicio.
	static function Service($id, $service)
	{
		q("SELECT * FROM {DA}users_services WHERE (id = '$id' OR identification = '$id') AND service = '$service' LIMIT 1");
		return (num_rows() > 0) ? fetch_assoc() : false;
	}
	
	// Obtener la información del usuario con un servicio.
	// - $hash: Hash del enlace.
	static function UserService($hash)
	{
		Query('users')->Select()->Add('service_hash', $hash)->Limit()->Run();
		return (num_rows() > 0) ? fetch_assoc() : false;
	}

	// Obtener los servicios enlazados a un usuario.
	// - $hash: Hash del enlace.
	static function GetServices($hash)
	{
		$q = Query('users_services')->Select()->Add('service_hash', $hash)->Order('id')->Run();
		return (num_rows() > 0) ? $q : false;
	}

	// Eliminar un servicio.
	// $id: Identificación del servicio.
	static function DeleteService($id)
	{
		Query('users_services')->Delete()->Add('id', $id)->Limit()->Run();
	}
	
	// Actualizar información masiva de un usuario.
	// - $data (Array): Información a actualizar.
	// - $id: ID - Nombre de usuario - Correo electrónico - Valor de la columna personalizada.
	// - $row: Columna personalizada.
	static function Update($data, $id = ME_ID, $row = 'id')
	{
		if(!is_numeric($id))
			$id = self::Data('id', $id, $row);

		Query('users')->Update($data)->Add('id', $id)->Limit()->Run();
	}
	
	// Actualizar información de un usuario.
	// - $data: Campo a actualizar.
	// - $value: Nuevo valor.
	// - $id: ID - Nombre de usuario - Correo electrónico - Valor de la columna personalizada.
	// - $row: Columna personalizada.
	static function UpdateData($data, $value, $id = ME_ID, $row = 'id')
	{
		self::Update(array($data => $value), $id, $row);
	}
	
	// Obtener cierta información de un usuario.
	// - $data: Información a obtener.
	// - $id: ID - Nombre de usuario - Correo electrónico - Valor de la columna personalizada.
	// - $row: Columna personalizada.
	static function Data($data, $id = ME_ID, $row = 'id')
	{
		return Query('users')->Select($data)->Add('id', $id)->Add('username', $id, 'OR')->Add('email', $id, 'OR')->Add($row, $id, 'OR')->Limit()->Get($data);
	}
	
	// Obtener cierta información de un usuario a partir de una sola columna.
	// Este método proporciona más protección y exactitud.
	// - $data: Información a obtener.
	// - $id: Valor de la columna personalizada.
	// - $row: Columna personalizada.
	static function DataOnly($data, $id = ME_ID, $row = 'id')
	{
		return Query('users')->Select($data)->Add($row, $id)->Limit()->Get($data);
	}
	
	// Obtener TODA la información de un usuario.
	// - $id: ID - Nombre de usuario - Correo electrónico - Valor de la columna personalizada.
	// - $row: Columna personalizada.
	static function User($id = ME_ID, $row = 'id')
	{
		Query('users')->Select()->Add('id', $id)->Add('username', $id, 'OR')->Add('email', $id, 'OR')->Add($row, $id, 'OR')->Limit()->Run();
		return (num_rows() > 0) ? fetch_assoc() : false;
	}

	// Obtener TODA la información de un usuario a partir de una sola columna.
	// Este método proporciona más protección y exactitud.
	// - $id: Valor de la columna personalizada.
	// - $row: Columna personalizada.
	static function GetUser($id = ME_ID, $row = 'id')
	{
		Query('users')->Select()->Add($row, $id)->Limit()->Run();
		return (num_rows() > 0) ? fetch_assoc() : false;
	}
	
	// Validar la identificación y la contraseña.
	// - $id: Identificación (Nombre de usuario - Correo electrónico)
	// - $password: Contraseña en texto plano.
	// - $service (String/Bool): Servicio con el que se inicia sesión.
	static function Verify($id, $password, $service = false)
	{
		if(!empty($password))
			$password = Core::Encrypt($password);

		$query = "SELECT null FROM {DA}users WHERE (username = '$id' OR email = '$id') AND password = '$password' ";

		// TODO: ¿WTF?
		if($service !== false)
			$query .= "AND service = '$service' ";

		$query .= 'LIMIT 1';	
		$q 		= Rows($query);

		return ($q > 0) ? true : false;
	}
	
	// Buscar una sesión activa.
	// - $loginId: ID del usuario a finjir inicio de sesión.
	static function Session($loginId = '')
	{
		if(empty($loginId))
			$loginId = _F(_SESSION('login_id'));
		
		if(empty($loginId) OR !self::Exist($loginId, 'id'))
			goto nosession;
		
		global $me, $ms;
			
		$me = self::User($loginId);		
		$ms = @json_decode(_SESSION('service_info'), true);
		
		foreach($me as $key => $value)
			Tpl::Set('me_' . $key, $value);
			
		if(is_array($ms))
		{
			foreach($ms as $key => $value)
				Tpl::Set('ms_' . $key, $value);
		}
		
		define('ME_ID', 		$me['id']);
		define('ME_RANK', 		$me['rank']);
		define('ME_USERNAME', 	$me['username']);
		define('ME_NAME', 		$me['name']);
		define('LOG_IN', 		true);
		
		self::Update(array(
			'lastonline' 	=> time(),
			'ip' 	=> IP,
			'browser' 		=> BROWSER,
			'agent' 		=> _F(AGENT),
			'lasthost' 		=> HOST,
			'os' 			=> OS
		));
		
		return true;
		
		nosession: 
		{
			define('ME_ID', 		'0');
			define('ME_RANK', 		'0');
			define('ME_USERNAME', 	'');
			define('ME_NAME', 		'');
			define('LOG_IN', 		false);			
			return false;
		}
	}
	
	// Buscar una cookie activa.
	// Iniciar sesión automaticamente al volver.
	static function Cookie()
	{
		if(LOG_IN)
			return;

		$session 		= _F(_COOKIE('session'));
		$userId 		= self::DataOnly('id', $session, 'cookie');

		if(empty($session))
			return;
		
		if(!self::Exist($session, 'cookie') OR !is_numeric($userId))
		{
			self::Logout();
			return false;
		}
		
		self::Login($userId);
		Client::SavePost();

		Core::Redirect(PATH_NOW);
	}
	
	// Obtener el "Gravatar" de un correo electrónico.
	// - $email: Correo electrónico.
	// - $to: Ruta del archivo de destino donde guardar la imagen.
	// - $size (Int): Tamaño de ancho y altura.
	// - $default (Ruta, mm, identicon, monsterid, wavatar, retro): Imagen predeterminada en caso de que el usuario no use el servicio de Gravatar.
	// - $rating (g, pg, r, x): Clasificación máxima de las imagenes.
	static function GetGravatar($email, $to = '', $size = 40, $default = 'mm', $rating = 'g')
	{
		$email 		= md5(strtolower(trim($email)));
		$default 	= urlencode($default);
		
		$gravatar 	= "https://www.gravatar.com/avatar/$email?s=$size&d=$default&r=$rating";
		
		if(!empty($to))
			Io::Write($to, $gravatar);
			
		return $gravatar;
	}
}
?>