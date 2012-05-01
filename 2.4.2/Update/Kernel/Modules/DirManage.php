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

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////																											      
/////////		@AUTOR: Jordi Hoock Castro
/////////		@FUNCIONALIDAD: Administración y Manejo de Carpetas y Archivos.
/////////		@Adaptado a: BeatRock FrameWork.
/////////		@De (No Terminado) VERSIÓN BETA.
/////////																									//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(!defined('BEATROCK'))
	exit;

class DirManage
{
	//Creando los atributos necessarias para trabajar con la classe
	static $origin;
	static $rute;
	static $status = FALSE;

	// Función - Inicializar.
	static function Init($rute)
	{
		if(isset($rute))
		{
			self::$rute 	= opendir($rute);
			self::$origin 	= $rute;
			self::$status 	= TRUE;
		}
		else
			exit('<b>Es necesario una ruta inicial valida de sistemas Windows o Unix.</b>');
	}

	// Función - Indexar contenidos.
	// - $navigate: Permitir la entrada a directorios.
	static function list_content($navigate = TRUE)
	{
		if(self::$status !== TRUE)
			exit('<b>No se ha inicializado el Módulo.</b>');

		while($files = readdir(self::$rute))
		{
			if($files == '.')
				continue;

			$FILE_PATH = self::$origin . '/' . $files;
			$type = (is_dir($FILE_PATH)) ? 'Carpeta' : 'Archivo';

			echo '<b>' . $type . '</b>: <u>' . $files . '</u>';
				
			if($navigate !== TRUE AND $files !== '..')
				continue;

			if($type == 'Carpeta')
			{
				if($files == '..')
				{
					$rute = self::$origin;

					if(substr($rute, -1) == DS)
						$rute = substr($rute, 0, (strlen($rute) - 1));

					$folders = explode(DS, $rute);
					$DIR = '';

					foreach($folders as $i => $f)
					{
						if($i == (count($folders) - 1))
							continue;

						$DIR .= $f . DS;						
					}

					$PATH = preg_replace('/rute=(.*)/i', 'rute=' . $DIR, PATH_NOW);
				}
				else
				{
					$rute = self::$origin;
					$PATH = preg_replace('/rute=(.*)/i', 'rute=' . $rute.DS.$files, PATH_NOW);
				}

				echo '<br /><a href="' . $PATH . '">Entrar al directorio</a>';
			}

			echo '<br /><br />';
		}
	}

	// Función - Devolver lista de archivos.
	// - $sort:
	// - $show: Tipo de vista.
	static function return_array($sort = TRUE, $show = 'debug')
	{
		if(self::$status !== TRUE)
			exit('<b>No se ha inicializado el Módulo.</b>');

		if(is_bool($sort) && $sort === TRUE && isset($show))
		{
			$array =scandir(self::$origin, 1);
			return $list = ($show === 'debug') ? _r($array) : $array[$show];
		}
		elseif(is_bool($sort) && $sort === TRUE)
		{
			return _r($array =scandir(self::$origin, 0));
		}
	}

	//Permisos de archivos, no empezado.
	static function permissions()
	{

	}

	//Cerrar todas las conexiones existentes.
	static function close_all()
	{
		if(self::$status === TRUE)
		{
			closedir(self::$rute);
		}
	}

}
?>