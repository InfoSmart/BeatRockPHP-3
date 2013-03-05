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

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

###############################################################
## Controlador Tpl
###############################################################
## Controla el funcionamiento y la configuración relacionada
## a la vista de página actual. ( $page['id'] ) 
###############################################################

class Tpl extends BaseStatic
{
	static $html;				# Código HTML de la vista actual.
	static $params 	= array();	# Variables de plantilla definidas.
	
	static $METAS;				# Meta etiquetas
	static $LINKS;				# Archivos Enlazados.
	static $JS;					# Archivos JavaScript
	static $VARS;				# Variables de JavaScript.
	static $STUFF;				# Código HTML
	static $JAVASCRIPT;			# Código JavaScript a ser ejecutado al final.

	static $ATTRS_HTML;			# Atributos de la etiqueta <html>
	static $ATTRS_HEAD;			# Atributos de la etiqueta <head>

	###############################################################
	## Cargar la vista de página.
	###############################################################
	static function Load()
	{
		# Verificamos si hay una caché guardada.
		$cache = self::GetCache();

		# Al parecer si, devolver directamente la caché.
		if( $cache !== false )
		{
			self::$html = $cache;
			return true;
		}

		# Necesitamos todas las variables de PHP.
		extract($GLOBALS);

		# Definimos la extensión adecuada.
		$ext 	= (Jade::Enabled()) ? '.jade' : '.html';
		# ¿Usar cabecera?
		$header = true;
		# ¿Usar pie de página?
		$footer = true;

		# Los folders estan ordenados en un array.
		if( is_array($page['folder']) )
		{
			# Ordenamos los folders en una sola ruta.

			$folders 		= $page['folder'];
			$page['folder'] = '';

			foreach( $folders as $fo )
				$page['folder'] .= $fo . DS;
		}

		# La vista actual.
		$template 	= $page['id'];
		# La ruta del folder donde se ubica la vista.
		$folder 	= $page['folder'];

		# ¿Usar cabecera?
		if( isset($page['header']) )
			$header = $page['header'];

		# ¿Usar pie de página?
		if( isset($page['footer']) )
			$footer = $page['footer'];

		# Verificamos la ruta del folder.
		if( !empty($folder) )
		{
			if( substr($folder, -1) !== DS AND substr($folder, -1) !== '/' )
				$folder .= DS;
		}

		# Hay más de una vista definida.
		if( is_array($template) )
		{
			# Adjuntamos el código HTML de cada una de ellas.

			foreach( $template as $tp )
				$html 	.= new View($folder . $tp);
		}

		# Solo hay una vista.
		else
			$html = new View($folder . $template);

		# Queremos agregar la cabecera.
		if( $header )
		{
			# El archivo de cabecera no existe ¿wtf?
			if( !file_exists(KERNEL_VIEWS . 'Header' . $ext) )
				self::Error('site.loadfiles', '%error.load.header% "' . KERNEL_VIEWS . 'Header'.$ext.'".');

			# Se ha definido reemplazar todo el titulo por un texto en especifico.
			if( !empty($page['site_name']) )
				$page['page_name'] = $page['site_name'];

			# Usar como titulo el nombre del sitio y seguidamente el eslogan o nombre de página actual.
			else
			{
				# No hay nombre de página, usar el eslogan.
				if( empty($page['name']) )
					$page['name'] = $site['site_slogan'];

				# Ordenamos de una manera PRO.
				$page['page_name'] = SITE_NAME;
				$page['page_name'] .= ( !empty($page['name']) ) ? " $site[site_separation] " : '';
				$page['page_name'] .= $page['name'];
			}

			# Limpiamos el buffer de salida. (En caso de que algo extraño haya generado buffer)
			ob_clean();
			# Comenzamos un nuevo buffer.
			ob_start();

			# Requerimos la configuración de cabecera.
			require APP . 'Setup.Header.php';
			# Adjuntamos la cabecera.
			require KERNEL_VIEWS . 'Header' . $ext;

			# No se ha definido una subcabecera, utilizar la predeterminada.
			if( empty($page['subheader']) )
				$page['subheader'] = 'header';

			# Queremos usar la subcabecera.
			if( $page['subheader'] !== false)
			{
				# ¡La subcabecera no existe!
				if( !file_exists(APP_VIEWS_HEADERS . $page['subheader'] . $ext) )
					self::Error('site.loadfiles', '%error.load.subheader% "' . APP_VIEWS_HEADERS . $page['subheader'] . $ext . '".');

				# Adjuntamos la subcabecera.
				require APP_VIEWS_HEADERS . $page['subheader'] . $ext;
			}

			# Agregamos el código HTML generado hasta ahora al principio del contenido.
			$html->PrependHtml(ob_get_contents());
		}

		# Queremos agregar el pie de página.
		if( $footer )
		{
			# Limpiamos el buffer de salida. (Por si quisimos la cabecera)
			ob_clean();
			# Comenzamos un nuevo buffer.
			ob_start();

			# No se ha definido un sub-pie de página, utilizar la predeterminada.
			if( empty($page['subfooter']) )
				$page['subfooter'] = 'footer';

			# Queremos usar el sub pie de página.
			if( $page['subfooter'] !== false )
			{
				# ¡El sub pie de página no existe!
				if( !file_exists(APP_VIEWS_HEADERS . $page['subfooter'] . $ext) )
					self::Error('site.loadfiles', '%error.load.subfooter% "' . APP_VIEWS_HEADERS . $page['subfooter'] . $ext . '".');

				# Adjuntamos el sub pie de página.
				require APP_VIEWS_HEADERS . $page['subfooter'] . $ext;
			}

			# Adjuntamos el pie de página.
			require KERNEL_VIEWS . 'Footer' . $ext;

			# Agregamos el código HTML generado hasta ahora al final del contenido.
			$html->AppendHtml(ob_get_contents());
		}

		# Limpiamos el buffer de salida.
		ob_clean();

		# Definimos las variables PHP.
		$html->func_globals = get_defined_vars();

		# Agregamos traducción y variables de plantilla al código HTML actual.
		$html->AddLang($page['lang'], $page['lang.sections'], $page['lang.live'])->AddParams()->AddJade();
		self::$html = $html;
	}

	###############################################################
	## Obtener caché de la página actual.
	###############################################################
	static function GetCache()
	{
		global $page, $site;

		# Obtenemos información de la caché de esta página.	
		$cache = Site::GetCache($page['id']);

		# Esta página no debe ser guardada en caché.
		if( !$cache )
			return false;

		# Lenguaje de la página.
		$lang = $page['lang'];

		# Lenguaje vacio, usar el lenguaje del visitante.
		if( empty($lang) )
			$lang = LANG;
			
		# La configuración del sitio nos obliga a usar un lenguaje en especifico.
		if( !empty($site['site_translate']) )
			$lang = $site['site_translate'];		

		# El servidor Memcache no esta preparado.
		if( !Mem::Ready() )
		{
			# Este debería ser el archivo de la caché.
			$file = BIT . 'Cache' . DS . $page['id'] . '.' . $lang . '.cache';
			
			# El archivo no existe.
			if( !file_exists($file) )
				return false;		
			
			# Retornar el código HTML de la caché.
			return Io::Read($file);
		}

		# El servidor Memcache esta preparado.
		else
		{
			# Obtenemos el código HTML de la caché.
			$data = Mem::Get($page['id'] . $lang);

			# ¡No hay ningún código!
			if( !$data )
				return false;

			return $data;
		}
	}
	
	###############################################################
	## Establecer variable de plantilla.
	## - $param (string, array): 	Nombre de la variable.
	## - $value: 					Valor.
	###############################################################
	static function Set($param, $value = '')
	{
		# La variable es un array, estamos guardando más de una variable de plantilla.
		if( is_array($param) )
		{	
			# Guardar cada una de ellas.
			foreach( $param as $key => $val )
			{
				# El valor se encuentra vacio, omitir.
				if( empty($val) )
					continue;

				self::$params[$key] = $val;
			}
		}

		# El nombre de la variable es una cadena, todo bien.
		else if( is_string($param) )
			self::$params[$param] = $value;
	}
	
	###############################################################
	## Eliminar variable.
	## - $param: Nombre de la variable.
	###############################################################
	static function Del($param)
	{
		unset(self::$params[$param]);
	}
	
	###############################################################
	## Implementar la librería jQuery a la página.
	## - $resources (bool): ¿Agregarlo desde los recursos globales?
	###############################################################
	static function AddjQuery($resources = true)
	{
		$file = ( $resources ) ? RESOURCES_GLOBAL . '/js/jquery.js' : '//code.jquery.com/jquery-latest.min.js';
		self::AddScript($file);
	}
	
	###############################################################
	## Agregar una meta etiqueta.
	## - $name: 		Nombre de la META.
	## - $content: 		Contenido/Valor.
	## - $type: 		Tipo.
	###############################################################
	static function AddMeta($name, $content, $type = 'name')
	{
		$html = new Html('meta');
		$html->Set($type, $name)->Set('content', $content);

		self::$METAS .= '	' . $html->Build() . "\r\n";
	}
	
	###############################################################
	## Agregar un archivo enlazado.
	## - $file: 	Ruta del archivo.
	## - $rel: 		Rel.
	## - $id: 		ID del elemento.
	## - $media: 	Media.
	###############################################################
	static function AddLink($file, $rel = 'stylesheet', $id = '', $media = '')
	{
		$html = new Html('link');
		$html->Set('href', $file);

		if( !empty($rel) )
			$html->Set('rel', $rel);

		if( !empty($id) )
			$html->Set('id', $id);

		if( !empty($media) )
			$html->Set('media', $media);
		
		self::$LINKS .= '	' . $html->Build() . "\r\n";	
		return true;
	}
	
	###############################################################
	## Agregar un archivo de estilo desde una ubicación local.
	## - $file: 			Archivo CSS.
	## - $global (bool): 	¿De los recursos globales?
	## - $external (bool): 	¿De los recursos globales externos?
	###############################################################
	static function AddLocalStyle($file, $global = false, $external = false)
	{
		$path = ( !$global ) ? RESOURCES . '/css' : RESOURCES_GLOBAL . '/css';

		if( $external )
			$path = $path . '/external';
			
		self::AddLink("$path/$file.css");
	}
	
	###############################################################
	## Agregar un archivo JavaScript.
	## - $file: 		Ruta del archivo.
	## - $async (bool): ¿Async?
	## - $id: 			ID del elemento.
	###############################################################
	static function AddScript($file, $async = false, $id = '')
	{
		$html = new Html('script');
		$html->Set('src', $file);
		
		if( !empty($id) )
			$html->Set('id', $id);
		
		if( $async )
			$html->Set('async', $async);
			
		self::$JS .= '	' . $html->Build() . "\r\n";
	}
	
	###############################################################
	## Agregar un archivo Javascript desde una ubicación local.
	## - $file: 			Archivo JavaScript.
	## - $global (bool): 	¿De los recursos globales?
	## - $external (bool): 	¿De los recursos globales externos?
	###############################################################
	static function AddLocalScript($file, $global = false, $external = false)
	{
		$path = ( !$system ) ? RESOURCES . '/js' : RESOURCES_GLOBAL . '/js';
			
		if( $external )
			$path = $path . '/external';
			
		self::AddScript("$path/$file.js");
	}
	
	###############################################################
	## Agregar variable/función/definición JavaScript.
	## - $param:	Nombre de la Variable/Función/Definición.
	## - $value: 	Valor.
	## - $string: 	¿Es una cadena?
	###############################################################
	static function AddVar($param, $value, $string = true)
	{
		if( $string )
			$value = '"' . $value . '"';
		
		$html = "$param = $value;\r\n";
		self::$VARS .= $html;
	}
	
	###############################################################
	## Agregar código HTML a la cabecera.
	## - $html: HTML
	###############################################################
	static function AddStuff($html)
	{
		self::$STUFF .= "	$html\r\n";
	}

	###############################################################
	## Agregar atributos a la etiqueta <html>
	## - $param: Parametro.
	## - $value: Valor.
	###############################################################
	static function AttrHTML($param, $value = '')
	{
		$html = $param;

		if( !empty($value) )
			$html .= "=\"$value\"";

		$html .= ' ';
		self::$ATTRS_HTML .= $html;
	}

	###############################################################
	## Agregar atributo a la etiqueta <head>
	## - $param: Parametro.
	## - $value: Valor.
	###############################################################
	static function AttrHead($param, $value = "")
	{
		$html = $param;

		if( !empty($value) )
			$html .= "=\"$value\"";

		$html .= ' ';
		self::$ATTRS_HEAD .= $html;
	}
	
	###############################################################
	## Agregar una tarea para la barra de tareas especial de Internet Explorer 9+
	## - $name: 	Nombre de la tarea.
	## - $url: 		Dirección web de la tarea.
	## - $icon: 	Dirección web del icono.
	###############################################################
	static function IETask($name, $url, $icon = '')
	{
		self::AddMeta('msapplication-task', "name=$name;action-uri=$url;icon-uri=$icon");
	}
	
	###############################################################
	## Ejecutar un código JavaScript al terminar de cargar la página.
	## - action: 	Acción JavaScript.
	###############################################################
	static function JSAction($action)
	{			
		self::$JAVASCRIPT .= " $action ";
	}
	
	###############################################################
	## Ejecutar una alerta JavaScript.
	## - $message: 	Mensaje.
	###############################################################
	static function JSAlert($message)
	{
		self::JSAction("alert('$message'); ");
	}

	###############################################################
	## Envio de cabeceras para permitir solicitudes Cross-Domain.
	## - $domain: 			Dominio(s) a permitir.
	## - $max_age (int): 	Duración máxima de la petición.
	## - $methods: 			Métodos permitidos.
	###############################################################
	static function AllowCross($domain, $max_age = 3628800, $methods = 'PUT, DELETE, POST, GET')
	{
		header('Access-Control-Allow-Origin: ' . 	$domain);
		header('Access-Control-Max-Age: ' . 		$max_age);
		header('Access-Control-Allow-Methods: ' . 	$methods);
	}
	
	###############################################################
	## Envio de cabeceras para la protección de Frames.
	## - $frame: 	De donde permitir.
	###############################################################
	static function Protect($frame = 'SAMEORIGIN')
	{
		header('X-Frame-Options: ' . $frame);
		header('X-XSS-Protection: 1; mode=block');
	}

	###############################################################
	## Envio de cabeceras para simular una imagen.
	## - $type: 	Tipo de imagen.
	###############################################################
	static function Image($type = 'PNG')
	{
		header('Content-type: image/' . $type);
	}

	###############################################################
	## Envio de cabeceras para la descarga de un archivo.
	## - $file: 	Ruta del archivo.
	## - $name: 	Nombre del archivo.
	## - $mimetype: Mimetype del archivo.
	###############################################################
	static function Download($file, $name, $mimetype = '')
	{
		if( empty($mimetype) )
			$mimetype = Io::Mimetype($file);

		header('Content-Type: ' . 	$mimetype);
		header('Content-Length: ' . filesize($file));
		header('Content-Disposition: attachment; filename="' . $name . '"');
		header('Accept-Ranges: bytes');

		echo Io::Read($file);
	}
}
?>