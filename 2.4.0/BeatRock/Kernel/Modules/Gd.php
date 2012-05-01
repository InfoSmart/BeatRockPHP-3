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
		if(Contains($path, ".png", true))
			$img = ImageCreateFromPng($path);
	
		if(Contains($path, ".jpeg", true) OR Contains($path, ".jpg", true))
			$img = ImageCreateFromJpeg($path);
			
		if(Contains($path, ".gif", true) OR Contains($path, ".jpg", true))
			$img = ImageCreateFromGif($path);
			
		if(empty($img))
			self::Error('01g', __FUNCTION__);
			
		return $img;
	}
	
	// Función - Tomar un "pantallazo" a la web especificada.
	// - $url: Dirección del sitio web.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retornarán los datos/bits)
	// - $width (Int): Ancho de la imagen.
	public static function SnapshotWeb($url, $to = '', $width = 300)
	{
		if(!Core::isValid($url, 'url') OR !is_numeric($width))
			return false;
		
		$url = rawurlencode($url);

		$default = Io::Read(RESOURCES_SYS . '/images/default.snapshot.png');
		$default2 = Io::Read(RESOURCES_SYS . '/images/default.snapshot.gif');
			
		Curl::Init('http://s.wordpress.com/mshots/v1/'.$url.'?w='.$width);
		$data = Curl::Get();
		
		if($data == $default OR $data == $default2 OR empty($data))
		{
			sleep(5);
			return self::SnapshotWeb($url, $to, $width);
		}
		
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
		if(PHP_OS !== 'WINNT')
			return false;
		
		if(empty($to))
		{
			$temp = true;
			$to = BIT . 'Temp' . DS . Core::Random(10) . '.png';
		}
		
		$img = imagegrabscreen();
		$res = imagepng($img, $to, $quality);

		if(!$res)
			return false;
		
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
		if(PHP_OS !== 'WINNT')
			return false;
		
		if(empty($to))
		{
			$temp = true;
			$to = BIT . 'Temp' . DS . Core::Random(10) . '.png';
		}
		
		$w = new COM($id);
		$w->Visible = true;
		$h = $w->HWND;
		
		$img = @imagegrabwindow($h);
		$res = @imagepng($img, $to, $quality, PNG_ALL_FILTERS);
		$w->Quit();
		
		if(!$res)
			return false;
		
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
		if(!file_exists($file))
			$file = Io::SaveTemporal($file);
			
		if(empty($to))
		{
			$temp = true;
			$to = BIT . 'Temp' . DS . Core::Random(10) . '.png';
		}
		
		$img = self::ImgType($file);
		
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
		
		$thu = imagecreatetruecolor($width, $height);
		
		if($trans)
		{
			$tra = imagecolorallocate($thu, 0, 0, 0);		
			imagecolortransparent($thu, $tra);
		}
		
		imagecopyresampled($thu, $img, 0, 0, 0, 0, $width, $height, ImageSX($img), ImageSY($img));		
		$res = imagepng($thu, $to, $quality, PNG_ALL_FILTERS);
		
		if(!$res)
			return false;
			
		if($temp)
		{
			$img = Io::Read($to);			
			return $img;
		}
		
		return true;		
	}
}
?>