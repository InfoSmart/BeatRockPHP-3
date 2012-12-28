<?
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

class Tpl
{
	static $html 			= '';
	static $lang 			= '';
	static $params 			= array();
	static $globls 			= array();
	static $func_globals 	= array();
	
	static $METAS 		= '';
	static $STYLES 		= '';
	static $JS 			= '';
	static $VARS 		= '';
	static $STUFF 		= '';
	static $JAVASCRIPT 	= '';

	static $MHTML 		= '';
	static $MHEAD 		= '';
	
	// Lanzar error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{
		Lang::SetSection('mod.tpl');

		Bit::Status($message, __FILE__, array('function' => $function));
		Bit::LaunchError($code);
	}
	
	// Cargar una plantilla.
	static function Load()
	{
		// Caché.

		$cache = self::GetCache();
		
		if($cache !== false)
		{
			self::$html = $cache;
			return true;
		}

		// Inicialización
		
		extract($GLOBALS);
		
		$header = true;
		$footer = true;
		$folder = '';

		$ext 	= (Jade::Enabled()) ? '.jade' : '.php';
		$pext 	= (Jade::Enabled()) ? '.jade' : '.html';

		// Folder de plantilla.
				
		if(is_array($page['folder']))
		{
			$pageFolder 	= $page['folder'];
			$page['folder'] = '';
			
			foreach($pageFolder as $fo)
				$page['folder'] .= $fo . DS;
		}

		// Definición de opciones de plantilla.
		
		$template 	= $page['id'];		
		$folder 	= $page['folder'];
			
		if(isset($page['header']))
			$header = $page['header'];
			
		if(isset($page['footer']))
			$footer = $page['footer'];
			
		if(!empty($folder))
		{
			if(substr($folder, -1) !== DS AND substr($folder, -1) !== '/')
				$folder .= DS;
		}

		// Obtención del código HTML de la plantilla.
			
		if(is_array($template))
		{
			$html = '';
			
			foreach($template as $view)
				$html .= self::Process($folder . $view);
		}
		else
			$html = self::Process($folder . $template);
			
		$html = self::Compress($html);
		ob_start();

		// Implementación de cabecera.		
		
		if($header)
		{			
			if(!file_exists(HEADERS . 'Header' . $ext))
				self::Error('tpl.loadfiles', __FUNCTION__, '%error.load.header% "' . HEADERS . 'Header'.$ext.'".');
			
			if(!empty($page['site_name']))
				$site['name'] = $page['site_name'];
			else
			{
				if(empty($page['name']))
					$page['name'] = $site['site_slogan'];
					
				$site['name'] = SITE_NAME;
				$site['name'] .= (!empty($page['name'])) ? " $site[site_separation] " : '';
				$site['name'] .= $page['name'];
			}

			require APP . 'Setup.Header.php';
			require HEADERS . 'Header' . $ext;
			
			if(empty($page['subheader']))
				$page['subheader'] = 'header';		
			
			if($page['subheader'] !== false)
			{
				if(!file_exists(APP_TPL_HEADERS . $page['subheader'] . $pext))
					self::Error('tpl.loadfiles', __FUNCTION__, '%error.load.subheader% "' . APP_TPL_HEADERS . $page['subheader'] . $pext . '".');

				require APP_TPL_HEADERS . $page['subheader'] . $pext;
			}
		}
		
		echo $html;

		// Implementación de pie de página.

		if($footer)
		{
			if(empty($page['subfooter']))
				$page['subfooter'] = 'footer';
				
			if($page['subfooter'] !== false)
			{
				if(!file_exists(APP_TPL_HEADERS . $page['subfooter'] . $pext))
					self::Error('tpl.loadfiles', __FUNCTION__, '%error.load.subfooter% "' . APP_TPL_HEADERS . $page['subfooter'] . $pext . '".');

				require APP_TPL_HEADERS . $page['subfooter'] . $pext;
			}
			
			require HEADERS . 'Footer' . $ext;
		}
		
		$html = ob_get_contents();

		// Aplicando filtros.

		self::$globls = get_defined_vars();

		$lang = self::SetLang($html, $page['lang'], $page['lang.sections'], $page['lang.live']);
		$html = $lang[0];
		
		$html = self::SetParams($html, $page['parid']);
		$html = Jade::Render($html);
		
		self::$html = $html;
		self::$lang = $lang[1];
		
		ob_clean();
	}
	
	// Procesar una plantilla (TPL) y obtener su contenido en HTML.
	// - $tpl: Ubicación de la plantilla.
	// - $extra (Bool): ¿Aplicar las variables y comprimir HTML?
	// - $ext: Extensión de la plantilla.
	static function Process($tpl, $extra = false, $ext = '')
	{		
		extract($GLOBALS);
		ob_start();

		if(!empty($ext))
			$ext = '.' . $ext;
		else
			$ext = (Jade::Enabled()) ? '.jade' : '.html';
		
		if(!file_exists(KERNEL_TPL . $tpl . $ext) AND !file_exists(APP_TPL . $tpl . $ext) AND !file_exists($tpl . $ext))
			self::Error('tpl.process', __FUNCTION__, '%error.load.template% "'.$tpl.'".');
		
		if(file_exists(KERNEL_TPL . $tpl . $ext))
			require KERNEL_TPL . $tpl . $ext;

		else if(file_exists(APP_TPL . $tpl . $ext))
			require APP_TPL . $tpl . $ext;

		else if(file_exists($tpl . $ext))
			require $tpl . $ext;

		$html = ob_get_contents();
		
		if($extra == true)
		{
			self::$globls = get_defined_vars();

			$lang = self::SetLang($html, $page['lang'], $page['lang.sections']);
			$html = $lang[0];
			
			$html = self::SetParams($html);
			$html = Jade::Render($html);
		}
		
		ob_clean();
		return $html;
	}
	
	// Aplicar variables al código HTML.
	// - $html: Código HTML.
	static function SetParams($html)
	{
		preg_match_all("/\\{\\$(.*?)\\}/is", $html, $VARIABLES);
		$arr = array_merge(self::$globls, self::$func_globals);

		if(count($VARIABLES[1]) > 0)
		{
			foreach($VARIABLES[1] as $var)
			{
				if(Contains($var, '.'))
				{
					$e 			= explode('.', $var);
					$iparam 	= $e[0];
					$sparam 	= $e[1];

					$html = str_ireplace('{$' . $var . '}', $arr[$iparam][$sparam], $html);
				}
				else
					$html = str_ireplace('{$'. $var .'}', $arr[$var], $html);
			}
		}

		preg_match_all("/\\{%(.*?)\\}/is", $html, $CONSTANTS);

		$constants = get_defined_constants(true);
		$constants = $constants['user'];

		if(count($CONSTANTS[1]) > 0)
		{
			foreach($CONSTANTS[1] as $constant)
				$html = str_ireplace('{%'. $constant .'}', $constants[$constant], $html);
		}

		preg_match_all("/%(.*?)%/is", $html, $PARAMS);

		if(count($PARAMS[1]) > 0)
		{
			foreach($PARAMS[1] as $param)
			{
				if(strlen($param) > 30)
					continue;

				$html = str_ireplace('%'. $param .'%', self::$params[$param], $html);
			}
		}
		
		return $html;
	}
	
	// Aplicar variables de traducción al código HTML.
	// - $html: Código HTML.
	// - $lang: Código de lenguaje.
	// - $param: Parametro de traducción.
	static function SetLang($html, $lang = '', $sections = array(), $live = true)
	{
		global $site, $page;

		if($site['site_translate'] == 'false')
			return array($html, $lang);

		if(!empty($site['site_translate']))
			$lang = $site['site_translate'];
		
		if(empty($lang))
			$lang = LANG;
		
		$html = Lang::SetParams($html, $lang, $sections, $live);
		return array($html, $lang);
	}
	
	// Comprimir código HTML.
	// - $html: Código HTML.
	static function Compress($html)
	{
		global $site, $page;
		
		if(BROWSER !== 'Internet Explorer' AND BROWSER !== 'Internet Explorer 9' AND $page['compress'] !== false)
		{
			if($site['site_compress'] == 'true' OR $page['compress'] == true)
				$html = Core::Compress($html);
		}
		
		return $html;
	}
	
	// Guardar caché de la página actual.
	static function SaveCache()
	{
		global $page;		
		$cache = Site::GetCache($page['id']);

		if(!$cache)
			return false;

		if(!Mem::Ready())
		{
			$file = BIT . 'Cache' . DS . $page['id'] . '.' . self::$lang . '.cache';	
		
			if(time() > (filemtime($file) + ($cache['time'] * 60 * 60)) AND file_exists($file))
				return false;
		
			return Io::Write($file, Tpl::$html);
		}
		else
		{
			$time = Mem::Get($page['id'] . self::$lang . '_time');

			if(time() < ($time + ($cache['time'] * 60 * 60)) AND !empty($time))
				return false;

			Mem::Set($page['id'] . self::$lang . '_time', time());
			Mem::Set($page['id'] . self::$lang, self::$html);

			return true;
		}
	}
	
	// Obtener caché de la página actual.
	static function GetCache()
	{
		global $page, $site;		
		$cache = Site::getCache($page['id']);

		if(!$cache)
			return false;

		$lang = $page['lang'];

		if(empty($lang))
			$lang = LANG;
			
		if(!empty($site['site_translate']))
			$lang = $site['site_translate'];		

		if(!Mem::Ready())
		{
			$file = BIT . 'Cache' . DS . $page['id'] . '.' . $lang . '.cache';
		
			if(!file_exists($file))
				return false;		
		
			return Io::Read($file);
		}
		else
		{
			$data = Mem::Get($page['id'] . $lang);

			if($data == false)
				return false;

			return $data;
		}
	}
	
	// Establecer variable.
	// - $param (String, Array): Variable.
	// - $vlaue: Valor.
	static function Set($param, $value = '')
	{
		if(is_array($param))
		{
			foreach($param as $key => $val)
				self::$params[$key] = $val;
		}
		else if(is_string($param))
			self::$params[$param] = $value;
	}
	
	// Eliminar variable.
	// - $param: Variable.
	static function Del($param)
	{
		unset(self::$params[$param]);
	}
	
	// Implementar jQuery a la página.
	// - $resources: ¿Agregarlo desde los recursos locales?
	static function jQuery($resources = true)
	{
		$file = ($resources) ? RESOURCES_GLOBAL . '/js/jquery.js' : '//code.jquery.com/jquery-latest.min.js';
		self::AddScript($file);
	}
	
	// Agregar un elemento Meta.
	// - $name: Nombre de la META.
	// - $content: Contenido/Valor.
	// - $type: Tipo.
	static function Meta($name, $content, $type = 'name')
	{
		$html = new Html('meta');
		$html->Set($type, $name)->Set('content', $content);

		self::$METAS .= '	' . $html->Build() . "\r\n";
	}
	
	// Agregar un archivo de estilo.
	// - $file: Ruta del archivo CSS.
	// - $rel: Rel.
	// - $id: ID del elemento.
	// - $media: Media.
	static function AddStyle($file, $rel = 'stylesheet', $id = '', $media = '')
	{
		$html = new Html('link');
		$html->Set('href', $file);

		if(!empty($rel))
			$html->Set('rel', $rel);

		if(!empty($id))
			$html->Set('id', $id);

		if(!empty($media))
			$html->Set('media', $media);
		
		self::$STYLES .= '	' . $html->Build() . "\r\n";		
		return true;
	}
	
	// Agregar un archivo de estilo con la ubicación predeterminada.
	// - $file: Archivo CSS.
	// - $system (Bool): ¿De los recursos globales?
	// - $external (Bool) - ¿De los recursos externos?
	static function Style($file, $system = false, $external = false)
	{
		$path = (!$system) ? RESOURCES . '/css' : RESOURCES_GLOBAL . '/css';

		if($external)
			$path = $path . '/external';
			
		self::AddStyle("$path/$file.css");
	}
	
	// Agregar un archivo JavaScript.
	// - $file: Ruta del archivo.
	// - $async (Bool): ¿Sincronizado?
	// - $id: ID del elemento.
	static function AddScript($file, $async = false, $id = '')
	{
		$html = new Html('script');
		$html->Set('src', $file);
		
		if(!empty($id))
			$html->Set('id', $id);
		
		if($async)
			$html->Set('async', $async);
			
		self::$JS .= '	' . $html->Build() . "\r\n";
	}
	
	// Agregar un archivo Javascript con la ubicación predeterminada.
	// - $file: Archivo JavaScript.
	// - $system (Bool): ¿De los recursos globales?
	// - $external (Bool) - ¿De los recursos externos?
	static function Script($file, $system = false, $external = false)
	{
		$path = (!$system) ? RESOURCES . '/js' : RESOURCES_GLOBAL . '/js';
			
		if($external)
			$path = $path . '/external';
			
		self::AddScript("$path/$file.js");
	}
	
	// Agregar variable/función/definición JavaScript.
	// - $param: Variable/Función/Definición.
	// - $value: Valor.
	// - $var: ¿Variable?
	static function AddVar($param, $value, $var = true)
	{
		if($value !== 'true' AND $value !== 'false' AND $value !== 'null' AND !is_numeric($value))
			$value = "\"$value\"";
		
		$html = "$param = $value;\r\n";
		self::$VARS .= $html;
	}
	
	// Agregar "Cosas" a la cabecera.
	// - $html: HTML de tu "Cosa".
	static function Stuff($html)
	{
		self::$STUFF .= "	$html\r\n";
	}

	// Agregar atributo a la etiqueta <html>
	// - $param: Parametro.
	// - $value: Valor.
	static function MoreHTML($param, $value = "")
	{
		$html = $param;

		if(!empty($value))
			$html .= "=\"$value\"";

		$html .= ' ';
		self::$MHTML .= $html;
	}

	// Agregar atributo a la etiqueta <head>
	// - $param: Parametro.
	// - $value: Valor.
	static function MoreHead($param, $value = "")
	{
		$html = $param;

		if(!empty($value))
			$html .= "=\"$value\"";

		$html .= ' ';
		self::$MHEAD .= $html;
	}
	
	// Agregar una tarea para la barra de tareas especial para Internet Explorer 9+
	// - $name: Nombre de la tarea.
	// - $url: Dirección web de la tarea.
	// - $icon: Dirección web del icono.
	static function IETask($name, $url, $icon = "")
	{
		self::Meta('msapplication-task', "name=$name;action-uri=$url;icon-uri=$icon");
	}
	
	// Ejecutar una acción JavaScript al terminar de cargar la página.
	// - action: Acción JavaScript.
	static function JavaAction($action)
	{
		// El HTML de JavaScript esta vacio.
		if(empty(self::$JAVASCRIPT))
			self::$JAVASCRIPT = '<script>$(document).on("ready", function() { ';
			
		self::$JAVASCRIPT .= " $action ";
	}
	
	// Ejecutar una alerta JavaScript.
	// - $msg: Mensaje.
	static function JavaAlert($msg)
	{
		self::JavaAction("alert('$msg'); ");
	}

	// Envio de cabeceras para permitir Cross-Domain.
	// - $domain: Dominio(s) a permitir.
	// - $max_age (Int): Duración máxima.
	// - $methods: Metodos permitidos.
	static function AllowCross($domain, $max_age = 3628800, $methods = 'PUT, DELETE, POST, GET')
	{
		header('Access-Control-Allow-Origin: ' . $domain);
		header('Access-Control-Max-Age: ' . $max_age);
		header('Access-Control-Allow-Methods: ' . $methods);
	}
	
	// Protección de iFrame.
	// - $frame: De donde permitir.
	static function Protect($frame = 'SAMEORIGIN')
	{
		header('X-Frame-Options: ' . $frame);
		header('X-XSS-Protection: 1; mode=block');
	}

	// Envio de cabeceras para simular una imagen.
	// - $type: Tipo de imagen.
	static function Image($type = 'PNG')
	{
		header('Content-type: image/' . $type);
	}

	// Envio de cabeceras para la descarga de un archivo.
	// - $file: Ubicación del archivo.
	// - $name: Nombre del archivo.
	// - $mimetype: Mimetype del archivo.
	static function Download($file, $name, $mimetype = '')
	{
		if(empty($mimetype))
			$mimetype = Io::Mimetype($file);

		header('Content-Type: ' . $mimetype);
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header('Content-Length: ' . filesize($file));
		header('Accept-Ranges: bytes');

		echo Io::Read($file);
	}
}
?>