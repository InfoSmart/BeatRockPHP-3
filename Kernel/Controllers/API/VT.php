<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart ? 2012 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

// Acci?n ilegal.
if(!defined('BEATROCK'))
	exit;	

class VT
{
	static $API = null;

	// Preparar e implementar la API.
	static function Init()
	{
		if(self::$API !== null)
			return true;

		require APP_CTRLS . 'External' . DS . 'virustotal' . DS . 'VirusTotal.php';
		$data = Social::$data['vt'];

		if(empty($data['apiKey']))
			return Social::Error('social.instance', __FUNCTION__, '%error.vt.data%');

		$API = new VirusTotal($data['apiKey']);

		if(!$API)
			return false;

		self::$API = $API;
		return true;
	}

	// Obteniendo y verificando el recurso de la API.
	static function API()
	{
		self::Init();
		$API = self::$API;

		if($API == null)
			return Social::Error('social.instance.fail', __FUNCTION__, '%error.vt%');

		return $API;
	}

	// Mandar a escanear un archivo.
	// - $file (direccion fisica, direccion web, bytes): Archivo de la foto.
	// - $filename: Nombre del archivo.
	// - $wait_results (bool): ?Esperar los resultados?
	static function Scan_File($file, $filename = '', $wait_results = false)
	{
		$API 				= self::API();
		$API->fileSupport 	= true;

		if(Core::Valid($file, 'url'))
			$file = Io::Read($file);

		if(!file_exists($file))
			$file = Io::SaveTemporal($file);

		$info = pathinfo($file);

		if(empty($filename))
			$filename = $info['basename'];
		try
		{
			$result = $API->api('file/scan', 'POST', array(
				'file'	=> '@' . $file . 
				';type=' . Io::Mimetype($file) . 
				';filename=' . $filename
			));
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		if($wait_results)
			$result = self::Get_File_Report($result['resource'], true);

		return $result;
	}

	// Obtener el reporte de un archivo.
	// - $resourceId: ID del archivo.
	// - $wait (bool): ?Esperar a que el reporte este listo?
	static function Get_File_Report($resourceId, $wait = false)
	{
		$API = self::API();

		try
		{
			$result = $API->api('file/report', 'POST', [
				'resource'	=> $resourceId
			]);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		if($wait)
		{
			set_time_limit(0);
			$ready = false;

			while(!$ready)
			{
				if(
					Contains($result['verbose_msg'], ['come back later', 'is not among the finished'])
					)
				{
					sleep(10);
					$result = self::Get_File_Report($resourceId);
				}
				else
					$ready = true;
			}
		}

		return $result;
	}

	// Mandar a escanear una direcci?n web.
	// - $url: Direcci?n web.
	// - $wait_results (bool): ?Esperar los resultados?
	static function Scan_Url($url, $wait_results = false)
	{
		$API = self::API();

		if(!Core::Valid($url, 'url'))
			return false;

		try
		{
			$result = $API->api('url/scan', 'POST', [
				'url'	=> $url
			]);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		if($wait_results)
			$result = self::Get_Url_Report($result['resource'], true);

		return $result;
	}

	// Obtener el reporte de una web.
	// - $resourceId: ID del archivo.
	// - $wait (bool): ?Esperar a que el reporte este listo?
	static function Get_Url_Report($resourceId, $wait = false)
	{
		$API = self::API();

		try
		{
			$result = $API->api('url/report', 'POST', [
				'resource'	=> $resourceId
			]);
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}

		if($wait)
		{
			set_time_limit(0);
			$ready = false;

			while(!$ready)
			{
				if(
					Contains($result['verbose_msg'], ['come back later', 'is not among the finished'])
					)
				{
					sleep(10);
					$result = self::Get_Url_Report($resourceId);
				}
				else
					$ready = true;
			}
		}

		return $result;
	}
}
?>