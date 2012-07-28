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
	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	static function Error($code, $function, $message = '')
	{
		Lang::SetSection("mod.gd");

		BitRock::SetStatus($message, __FILE__, array('function' => $function));
		BitRock::LaunchError($code);
	}
	
	// Detectar tipo de imagen.
	// - $path: Ruta del archivo de imagen.
	static function ImgType($path)
	{
		if(!file_exists($path))
			self::Error('gd.noexist', __FUNCTION__);

		$mime = Core::MimeType($path);

		if($mime == 'image/png')
			$img = ImageCreateFromPng($path);
	
		if($mime == 'image/jpeg')
			$img = ImageCreateFromJpeg($path);
			
		if($mime == 'image/gif')
			$img = ImageCreateFromGif($path);
			
		if(!isset($img))
			self::Error('gd.verify', __FUNCTION__);
			
		return $img;
	}
	
	// Tomar un "pantallazo" a la web especificada.
	// - $url: Dirección del sitio web.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retornarán los datos/bits)
	// - $width (Int): Ancho de la imagen.
	// - $persistent (Bool): ¿Esperar hasta que el pantallazo este listo?
	static function SnapshotWeb($url, $to = '', $width = 300, $persistent = true)
	{
		if(!Core::isValid($url, 'url') OR !is_numeric($width))
			return false;
		
		$url 		= rawurlencode($url);
		$ready		= false;
		$attempts 	= 0;

		$default 	= file_get_contents(RESOURCES_SYS . '/images/default.snapshot.png');
		$default2 	= file_get_contents(RESOURCES_SYS . '/images/default.snapshot.gif');

		while(!$ready)
		{
			$data = file_get_contents('http://s.wordpress.com/mshots/v1/'.$url.'?w='.$width);

			if($persistent == true AND $attempts <= 20)
			{
				if($data == $default OR $data == $default2 OR empty($data))
				{
					++$attempts;
					sleep(3);
				}
				else
					$ready = true;
			}
			else
				$ready = true;
		}		
		
		if(empty($to))		
			return $data;
		
		Io::Write($to, $data);
		return true;
	}
	
	// Tomar una foto del escritorio del servidor. (Solo Windows)
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $quality (1 a 9): Calidad del archivo.
	static function SnapshotDesktop($to = '', $quality = 9)
	{
		if(PHP_OS !== 'WINNT')
			return false;
		
		if(empty($to))
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}
		
		$img = imagegrabscreen();
		$res = imagepng($img, $to, $quality);
		imagedestroy($img);

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
	
	// Tomar una foto de la ventana especificada del servidor. (Solo Windows)
	// - $id: Identificación COM de la ventana.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $quality (1 a 9): Calidad del archivo.
	static function SnapshotWindow($id, $to = '', $quality = 9)
	{
		if(PHP_OS !== 'WINNT')
			return false;
		
		if(empty($to))
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}
		
		$w = new COM($id);
		$w->Visible = true;
		$h = $w->HWND;
		
		$img = imagegrabwindow($h);
		$res = imagepng($img, $to, $quality, PNG_ALL_FILTERS);
		imagedestroy($img);
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
	
	// Redimensionar una imagen.
	// - $file: Ruta/Dirección web/Datos/Bits de la imagen a redimensionar.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $width (Int): Nuevo ancho.
	// - $height (Int): Nueva altura.
	// - $trans (Bool): ¿Transparencia?
	// - $quality (1 a 9): Calidad/Compresión del archivo.
	// - $adapt (Bool): Adaptación proporcionada del tamaño según ancho y altura.
	static function Resize($file, $to = '', $width, $height, $trans = true, $quality = 9, $adapt = false)
	{
		if(Core::isValid($file, 'url'))
			$file = Io::Read($file);

		if(!file_exists($file))
			$file = Io::SaveTemporal($file);
			
		if(empty($to))
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}
		
		$img = self::ImgType($file);
		
		if($adapt)
		{
			$new_width 	= ImageSx($img);
			$new_height = ImageSy($img);
			
			$ar 	= $new_height / $new_width;
			$ar2 	=  $new_width / $new_height;
			
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
		imagedestroy($img);
		
		if(!$res)
			return false;
			
		if($temp)
		{
			$img = Io::Read($to);			
			return $img;
		}
		
		return true;		
	}

	// Acceso directo a un filtro de escala de grises.
	// - $file: Ruta/Dirección web/Datos/Bits de la imagen a redimensionar.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $quality (1 a 9): Calidad/Compresión del archivo. 
	static function Grayscale($file, $to = '', $quality = 9)
	{
		return self::Filter($file, GRAYSCALE, $to, $quality);
	}

	// Aplicar difuminación a una imagen.
	// - $file: Ruta/Dirección web/Datos/Bits de la imagen a redimensionar.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $scale: Escala de difuminación.
	// - $quality (1 a 9): Calidad/Compresión del archivo. 
	static function Blur($file, $to = '', $scale = 5, $quality = 9)
	{
		if(Core::isValid($file, 'url'))
			$file = Io::Read($file);

		if(!file_exists($file))
			$file = Io::SaveTemporal($file);

		if(empty($to))
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}

		$img 	= self::ImgType($file);
		imagefilter($img, IMG_FILTER_PIXELATE, $scale);

		$res = imagepng($img, $to, $quality);
		imagedestroy($img);

		if(!$res)
			return false;

		if($temp)
		{
			$img = Io::Read($to);
			return $img;
		}

		return true;
	}

	// Aplicar un filtro a una imagen.
	// - $file: Ruta/Dirección web/Datos/Bits de la imagen a redimensionar.
	// - $filter: Filtro a aplicar.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $quality (1 a 9): Calidad/Compresión del archivo.
	static function Filter($file, $filter, $to = '', $quality = 9)
	{
		$types = array(
			NEGATE, GRAYSCALE, EDGEDETECT, EMBOSS, MEAN_REMOVAL
		);

		if(!in_array($filter, $types))
			self::Error('gd.filter', __FUNCTION__);

		if(Core::isValid($file, 'url'))
			$file = Io::Read($file);

		if(!file_exists($file))
			$file = Io::SaveTemporal($file);

		if(empty($to))
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}

		switch($filter)
		{
			case NEGATE:
				$filt = IMG_FILTER_NEGATE;
			break;

			case GRAYSCALE:
				$filt = IMG_FILTER_GRAYSCALE;
			break;

			case EDGEDETECT:
				$filt = IMG_FILTER_EDGEDETECT;
			break;

			case EMBOSS:
				$filt = IMG_FILTER_EMBOSS;
			break;

			case MEAN_REMOVAL:
				$filt = IMG_FILTER_MEAN_REMOVAL;
			break;
		}

		$img 	= self::ImgType($file);
		imagefilter($img, $filt);

		$res = imagepng($img, $to, $quality);
		imagedestroy($img);

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