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

class Media
{
	var $element = '';
	
	// Preparar un nuevo elemento de Audio.
	// - $type (audio, video): Tipo del elemento.
	// - $data (Array): Opciones.
	function __construct($type = 'audio', $data = array('controls'))
	{
		if($type !== 'audio' AND $type !== 'video')
			return;

		$element = new Html($type);

		foreach($data as $param => $value)
			$element->Set($param, $value);

		$this->element = $element;
	}
	
	// Agregar un nuevo archivo de media al elemento activo.
	// - $src: Ruta del archivo.
	// - $type (audio/mpeg, audio/ogg): Tipo del archivo.
	function Add($src, $type = "audio/mpeg")
	{
		$source = new Html('source');
		$source->Set('src', $src);
		$source->Set('type', $type);

		$this->element->Add($source);
	}
	
	// Mostrar el HTML del elemento.
	function Show()
	{
		return $this->element->Build();
	}
	
	// Preparar un elemento de voz.
	// - $message: Mensaje corto.
	// - $lang: Lenguaje del mensaje.
	// - $controls: ¿Mostrar controles?
	// - $autoplay: ¿Autoreproducir?
	// - $id: ID del elemento.
	// - $class: Clase/Estilo del elemento.
	static function Voice($message, $lang = 'es', $data = '')
	{
		$message = urlencode($message);		
		Curl::Init('http://translate.google.com/translate_tts?q='.$message.'&tl='.$lang);

		$result = Curl::Get();
		$result = base64_encode($result);

		if(empty($data))
			$data = Array('controls', 'autoplay');

		$data['src'] = 'data:audio/mpeg;base64,' . $result;
		$data['type'] = 'audio/mpeg';

		$html = new Media('audio', $data);
		return $html->Show();
	}
}
?>