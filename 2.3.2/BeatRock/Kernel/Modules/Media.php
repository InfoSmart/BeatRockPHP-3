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

class Media
{
	private static $type = "";
	private static $html = "";
	
	// Función - Preparar un nuevo elemento de Audio.
	// - $type (audio, video): Tipo del elemento.
	// - $data (Array): Opciones.
	public static function Init($type = 'audio', $data = Array('controls' => 'controls'))
	{
		// Preparando HTML con audio o video.
		if($type == 'audio')
		{
			$html = "<audio";
			self::$type = "audio";
		}
		else if($type == "video")
		{
			$html = "<video";
			self::$type = "video";
		}
		else
			return;
		
		// Completar el elemento HTML dependiendo de las opciones especificadas.
		if(!empty($data['id']))
			$html .= " id='$data[id]'";
			
		if(!empty($data['class']))
			$html .= " class='$data[class]'";
			
		if(!empty($data['codec']))
			$html .= " type='$data[codec]'";
			
		if(!empty($data['autoplay']))
			$html .= " autoplay='$data[autoplay]'";
			
		if(!empty($data['controls']))
			$html .= " controls='$data[controls]'";
			
		if(!empty($data['loop']))
			$html .= " loop='$data[loop]'";
			
		if(!empty($data['preload']))
			$html .= " preload='$data[preload]'";
			
		if(!empty($data['width']))
			$html .= " width='$data[width]'";
			
		if(!empty($data['height']))
			$html .= " height='$data[height]'";
			
		if(!empty($data['src']))
			$html .= " src='$data[src]'";
			
		// ¡Listo!
		$html .= ">";
		self::$html = $html;
	}
	
	// Función - Agregar un nuevo archivo de media al elemento activo.
	// - $src: Ruta del archivo.
	// - $type (audio/mpeg, audio/ogg, etc...): Tipo del archivo.
	public static function Add($src, $type = "audio/mpeg")
	{
		$html = "<source src='$src'";
		
		if(!empty($type))
			$html .= " type='$type'";
			
		$html .= " />";
		self::$html .= $html;
	}
	
	// Función - Mostrar el HTML del elemento.
	public static function Show()
	{
		$type = self::$type;
		self::$html .= "</$type>";
		return self::$html;
	}
	
	// Función - Preparar un elemento de voz.
	// - $msg: Mensaje corto.
	// - $lang: Lenguaje del mensaje.
	// - $controls: ¿Mostrar controles?
	// - $autoplay: ¿Autoreproducir?
	// - $id: ID del elemento.
	// - $class: Clase/Estilo del elemento.
	public static function Voice($msg, $lang = 'es', $controls = 'controls', $autoplay = 'autoplay', $id = '', $class = '')
	{
		// Codificar el mensaje.
		$msg = urlencode($msg);
		
		// Obtener los datos del audio gracias al traductor de Google.
		Curl::Init("http://translate.google.com/translate_tts?q=$msg&tl=$lang");
		// Codificando los datos a Base64.
		$s = base64_encode(Curl::Get());
		
		// Preparando un elemento de audio con los datos.
		self::Init('audio', Array(
			"src" => "data:audio/mpeg;base64,$s",
			"controls" => $controls,
			"autoplay" => $autoplay,
			"id" => $id,
			"class" => $class,
			"codec" => "audio/mpeg"
		));
		
		return self::Show();
	}
}
?>