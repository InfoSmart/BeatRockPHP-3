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
	// Función - Obtener la edad de un usuario por medio del año especificado.
	// - $year: Año.
	public static function Age($year)
	{
		if(!is_numeric($year) || $year < 1990 || $year > date('Y'))
			return $year;
			
		return (date('Y') - $year);
	}
	
	// Función - Contar los usuarios o usuarios online.
	// - $online (Bool): ¿Usuarios online?
	public static function Count($online = false)
	{
		if($online)
		{
			global $date;
			return query_rows("SELECT null FROM {DA}users WHERE lastonline > '$date[f]'");
		}
		else
			return query_rows("SELECT null FROM {DA}users");
	}
	
	// Función - Obtener los usuarios online.
	// - $limit (Int): Limite de usuarios.
	public static function OnlineUsers($limit = 0)
	{
		global $date;
		
		$q = "SELECT * FROM {DA}users WHERE lastonline > '$date[f]'";
		
		if($limit > 0)
			$q .= " ORDER BY RAND() LIMIT $limit";
			
		return query($q);
	}
	
	// Función - Verificar si un dato ya existe.
	// - $str: Dato a verificar.
	// - $type (email, username): Tipo de verificación.
	public static function Exist($str, $type = 'email')
	{			
		$q = query_rows("SELECT null FROM {DA}users WHERE $type = '$str' LIMIT 1");	
		return $q > 0 ? true : false;
	}
	
	// Función - Iniciar sesión.
	// - $id (Int): ID del usuario.
	// - $cookie (Bool): ¿Recordar el usuario en esta PC?
	public static function Login($id, $cookie = true)
	{
		Core::theSession('login_id', $id);
		
		if($cookie)
		{
			$password = self::Data("password", $id);
			$s = Core::Encrypte(md5($id . $password));
			
			self::UpdateData("cookie_session", $s, $id);
			Core::theCookie("session", $s);
		}
		
		self::Enter($id);
	}
	
	// Función - Desconectarse.
	public static function Logout()
	{
		Core::delSession('login_id');
		Core::delCookie('session');	

		self::UpdateData('cookie_session', '');
		self::Out(MY_ID);
	}
	
	// Función - Actualizar datos de conexión.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	public static function Enter($id)
	{
		self::Update(Array(
			'lastaccess' => time(),
			'lastonline' => time(),
			'ip_address' => IP
		), $id);
	}
	
	// Función - Actualizar datos de desconexión.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	public static function Out($id)
	{
		self::Update(Array(
			'lastonline' => '0',
			'ip_address' => IP
		), $id);
	}
	
	// Función - Agregar un nuevo usuario.
	// - $username: Nombre de usuario.
	// - $password: Contraseña en texto plano.
	// - $name: Nombre real.
	// - $email: Correo electrónico.
	// - $birthday: Fecha de nacimiento.
	// - $photo (Dirección, Array): Foto de perfil.
	// - $auto (Bool): ¿Auto conectarse?
	// - $o (Array): Otros valores...
	public static function NewUser($username, $password, $name, $email, $birthday = "", $photo = "", $auto = true, $o = '')
	{
		if(is_array($photo))
		{
			if(!is_numeric($photo['size']))
				$photo['size'] = 80;
			
			if(empty($photo['rating']))
				$photo['rating'] = "g";
				
			$photo = self::GetGravatar($email, '', $photo['size'], $photo['default'], $photo['rating']);
		}

		if(!empty($password))
			$password = Core::Encrypte($password);
		
		$data = Array(
			'username' => $username,
			'password' => Core::Encrypte($password),
			'name' => $name,
			'email' => $email,
			'photo' => $photo,
			'birthday' => $birthday,
			'account_birthday' => time(),
			'ip_address' => IP,
			'browser' => BROWSER,
			'agent' => AGENT,
			'os' => OS
		);
		
		if(is_array($o))
		{
			foreach($o as $param => $value)
				$data[$param] = $value;
		}
		
		query_insert('users', $data);
		$id = mysql_insert_id();
		
		if($auto)
			self::Login($id);
			
		return $id;
	}
	
	// Función - Agregar un nuevo servicio.
	// - $id: Identificación de servicio.
	// - $service (facebook, twitter, google): Servicio.
	// - $name: Nombre de identificación de usuario.
	// - $info: Información obtenida del servicio.
	public static function NewService($id, $service, $name, $info)
	{
		$hash = Core::Random(80);
		
		query_insert('users_services', Array(
			'identification' => $id,
			'service' => $service,
			'name' => $name,
			'user_hash' => $hash,
			'info' => _f($info, false),
			'date' => time()
		));
		
		return $hash;
	}
	
	// Función - ¿Existe el servicio?
	// - $id: Identificación del servicio.
	// - $service (facebook, twitter, google): Servicio.
	public static function ServiceExist($id, $service)
	{
		$q = query_rows("SELECT null FROM {DA}users_services WHERE identification = '$id' AND service = '$service' LIMIT 1");
		return $q > 0 ? true : false;
	}
	
	// Función - Actualizar varios datos de un servicio.
	// - $data (Array): Datos a actualizar.
	// - $id: ID del servicio.
	public static function UpdateService($data, $id)
	{
		query_update('users_services', $data, Array(
			"id = '$id'"
		), 1);
	}
	
	// Función - Obtener todos los datos de un servicio.
	// - $id: Identificación del servicio.
	// - $service (facebook, twitter, google): Servicio.
	public static function Service($id, $service)
	{
		$q = query("SELECT * FROM {DA}users_services WHERE (id = '$id' OR identification = '$id') AND service = '$service' LIMIT 1");
		return num_rows() > 0 ? fetch_assoc() : false;
	}
	
	// Función - Obtener los datos de un usuario con servicio.
	// - $hash: Hash del servicio.
	// - $service: Servicio.
	public static function UserService($hash, $service)
	{
		$q = query("SELECT * FROM {DA}users WHERE user_hash = '$hash' AND service = '$service' LIMIT 1");
		return num_rows() > 0 ? fetch_assoc() : false;
	}

	// Función - Obtener los servicios conectados a un usuario.
	// - $hash: Hash del usuario.
	public static function GetServices($hash)
	{
		$q = query("SELECT * FROM {DA}users_services WHERE user_hash = '$hash' ORDER BY id DESC");
		return num_rows() > 0 ? $q : false;
	}

	// Función - Eliminar un servicio.
	// $id: Identificación del servicio.
	public static function DeleteService($id)
	{
		query("DELETE FROM {DA}users_services WHERE id = '$id' LIMIT 1");
	}
	
	// Función - ¿Existe el usuario?
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro a verificar existencia.
	public static function UserExist($id, $row = 'id')
	{
		$q = query_rows("SELECT null FROM {DA}users WHERE id = '$id' OR username = '$id' OR email = '$id' OR $row = '$id' LIMIT 1");		
		return $q > 0 ? true : false;
	}
	
	// Función - Actualizar varios datos de un usuario.
	// - $data (Array): Datos a actualizar.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	public static function Update($data, $id = '', $row = 'id')
	{
		if(empty($id))
			$id = MY_ID;
			
		if(!is_numeric($id))
			$id = self::Data('id', $id, $row);
			
		query_update('users', $data, Array(
			"id = '$id'"
		), 1);
	}
	
	// Función - Actualizar dato de un usuario.
	// - $data: Parametro a actualizar.
	// - $new: Valor nuevo.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	public static function UpdateData($data, $new, $id = '', $row = 'id')
	{
		if(empty($id))
			$id = MY_ID;
			
		if(!is_numeric($id))
			$id = self::Data('id', $id, $row);
			
		query_update('users', Array(
			$data => $new
		), Array(
			"id = '$id'"
		), 1);
	}
	
	// Función - Obtener el dato de un usuario.
	// - $data: Parametro a obtener.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	public static function Data($data, $id = '', $row = 'id')
	{
		if(empty($id))
			$id = MY_ID;
			
		return query_get("SELECT $data FROM {DA}users WHERE id = '$id' OR username = '$id' OR email = '$id' OR $row = '$id' LIMIT 1", $data);
	}
	
	
	// Función - Obtener el dato de un usuario con solo una condición.
	// - $data: Parametro a obtener.
	// - $id: Valor del usuario a cumplir la condición.
	// - $row: Parametro de condición.
	public static function Only($data, $id = '', $row = 'id')
	{
		if(empty($id))
			$id = MY_ID;
			
		return query_get("SELECT $data FROM {DA}users WHERE $row = '$id' LIMIT 1", $data);
	}
	
	// Función - Obtener todos los datos de un usuario.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	public static function User($id = '', $row = 'id')
	{
		if(empty($id))
			$id = MY_ID;
			
		$q = query("SELECT * FROM {DA}users WHERE id = '$id' OR username = '$id' OR email = '$id' OR $row = '$id' LIMIT 1");
		return num_rows() > 0 ? fetch_assoc() : false;
	}
	
	// Función - Verificar los datos de un usuario.
	// - $id: Identificación (Nombre de usuario/Correo electrónico).
	// - $password: Contraseña sin codificar.
	// - $service (String/Bool): Servicio predeterminado.
	public static function Verify($id, $password, $service = false)
	{
		if(!empty($password))
			$password = Core::Encrypte(Core::Encrypte($password));

		$query = "SELECT null FROM {DA}users WHERE (username = '$id' OR email = '$id') AND password = '$password' ";

		if($service !== false)
			$query .= "AND service = '$service' ";

		$query .= 'LIMIT 1';
		
		$q = query_rows($query);
		return $q > 0 ? true : false;
	}
	
	// Función - Checar sesión actual.
	public static function CheckSession($loginId = '')
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
		
		define('MY_ID', $my['id']);
		define('MY_RANK', $my['rank']);
		define('MY_USERNAME', $my['username']);
		define('MY_NAME', $my['name']);
		define('LOG_IN', true);
		
		self::Update(Array(
			'lastonline' => time(),
			'ip_address' => IP,
			'browser' => BROWSER,
			'agent' => AGENT,
			'lasthost' => HOST,
			'os' => OS
		));
		
		return true;
		
		nosession: 
		{
			define('MY_ID', '0');
			define('MY_RANK', '0');
			define('MY_USERNAME', '');
			define('MY_NAME', '');
			define('LOG_IN', false);			
			return false;
		}
	}
	
	// Función - Checar cookie actual.
	public static function CheckCookie()
	{			
		$cookieSession = _f(Core::theCookie('session'));
		$check = self::Only('id', $cookieSession, 'cookie_session');
		
		if(empty($cookieSession) OR !self::UserExist($cookieSession, 'cookie_session') OR !is_numeric($check))
		{
			self::Logout();
			return false;
		}
		
		self::Login($check);
	}
	
	// Función - Verificar si un usuario se encuentra online.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $t (Bool): ¿Devolver en texto?
	public static function IsOnline($id = '', $t = false)
	{
		global $date;
		
		if(empty($id))
			$id = MY_ID;
		
		$online = self::Data("lastonline", $id);
		
		if($online >= $date['f'])
		{
			if($t)
				return "Online";
			else
				return true;
		}
		
		if($t)
			return "Offline";
		else
			return false;
	}
	
	// Función - Obtener el "Gravatar" de un usuario.
	// - $email: Correo electrónico.
	// - $to: Ruta del archivo de destino.
	// - $size (Int): Tamaño de ancho y altura.
	// - $default (Ruta, mm, identicon, monsterid, wavatar, retro): Imagen predeterminada en caso de que el usuario no use el servicio de Gravatar.
	// - $rating (g, pg, r, x): Clasificación máxima de las imagenes.
	public static function GetGravatar($email, $to = '', $size = 40, $default = 'mm', $rating = 'g')
	{
		$email = md5(strtolower(trim($email)));
		$default = urlencode($default);
		
		$gravatar = "http://www.gravatar.com/avatar/$email?s=$size&d=$default&r=$rating";
		
		if(!empty($to))
			Io::Write($to, $gravatar);
			
		return $gravatar;
	}

	// Función - Obtener todos los datos de un usuario con una sola condición.
	// - $id: ID/Nombre de usuario/Correo electrónico del usuario.
	// - $row: Parametro.
	public static function GetUser($id = MY_ID, $row = 'id')
	{			
		$q = query("SELECT * FROM {DA}users WHERE $row = '$id' LIMIT 1");
		return num_rows() > 0 ? fetch_assoc() : false;
	}
	
	/*####################################################
	##	FUNCIONES PERSONALIZADAS						##
	####################################################*/
}
?>