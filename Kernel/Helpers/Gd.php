<?php
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
 * @package 	Gd
 * Permite la creación y manipulación de imagenes.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

class Gd extends BaseStatic
{
	/**
	 * Crea un recurso de imágen a partir de su formato.
	 * @param string $path 	Ruta hacia la imágen.
	 * @return resource 	Recurso de imágen.
	 */
	static function ImgType($path)
	{
		# La imágen no existe.
		if ( !file_exists($path) )
			self::Error('gd.noexist');

		# Obtenemos el MimeType
		$mime = Io::Mimetype($path);

		if ( $mime == 'image/png' )
			$img = ImageCreateFromPng($path);

		if ( $mime == 'image/jpeg' )
			$img = ImageCreateFromJpeg($path);

		if ( $mime == 'image/gif' )
			$img = ImageCreateFromGif($path);

		# El formato de esta imágen es inválido
		# o no se pudo crear el recurso.
		if ( !isset($img) OR $img == false )
			self::Error('gd.verify');

		return $img;
	}

	/**
	 * Toma un "pantallazo" a un sitio web.
	 * @param string  $url        Dirección web.
	 * @param string  $to         Ruta del archivo donde se guardará el pantallazo.
	 * @param integer $width      Ancho de la imágen.
	 * @param boolean $persistent ¿Esperar hasta que el pantallazo este listo?
	 */
	static function SnapshotWeb($url, $to = '', $width = 300, $persistent = true)
	{
		# Esto no es una dirección web válida
		# o el ancho no es un valor numérico.
		if ( !Core::Valid($url, URL) OR !is_numeric($width) )
			return false;

		$url 		= rawurlencode($url);
		$ready		= false;
		$attempts 	= 0;

		$default = 'e89e34619e53928489a0c703c761cd58';

		# Mientras no estemos listos.
		while ( !$ready )
		{
			# Usamos la herramienta de Wordpress
			$data = file_get_contents('http://s.wordpress.com/mshots/v1/'.$url.'?w='.$width);

			# Debemos esperar hasta que el pantallazo se encuentre listo
			# y se han tenido menos de 20 intentos.
			if ( $persistent AND $attempts <= 20 )
			{
				# El pantallazo no se ha cargado.
				if ( md5($data) == $default OR empty($data) )
				{
					# Un intento más.
					++$attempts;
					# Intentarlo de nuevo en 2 segundos.
					sleep(2);
				}
				# Todo listo.
				else
					$ready = true;
			}
			# Todo listo.
			else
				$ready = true;
		}

		# No debemos guardar la imágen, retornamos el resultado.
		if ( empty($to) )
			return $data;

		# Guardar el pantallazo en una imágen.
		Io::Write($to, $data);
		return true;
	}

	/**
	 * Toma un "pantallazo" del escritorio del servidor (Solo Windows)
	 * @param string  $to      Ruta del archivo donde se guardará el pantallazo.
	 * @param integer $quality Calidad de imágen.
	 */
	static function SnapshotDesktop($to = '', $quality = 9)
	{
		# Esto solo funciona en Windows.
		if ( PHP_OS !== 'WINNT' )
			return false;

		# Devolveremos solo el pantallazo.
		if ( empty($to) )
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}

		$img = imagegrabscreen();
		$res = imagepng($img, $to, $quality);
		imagedestroy($img);

		# Hubo un problema al crear la imágen.
		if ( !$res )
			return false;

		# Devolvemos la imágen.
		if ( $temp )
		{
			$img = file_get_contents($to);
			unlink($to);

			return $img;
		}

		return true;
	}

	/**
	 * Toma un "pantallazo" de una ventana/programa del servidor. (Solo Windows)
	 * @param string  $id      Identificación COM de la ventana.
	 * @param string  $to      Ruta del archivo donde se guardará el pantallazo.
	 * @param integer $quality Calidad de imágen.
	 */
	static function SnapshotWindow($id, $to = '', $quality = 9)
	{
		# Esto solo funciona en Windows.
		if ( PHP_OS !== 'WINNT' )
			return false;

		# Devolveremos solo el pantallazo.
		if ( empty($to) )
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}

		$w 			= new COM($id);
		$w->Visible = true;
		$h 			= $w->HWND;

		$img = imagegrabwindow($h);
		$res = imagepng($img, $to, $quality, PNG_ALL_FILTERS);
		imagedestroy($img);
		$w->Quit();

		# Hubo un problema al crear la imágen.
		if ( !$res )
			return false;

		# Devolvemos la imágen.
		if ( $temp )
		{
			$img = file_get_contents($to);
			unlink($to);

			return $img;
		}

		return true;
	}

	/**
	 * Redimensiona una imágen.
	 * @param string  $file     Ruta de la imágen, dirección web o data.
	 * @param string  $to       Ruta del archivo donde guardar.
	 * @param integer $width    Nuevo ancho.
	 * @param integer $height   Nueva altura
	 * @param boolean $trans    ¿Permitir transparencia?
	 * @param integer $quality  Calidad de la imágen.
	 * @param boolean $adapt    ¿Usar adaptación inteligente de tamaño?
	 */
	static function Resize($file, $to = '', $width, $height, $trans = true, $quality = 9, $adapt = false)
	{
		# La imágen es una url.
		if ( Core::Valid($file, URL) )
			$file = file_get_contents($file);

		# La imágen no existe, tratarlo como los datos/bits.
		if ( !file_exists($file) )
			$file = Io::SaveTemporal($file, '.png');

		# Devolveremos la imágen.
		if ( empty($to) )
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}

		# Obtenemos el recurso dependiendo del tipo.
		$img = self::ImgType($file);

		# Usar adaptación inteligente.
		if ( $adapt )
		{
			$new_width 	= ImageSx($img);
			$new_height = ImageSy($img);

			$ar 	= $new_height / $new_width;
			$ar2 	=  $new_width / $new_height;

			if ( $new_width >= $new_height )
				$height = abs($width * $ar);
			else
				$width 	= abs($height * $ar2);
		}

		$thu = imagecreatetruecolor($width, $height);

		# Transparencia.
		if ( $trans )
		{
			$tra = imagecolorallocate($thu, 0, 0, 0);
			imagecolortransparent($thu, $tra);
		}

		imagecopyresampled($thu, $img, 0, 0, 0, 0, $width, $height, ImageSX($img), ImageSY($img));
		$res = imagepng($thu, $to, $quality, PNG_ALL_FILTERS);
		imagedestroy($img);

		# Hubo un problema al crear la imágen.
		if ( !$res )
			return false;

		# Devolvemos el resultado.
		if ( $temp )
		{
			$img = file_get_contents($to);
			return $img;
		}

		return true;
	}

	/**
	 * [Acceso directo] Aplica a una imágen un filtro de escala de grises.
	 * @param string  $file    Ruta de la imágen, dirección web o data.
	 * @param string  $to      Ruta del archivo donde guardar.
	 * @param integer $quality Calidad de la imágen.
	 */
	static function Grayscale($file, $to = '', $quality = 9)
	{
		return self::Filter($file, GRAYSCALE, $to, $quality);
	}

	// Aplicar difuminación a una imagen.
	// - $file: Ruta/Dirección web/Datos/Bits de la imagen a redimensionar.
	// - $to: Ruta del archivo donde guardar. (Si se deja vació se retorna los datos/bits)
	// - $scale: Escala de difuminación.
	// - $quality (1 a 9): Calidad/Compresión del archivo.
	/**
	 * Aplica a una imágen un filtro de difuminación.
	 * @param string  $file    Ruta de la imágen, dirección web o data.
	 * @param string  $to      Ruta del archivo donde guardar.
	 * @param integer $scale   Escala de difuminación
	 * @param integer $quality [description]
	 */
	static function Blur($file, $to = '', $scale = 5, $quality = 9)
	{
		# La imágen es una url.
		if ( Core::Valid($file, URL) )
			$file = file_get_contents($file);

		# La imágen no existe, tratarlo como los datos/bits.
		if ( !file_exists($file) )
			$file = Io::SaveTemporal($file);

		# Devolveremos la imágen.
		if ( empty($to) )
		{
			$temp 			= true;
			$to 			= BIT . 'Temp' . DS . Core::Random(10) . '.png';
			Io::$temp[] 	= $to;
		}

		# Obtenemos el recurso dependiendo del tipo.
		$img = self::ImgType($file);
		# Aplicamos el filtro.
		imagefilter($img, IMG_FILTER_PIXELATE, $scale);

		$res = imagepng($img, $to, $quality);
		imagedestroy($img);

		# Hubo un problema al crear la imágen.
		if ( !$res )
			return false;

		# Devolvemos el resultado.
		if ( $temp )
		{
			$img = file_get_contents($to);
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
		$types = array(f
			NEGATE, GRAYSCALE, EDGEDETECT, EMBOSS, MEAN_REMOVAL
		);

		if(!in_array($filter, $types))
			self::Error('gd.filter', __FUNCTION__);

		if(Core::Valid($file, 'url'))
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