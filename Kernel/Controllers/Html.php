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

/*
	Basado en la aportación de:	
	http://davidwalsh.name/create-html-elements-php-htmlelement-class
*/

class Html
{
	var $element 	= '';
	var $attrs 		= array();
	var $param 		= '';

	// Iniciar nueva instancia de creación de elemento HTML.
	// - $element: Elemento.
	// - $param: Nombre del parametro de plantilla para usar.
	function __construct($element, $param = '')
	{
		$this->element 	= strtolower($element);
		$this->param 	= $param;

		return $this;
	}

	// Definir un atributo.
	// - $param: Nombre del atributo.
	// - $value: Valor del atributo.
	function Set($param, $value = '')
	{
		if(!is_array($param))
			$this->attrs[$param] = $value;
		else
			$this->attrs = array_merge($this->attrs, $param);

		return $this;
	}

	// Remover un atributo.
	// - $param: Nombre del atributo.
	function Remove($param)
	{
		if(!isset($this->attrs[$param]))
			return;

		unset($this->attrs[$param]);
	}

	// Limpiar todos los atributos.
	function Clear()
	{
		$this->attrs = array();
	}

	// Agrear esta instancia a otra ya creada.
	// - $object: Instancia del tipo "Html".
	function Add($object)
	{
		if(@get_class($object) == __CLASS__)
			$this->attrs['text'] .= $object->Build();
	}

	//
	function Build()
	{
		return (Jade::Enabled() == true) ? self::Build_Jade() : self::Build_HTML();
	}

	// Generar código HTML resultante.
	function Build_HTML()
	{
		$html = '<' . $this->element;
		$atts = $this->attrs;

		if(!empty($atts))
		{
			foreach($atts as $param => $value)
			{
				if($param == 'text' AND $param !== 0)
					continue;

				if(is_numeric($param))
				{
					$param = $value;
					$value = '';
				}

				$html .= ' '.$param;

				if(!empty($value))
					$html .= '="'.$value.'"';
			}
		}

		$close = array('input', 'img', 'hr', 'br', 'meta', 'link');

		if(in_array($this->element, $close))
			$html .= ' />';
		else
			$html .= '>'.$this->attrs['text'].'</'.$this->element.'>';

		if(!empty($param))
			Tpl::Set($param, $html);

		return $html;
	}

	function Build_Jade()
	{
		$html = $this->element;
		$atts = $this->attrs;

		if(!empty($atts))
		{
			$html 	.= '(';
			$i 		= 0;

			foreach($atts as $param => $value)
			{
				if($param == 'text')
					continue;

				++$i;
				$html .= $param . '="' . $value . '"';

				if($i < count($atts))
					$html .= ', ';
			}

			$html .= ')';
		}

		$html .= ' ' . $attrs['text'];

		if(!empty($param))
			Tpl::Set($param, $html);

		return $html;
	}
}
?>