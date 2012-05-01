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
if(!defined("BEATROCK"))
	exit;	

class Gd
{
	// Función privada - Lanzar error.
	// - $function: Función causante.
	// - $msg: Mensaje del error.
	private static function Error($code, $function, $msg = '')
	{
		BitRock::setStatus($msg, __FILE__, Array('function' => $function));
		BitRock::launchError($code);
	}
	
	// Función privada - Detectar tipo de imagen.
	// - $path: Ruta del archivo de imagen.
	private static function ImgType($path)
	{
		$img = @ImageCreateFromPng($path);
	
		if(empty($img))
			$img = @ImageCreateFromJpeg($path);
			
		if(empty($img))
			$img = @ImageCreateFromGif($path);
			
		if(empty($img))
			self::Error("01g", __FUNCTION__);
			
		return $img;
	}
	
	// Función - Tomar una foto a un sitio web.
	// - $url: Dirección del sitio web.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $width (Int): Ancho de la imagen.
	public static function SnapshotWeb($url, $to = '', $width = 300)
	{
		// La dirección web no es válida o el ancho no es válido.
		if(!Core::isValid($url, "url") OR !is_numeric($width))
			return false;
		
		// Codificar Url.
		$url = rawurlencode($url);
		// Datos de la imagen "Por favor espere..."
		$default = Io::Read(RESOURCES_SYS . '/images/default.snapshot.png');
		$default2 = Io::Read(RESOURCES_SYS . '/images/default.snapshot.gif');
			
		// Obtener la imagen gracias al servicio de Wordpress.
		Curl::Init("http://s.wordpress.com/mshots/v1/$url?w=$width");
		$data = Curl::Get();
		
		// La imagen aún no esta lista.
		if($data == $default OR $data == $default2)
		{
			// Esperar 5 segundos e intentar de nuevo.
			sleep(5);
			return self::SnapshotWeb($url, $to, $width);
		}
		
		// No hay donde guardar, mostrar los datos/bits.
		if(empty($to))		
			return $data;
		
		Io::Write($to, $data);
		return true;
	}
	
	// Función - Tomar una foto del escritorio del servidor. (Solo Windows)
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $quality (1 a 9): Calidad del archivo.
	public static function SnapshotDesktop($to = '', $quality = 9)
	{
		// Solo Windows.
		if(PHP_OS !== "WINNT")
			return false;
		
		// Si no hay donde guardar, guardarlo en un archivo temporal.
		if(empty($to))
		{
			$temp = true;
			$to = BIT . 'Temp' . DS . Core::Random(10) . '.png';
		}
		
		// Tomar imagen del escritorio y ponerlo en una imagen PNG.
		$img = imagegrabscreen();
		$res = imagepng($img, $to, $quality);
		
		// Sucedio un error :(
		if(!$res)
			return false;
		
		// Leer archivo temporal de la imagen y mostrar datos.
		if($temp)
		{
			$img = Io::Read($to);
			unlink($to);
			
			return $img;
		}
		
		return true;
	}
	
	// Función - Tomar una foto de la ventana especificada del servidor. (Solo Windows)
	// - $id: Identificación COM de la ventana.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $quality (1 a 9): Calidad del archivo.
	public static function SnapshotWindow($id, $to = '', $quality = 9)
	{
		// Solo Windows.
		if(PHP_OS !== "WINNT")
			return false;
		
		// Si no hay donde guardar, guardarlo en un archivo temporal.		
		if(empty($to))
		{
			$temp = true;
			$to = BIT . 'Temp' . DS . Core::Random(10) . '.png';
		}
		
		// Abrir nueva ventana.
		$w = new COM($id);
		$w->Visible = true;
		$h = $w->HWND;
		
		// Tomar imagen de la ventana, ponerlo en una imagen PNG y cerrarla.
		$img = @imagegrabwindow($h);
		$res = @imagepng($img, $to, $quality, PNG_ALL_FILTERS);
		$w->Quit();
		
		// Sucedio un error :(
		if(!$res)
			return false;
		
		// Leer archivo temporal de la imagen y mostrar datos.
		if($temp)
		{
			$img = Io::Read($to);
			unlink($to);
			
			return $img;
		}
		
		return true;
	}
	
	// Función - Redimensionar una imagen.
	// - $file: Ruta/Dirección web/Datos/Bits de la imagen a redimensionar.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $width (Int): Nuevo ancho.
	// - $height (Int): Nueva altura.
	// - $trans (Bool): ¿Transparencia?
	// - $quality (1 a 9): Calidad del archivo.
	// - $adapt (Bool): Adaptación proporcionada del tamaño según ancho y altura.
	public static function Resize($file, $to = '', $width, $height, $trans = true, $quality = 9, $adapt = false)
	{
		// El archivo no existe, son datos/bits, guardarlos en un archivo temporal.
		if(!file_exists($file))
			$file = Io::SaveTemporal($file);
		
		// Si no hay donde guardar, guardarlo en un archivo temporal.		
		if(empty($to))
		{
			$temp = true;
			$to = BIT . 'Temp' . DS . Core::Random(10) . '.png';
		}
		
		// Detectar tipo de la imagen.
		$img = self::ImgType($file);
		
		// Realizando proceso de adaptación.
		if($adapt)
		{
			$new_width = ImageSx($img);
			$new_height = ImageSy($img);
			
			$ar = $new_height / $new_width;
			$ar2 =  $new_width / $new_height;
			
			if($new_width >= $new_height)
				$height = abs($width * $ar);
			else
				$width = abs($height * $ar2);
		}
		
		// Crear una imagen.
		$thu = imagecreatetruecolor($width, $height);
		
		// Realizar proceso de transparencia.
		if($trans)
		{
			$tra = imagecolorallocate($thu, 0, 0, 0);		
			imagecolortransparent($thu, $tra);
		}
		
		// Procesando y guardando la imagen redimensionada.
		imagecopyresampled($thu, $img, 0, 0, 0, 0, $width, $height, ImageSX($img), ImageSY($img));		
		$res = imagepng($thu, $to, $quality, PNG_ALL_FILTERS);
		
		// Ha ocurrido un error :(
		if(!$res)
			return false;
		
		// Leer archivo temporal de la imagen y mostrar datos.		
		if($temp)
		{
			$img = Io::Read($to);			
			return $img;
		}
		
		return true;		
	}
}
?>