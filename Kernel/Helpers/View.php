<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

# Acción ilegal
if( !defined('BEATROCK') )
	exit;

###############################################################
## Controlador View
###############################################################
## Permite cargar una vista de las carpetas:
## - /App/Views/
## - /Kernel/Views/
##
## Ejemplos:
##
## Para cargar la vista ubicada en /App/Views/acerca.html
## echo new View('acerca');
##
## Para cargar la vista ubicada en /App/Views/home/mapa.html
## echo new View('/home/mapa');
##
## Para cargar la vista ubicada en: /App/Views/sistema.tpl
## echo new View('sistema', 'tpl');
##
## Reemplazar variables de traducción.
## $myview = new View('acerca');
## echo $myview->AddLang();
##
## Reemplazar variables de traducción y de plantilla.
## $myview = new View('acerca');
## echo $myview->AddLang()->AddParams();
###############################################################

class View extends Base
{
	public $html; 			# Código HTML generado hasta ahora.

	public $globals;		# Variables globales.
	public $func_globals;	# Variables globales 2

	###############################################################
	## Procesar una plantilla.
	## - $tpl: 	Nombre/Ruta de la plantilla.
	## - $ext: 	Extensión de la plantilla.
	###############################################################
	function __construct($tpl, $ext = '')
	{
		# Iniciamos correctamente al padre.
		parent::__construct($this);

		# Necesitamos todas las variables de PHP.
		extract($GLOBALS);
		ob_start();

		# Definimos la extensión más adecuada para la plantilla.
		if( !empty($ext) )
			$ext = '.' . $ext;
		else
			$ext = ( Jade::Enabled() ) ? '.jade' : '.html';

		# Tratamos de encontrar la plantilla.
		if( file_exists(APP_VIEWS . $tpl . $ext) )
			require APP_VIEWS . $tpl . $ext;

		else if( file_exists(KERNEL_VIEWS . $tpl . $ext) )
			require KERNEL_VIEWS . $tpl . $ext;

		else if( file_exists($tpl . $ext) )
			require $tpl . $ext;

		else
			$this->Error('view.process', "%error.load.template% '$tpl'");

		# Definimos las variables actuales.
		$this->globals = get_defined_vars();

		# Guardamos el buffer HTML.
		$html = ob_get_contents();

		# Limpiamos el buffer de salida.
		ob_clean();

		$this->html = $html;
		return $this;
	}

	###############################################################
	## Adjuntamos código HTML al inicio.
	## - $html:	Código HTML.
	###############################################################
	function PrependHtml($html)
	{
		$this->html = $html . $this->html;
		return $this;
	}

	###############################################################
	## Adjuntamos código HTML al final.
	## - $html: Código HTML.
	###############################################################
	function AppendHtml($html)
	{
		$this->html .= $html;
		return $this;
	}

	###############################################################
	## Reemplazamos variables de traducción.
	## - $lang: 		Lenguaje a traducir.
	## - $sections:		Secciones de los lenguajes.
	## - $live (bool): 	¿Preparar para la traducción en tiempo real?
	###############################################################
	function AddLang($lang = LANG, $sections = array(), $live = false)
	{
		global $site, $page;

		# La configuración del sitio nos obliga a NO TRADUCIR.
		if( $site['site_translate'] == 'false' )
			return $this;

		# No se establecio el lenguaje, usar el del visitante.
		if( empty($lang) )
			$lang = LANG;

		# La configuración del sitio nos obliga a usar un lenguaje en especifico.
		if( !empty($site['site_translate']) )
			$lang = $site['site_translate'];

		# ¡Traducimos!
		$this->html = _l($this->html, $lang, $sections, $live);
		return $this;
	}

	###############################################################
	## Reemplazamos variables, variables de plantilla y constantes.
	###############################################################
	function AddParams()
	{
		$html = $this->html;

		# Buscamos todas las referencias hacia las variables.
		preg_match_all("/\\{\\$(.*?)\\}/is", $html, $VARIABLES);
		# Combinamos las externas.
		$arr = array_merge($this->globals, $this->func_globals);

		# Hay una o más referencias.
		if( count($VARIABLES[1]) > 0 )
		{
			foreach( $VARIABLES[1] as $var )
			{
				# Contienen un .
				if( Contains($var, '.') )
				{
					# Separamos su llave
					$e 			= explode('.', $var);
					$iparam 	= $e[0];
					$sparam 	= $e[1];

					# Remplazamos cada referencia por su valor real.
					$html = str_ireplace('{$' . $var . '}', $arr[$iparam][$sparam], $html);
				}
				else
					$html = str_ireplace('{$'. $var .'}', $arr[$var], $html);
			}
		}

		# Buscamos todas las referencias hacia constantes.
		preg_match_all("/\\{%(.*?)\\}/is", $html, $CONSTANTS);

		# Obtenemos las constantes definidas.
		$constants = get_defined_constants(true);
		$constants = $constants['user'];

		# Hay una o más referencias.
		if( count($CONSTANTS[1]) > 0 )
		{
			# Reemplazamos cada referencia por su valor real.
			foreach( $CONSTANTS[1] as $constant )
				$html = str_ireplace('{%'. $constant .'}', $constants[$constant], $html);
		}

		# Buscamos todas las referencias hacia las variables de plantilla.
		preg_match_all("/%(.*?)%/is", $html, $PARAMS);

		# Hay una o más referencias.
		if( count($PARAMS[1]) > 0 )
		{
			foreach( $PARAMS[1] as $param )
			{
				# Para evitar posibles confusiones con el código/contenido
				# si el nobre de esta referencia es mayor a 30 caracteres
				# omitir.
				if( strlen($param) > 30 )
					continue;

				# Reemplazamos cada referencia por su valor real.
				$html = str_ireplace('%'. $param .'%', Tpl::$params[$param], $html);
			}
		}

		$this->html = $html;
		return $this;
	}

	###############################################################
	## Agregamos compresión al código.
	###############################################################
	function AddCompress()
	{
		global $site, $page;

		# !!!
		# Internet Explorer (quien más) ha presentado problemas de renderizado
		# al intentar leer el código comprimido con esta función.
		#
		# Si el navegador del visitante no usa el motor "Trident" y
		# la compresión no esta desactivada en esta página...
		if( ENGINE !== 'Trident' AND $page['compress'] !== false )
		{
			# Comprimir código
			if( $site['site_compress'] == 'true' OR $page['compress'] == true )
				$this->html = Core::Compress($this->html);
		}

		return $this;
	}

	###############################################################
	## Compilar código en Jade
	###############################################################
	function AddJade()
	{
		# Compilamos el código en Jade (Si esta activado)
		$this->html = Jade::Render($this->html);
		return $this;
	}

	###############################################################
	## Guardamos la página en caché
	###############################################################
	function SaveCache()
	{
		global $page;

		# Obtenemos información de la caché de esta página.	
		$cache = Site::GetCache($page['id']);

		# Esta página no debe ser guardada en caché.
		if( !$cache )
			return false;

		# El servidor Memcache no esta preparado.
		if( !Mem::Ready() )
		{
			# Guardamos la caché de la página en un archivo físico.
			$file = BIT . 'Cache' . DS . $page['id'] . '.' . self::$lang . '.cache';	
			
			# Solo guardamos si la caché anterior ha expirado. (O si no se ha guradado ninguna caché)
			if( time() > (filemtime($file) + ($cache['time'] * 60 * 60)) AND file_exists($file) )
				return false;
			
			# Escribimos la caché.
			return Io::Write($file, $this->html);
		}

		# El servidor Memcache esta preparado.
		else
		{
			# Obtenemos la fecha de la última copia en caché de esta página.
			$time = Mem::Get($page['id'] . $this->lang . '_time');

			# Solo guardamos si la caché anterior ha expirado. (O si no se ha guradado ninguna caché)
			if( time() < ($time + ($cache['time'] * 60 * 60)) AND !empty($time) )
				return false;

			# Guardamos la caché de la página en el servidor Memcache
			Mem::Set($page['id'] . $this->lang . '_time', time());
			Mem::Set($page['id'] . $this->lang, self::$html);

			return true;
		}
	}

	###############################################################
	## Devolver el código HTML
	###############################################################
	function Html()
	{
		return $this->html;
	}

	###############################################################
	## Devolver resultado en una solicitud de tipo string.
	###############################################################
	function __toString()
	{
		return $this->html;
	}
}
?>