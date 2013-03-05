<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	BaseSQL
 * Ayudante Base para la creación de un driver SQL.
 *
*/

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

class BaseSQL extends BaseStatic
{
	/**
	 * Recurso a la conexión del servidor.
	 * @var resource
	 */
	static $server 		= null;
	/**
	 * ¿Nos hemos conectado al servidor?
	 * @var boolean
	 */
	static $connected 	= false;

	/**
	 * Consultas realizadas.
	 * @var integer
	 */
	static $querys 			= 0;
	/**
	 * Última consulta realizada.
	 * @var string
	 */
	static $lastQuery 		= '';
	/**
	 * Último recurso de la consulta realizada.
	 * @var resource
	 */
	static $lastResource 	= null;

	/**
	 * ¿Debemos liberar memoria de la próxima consulta?
	 * @var boolean
	 */
	static $freeResult = false;

	/**
	 * Ayudantes reales.
	 * @var array
	 */
	public $realHelper = array(
		'mysql' 	=> 'MySQL',
		'sqlite'	=> 'SQLite',
		'postgre'	=> 'PostgreSQL'
	);

	/**
	 * Lanzar error.
	 * @param string $code    Código del error.
	 * @param string $message Mensaje.
	 */
	static function Error($code, $message = '')
	{
		# Estamos conectados al servidor.
		if( self::Connected() )
		{
			# Si el mensaje esta vacio, el mensaje será el último
			# error generado por el servidor SQL.
			if( empty($message) )
				$message = self::GetErrorMessage();

			# Si el mensaje no esta vacio, lo adjuntamos al final.
			else
				$message .= ' - (' . self::GetErrorMessage() . ')';
		}

		# Definimos información.
		self::$error_other = array('query' => self::$last_query);
		return parent::Error($code, $message);
	}

	/**
	 * Obtiene el último error generado por el servidor SQL.
	 */
	static function GetErrorMessage()
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return;

		global $config;

		# !!DEVELOPER
		# Si agregaras un nuevo ayudante/driver SQL será necesario
		# que agregues aquí la función del último error generado.
		switch( $config['sql']['type'] )
		{
			# MySQLi
			case 'mysql':
				return self::$server->error;
			break;

			# SQLite 3
			case 'sqlite':
				return self::$server->lastErrorMsg();
			break;
		}
	}

	/**
	 * Constructor
	 * Dependiendo del archivo de configuración, carga el ayudante
	 * real para la conexión SQL.
	 */
	public function __construct()
	{
		global $config;

		# Obtenemos el ayudante real (MySQL, SQLite)
		$real = $this->realHelper[$config['sql']['type']];

		# ¿Alguien olvido poner el tipo de conexión?
		if( empty($real) )
			self::Error('sql.invalid.real.helper');

		# Cargamos el ayudante y conectamos.
		Bit::LoadHelper($real);
		SQL::Connect();
	}

	/**
	 * ¿Nos hemos conectado al servidor SQL?
	 * @return boolean Devuelve SI o NO.
	 */
	static function Connected()
	{
		Lang::SetSection('helper.sql');

		if( self::$server == null OR !self::$connected )
			return false;

		return true;
	}

	/**
	 * ¿Se ha hecho una consulta ya?
	 * @return boolean Devuelve SI o NO.
	 */
	static function FirstQuery()
	{
		return ( !empty(self::$lastQuery) ) ? true : false;
	}

	/**
	 * Desconectarse del servidor SQL.
	 */
	static function Disconnect()
	{
		# Necesitamos habernos conectado.
		if( !self::Connected() )
			return;

		# !!!DEVELOPER
		# $server->close() es un metodo que existe
		# tanto en MySQL o SQLite.
		# Si tu nuevo ayudante/driver SQL también lo tiene
		# dejalo como esta, de otra forma te tocará hacer un
		# switch o if
		self::$server->close();
		Reg('%connection.out%');

		# Reiniciamos variables.
		self::$server 		= null;
		self::$connected 	= false;
		self::$lastQuery 	= '';
	}

	/**
	 * Libera la memoria de la última consulta realizada.
	 * @param resource $query Recurso de la última consulta.
	 */
	static function Free($query = '')
	{
		return false;
	}

	/**
	 * Cambia el motor de las tablas especificadas.
	 * @param string $engine Nuevo motor (MYISAM o INNODB)
	 * @param array $tables Tablas afectadas.
	 */
	static function Engine($engine = 'MYISAM', $tables = '')
	{
		return false;
	}

	/**
	 * Optimiza las tablas especificadas.
	 * @param array $tables Tablas afectadas.
	 */
	static function Optimize($tables = '')
	{
		return false;
	}

	/**
	 *  Reparar las tablas especificadas.
	 * @param array $tables Tablas afectadas.
	 */
	static function Repair($tables = '')
	{
		return false;
	}

	/**
	 * Examina las tablas y ordena la información.
	 * Usado para la administración inteligente.
	 * @return Información.
	 */
	static function Examine()
	{
		return false;
	}
}
?>