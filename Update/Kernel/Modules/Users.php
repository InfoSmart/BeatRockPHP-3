<?php
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2012 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;	

class Users
{
	// Obtener la edad de un usuario por medio del año especificado.
	// - $year: Año.
	static function Age($year)
	{
		if(!is_numeric($year) || $year < 1990 || $year > date('Y'))
			return $year;
			
		return (date('Y') - $year);
	}
	
	// Contar los usuarios o usuarios online.
	// - $online (Bool): ¿Usuarios online?
	static function Count($online = false)
	{
		if($online)
		{
			global $date;
			return Rows("SELECT null FROM {DA}users WHERE lastonline > '$date[f]'");
		}
		else
			return Rows("SELECT null FROM {DA}users");
	}
	
	// Obtener una lista de los usuarios online.
	// - $limit (Int): Limite de usuarios.
	static function OnlineUsers($limit = 0)
	{
		global $date;
		
		$q = "SELECT * FROM {DA}users WHERE lastonline > '$date[f]'";
		
		if($limit > 0)
			$q .= " ORDER BY RAND() LIMIT $limit";
			
		return query($q);
	}
	
	// Verificar si un dato ya existe.
	// - $str: Dato a verificar.
	// - $type (email, username): Tipo de verificación.
	static function Exist($str, $type = 'email')
	{			
		$q = Rows("SELECT null FROM {DA}users WHERE $type = '$str' LIMIT 1");	
		return ($q > 0) ? true : false;
	}
	
	// Iniciar sesión.
	// - $id (Int): ID del usuario.
	// - $cookie (Bool): ¿Recordar el usuario en esta PC?
	static function Login($id, $cookie = true)
	{
		Core::theSession('login_id', $id);
		
		if($cookie)
		{
			$password 	= self::Data('password', $id);
			$cookie 	= Core::Encrypte(md5($id . $password));
			
			self::UpdateData('cookie_session', $cookie, $id);
			Core::theCookie('session', $cookie);
		}
		
		self::Enter($id);
	}
	
	// Desconectarse.
	static function Logout($force = false)
	{
		if($force)
			session_destroy();
		else
		{
			Core::delSession('login_id');
			Core::delCookie('session');
		}

		self::UpdateData('cookie_session', '');
		self::Out(MY_ID);
	}
	
	// Actualizar datos de conexión.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	static function Enter($id)
	{
		self::Update(array(
			'lastaccess' => time(),
			'lastonline' => time(),
			'ip_address' => IP
		), $id);
	}
	
	// Actualizar datos de desconexión.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	static function Out($id)
	{
		self::Update(array(
			'lastonline' => '0',
			'ip_address' => IP
		), $id);
	}
	
	// Agregar un nuevo usuario.
	// - $username: Nombre de usuario.
	// - $password: Contraseña en texto plano.
	// - $name: Nombre real.
	// - $email: Correo electrónico.
	// - $birthday: Fecha de nacimiento.
	// - $photo (Dirección, Array): Foto de perfil.
	// - $auto (Bool): ¿Auto conectarse?
	// - $params (Array): Otros valores...
	static function NewUser($username, $password, $name, $email, $birthday = '', $photo = '', $auto = true, $params = '')
	{
		if(is_array($photo))
		{
			if(!is_numeric($photo['size']))
				$photo['size'] 		= 80;
			
			if(empty($photo['rating']))
				$photo['rating'] 	= "g";
				
			$photo = self::GetGravatar($email, '', $photo['size'], $photo['default'], $photo['rating']);
		}

		if(!empty($password))
			$password = Core::Encrypte($password);
		
		$data = array(
			'username' 			=> $username,
			'password' 			=> Core::Encrypte($password),
			'name' 				=> $name,
			'email' 			=> $email,
			'photo' 			=> $photo,
			'birthday' 			=> $birthday,
			'account_birthday' 	=> time(),
			'ip_address' 		=> IP,
			'reg_ip_address'	=> IP,
			'browser' 			=> BROWSER,
			'agent' 			=> AGENT,
			'os' 				=> OS
		);
		
		if(is_array($params))
			$data = array_merge($params, $data);
		
		Insert('users', $data);
		$id = mysql_insert_id();
		
		if($auto)
			self::Login($id);
			
		return $id;
	}
	
	// Agregar un nuevo servicio.
	// - $id: Identificación de servicio.
	// - $service (facebook, twitter, google): Servicio.
	// - $name: Nombre de identificación de usuario.
	// - $info: Información obtenida del servicio.
	static function NewService($id, $service, $name, $info)
	{
		$hash = Core::Random(80);
		
		Insert('users_services', array(
			'identification' 	=> $id,
			'service' 			=> $service,
			'name' 				=> $name,
			'user_hash' 		=> $hash,
			'info' 				=> _f($info, false),
			'date' 				=> time()
		));
		
		return $hash;
	}
	
	// ¿Existe el servicio?
	// - $id: Identificación del servicio.
	// - $service (facebook, twitter, google): Servicio.
	static function ServiceExist($id, $service)
	{
		$q = Rows("SELECT null FROM {DA}users_services WHERE identification = '$id' AND service = '$service' LIMIT 1");
		return ($q > 0) ? true : false;
	}
	
	// Actualizar varios datos de un servicio.
	// - $data (Array): Datos a actualizar.
	// - $id: ID del servicio.
	static function UpdateService($data, $id)
	{
		Update('users_services', $data, array(
			"id = '$id'"
		), 1);
	}
	
	// Obtener todos los datos de un servicio.
	// - $id: Identificación del servicio.
	// - $service (facebook, twitter, google): Servicio.
	static function Service($id, $service)
	{
		$q = query("SELECT * FROM {DA}users_services WHERE (id = '$id' OR identification = '$id') AND service = '$service' LIMIT 1");
		return (num_rows() > 0) ? fetch_assoc() : false;
	}
	
	// Obtener los datos de un usuario con servicio.
	// - $hash: Hash del servicio.
	// - $service: Servicio.
	static function UserService($hash, $service)
	{
		$q = query("SELECT * FROM {DA}users WHERE user_hash = '$hash' AND service = '$service' LIMIT 1");
		return (num_rows() > 0) ? fetch_assoc() : false;
	}

	// Obtener los servicios conectados a un usuario.
	// - $hash: Hash del usuario.
	static function GetServices($hash)
	{
		$q = query("SELECT * FROM {DA}users_services WHERE user_hash = '$hash' ORDER BY id DESC");
		return (num_rows() > 0) ? $q : false;
	}

	// Eliminar un servicio.
	// $id: Identificación del servicio.
	static function DeleteService($id)
	{
		query("DELETE FROM {DA}users_services WHERE id = '$id' LIMIT 1");
	}
	
	// ¿Existe el usuario?
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro a verificar existencia.
	static function UserExist($id, $row = 'id')
	{
		$q = Rows("SELECT null FROM {DA}users WHERE id = '$id' OR username = '$id' OR email = '$id' OR $row = '$id' LIMIT 1");		
		return ($q > 0) ? true : false;
	}
	
	// Actualizar varios datos de un usuario.
	// - $data (Array): Datos a actualizar.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	static function Update($data, $id = MY_ID, $row = 'id')
	{
		if(!is_numeric($id))
			$id = self::Data('id', $id, $row);
			
		Update('users', $data, array(
			"id = '$id'"
		), 1);
	}
	
	// Actualizar dato de un usuario.
	// - $data: Parametro a actualizar.
	// - $new: Valor nuevo.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	static function UpdateData($data, $new, $id = MY_ID, $row = 'id')
	{
		if(!is_numeric($id))
			$id = self::Data('id', $id, $row);
			
		Update('users', array(
			$data => $new
		), array(
			"id = '$id'"
		), 1);
	}
	
	// Obtener el dato de un usuario.
	// - $data: Parametro a obtener.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	static function Data($data, $id = MY_ID, $row = 'id')
	{			
		return Get("SELECT $data FROM {DA}users WHERE id = '$id' OR username = '$id' OR email = '$id' OR $row = '$id' LIMIT 1", $data);
	}
	
	
	// Obtener el dato de un usuario con solo una condición.
	// - $data: Parametro a obtener.
	// - $id: Valor del usuario a cumplir la condición.
	// - $row: Parametro de condición.
	static function Only($data, $id = MY_ID, $row = 'id')
	{
		return Get("SELECT $data FROM {DA}users WHERE $row = '$id' LIMIT 1", $data);
	}
	
	// Obtener todos los datos de un usuario.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	static function User($id = MY_ID, $row = 'id')
	{			
		$q = query("SELECT * FROM {DA}users WHERE id = '$id' OR username = '$id' OR email = '$id' OR $row = '$id' LIMIT 1");
		return (num_rows() > 0) ? fetch_assoc() : false;
	}
	
	// Verificar los datos de un usuario.
	// - $id: Identificación (Nombre de usuario/Correo electrónico).
	// - $password: Contraseña sin codificar.
	// - $service (String/Bool): Servicio predeterminado.
	static function Verify($id, $password, $service = false)
	{
		if(!empty($password))
			$password = Core::Encrypte(Core::Encrypte($password));

		$query = "SELECT null FROM {DA}users WHERE (username = '$id' OR email = '$id') AND password = '$password' ";

		if($service !== false)
			$query .= "AND service = '$service' ";

		$query .= 'LIMIT 1';		
		$q = Rows($query);

		return ($q > 0) ? true : false;
	}
	
	// Checar sesión actual.
	static function CheckSession($loginId = '')
	{
		if(empty($loginId))
			$loginId = _f(Core::theSession('login_id'));
		
		if(empty($loginId) OR !self::UserExist($loginId))
			goto nosession;
		
		global $my, $ms;
			
		$my = self::User($loginId);		
		$ms = @json_decode(Core::theSession('service_info'), true);
		
		foreach($my as $param => $value)
			Tpl::Set('my_' . $param, $value);
			
		if(is_array($ms))
		{
			foreach($ms as $param => $value)
				Tpl::Set('ms_' . $param, $value);
		}
		
		define('MY_ID', 		$my['id']);
		define('MY_RANK', 		$my['rank']);
		define('MY_USERNAME', 	$my['username']);
		define('MY_NAME', 		$my['name']);
		define('LOG_IN', 		true);
		
		self::Update(array(
			'lastonline' 	=> time(),
			'ip_address' 	=> IP,
			'browser' 		=> BROWSER,
			'agent' 		=> AGENT,
			'lasthost' 		=> HOST,
			'os' 			=> OS
		));
		
		return true;
		
		nosession: 
		{
			define('MY_ID', 		'0');
			define('MY_RANK', 		'0');
			define('MY_USERNAME', 	'');
			define('MY_NAME', 		'');
			define('LOG_IN', 		false);			
			return false;
		}
	}
	
	// Checar cookie actual.
	static function CheckCookie()
	{			
		$cookieSession 	= _f(Core::theCookie('session'));
		$check 			= self::Only('id', $cookieSession, 'cookie_session');

		if(LOG_IN OR empty($cookieSession))
			return;
		
		if(!self::UserExist($cookieSession, 'cookie_session') OR !is_numeric($check))
		{
			self::Logout();
			return false;
		}
		
		self::Login($check);
		Core::Redirect(PATH_NOW);
	}
	
	// Verificar si un usuario se encuentra online.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $t (Bool): ¿Devolver en texto?
	static function IsOnline($id = MY_ID, $t = false)
	{
		global $date;		
		$online = self::Data("lastonline", $id);
		
		if($online >= $date['f'])
			return $t == true ? 'Online' : true;

		return $t == true ? 'Offline' : false;
	}
	
	// Obtener el "Gravatar" de un usuario.
	// - $email: Correo electrónico.
	// - $to: Ruta del archivo de destino.
	// - $size (Int): Tamaño de ancho y altura.
	// - $default (Ruta, mm, identicon, monsterid, wavatar, retro): Imagen predeterminada en caso de que el usuario no use el servicio de Gravatar.
	// - $rating (g, pg, r, x): Clasificación máxima de las imagenes.
	static function GetGravatar($email, $to = '', $size = 40, $default = 'mm', $rating = 'g')
	{
		$email 		= md5(strtolower(trim($email)));
		$default 	= urlencode($default);
		
		$gravatar 	= "http://www.gravatar.com/avatar/$email?s=$size&d=$default&r=$rating";
		
		if(!empty($to))
			Io::Write($to, $gravatar);
			
		return $gravatar;
	}

	// Obtener todos los datos de un usuario con una sola condición.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	static function GetUser($id = MY_ID, $row = 'id')
	{			
		$q = query("SELECT * FROM {DA}users WHERE $row = '$id' LIMIT 1");
		return (num_rows() > 0) ? fetch_assoc() : false;
	}
	
	/*####################################################
	##	FUNCIONES PERSONALIZADAS						##
	####################################################*/
}
?>