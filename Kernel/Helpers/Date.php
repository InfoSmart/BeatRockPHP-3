<?
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
 * @package 	Date
 * Contiene funciones para el procesamiento de fechas.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

const 	DASH 	= 1,
		SLASH 	= 2,
		THE 	= 3;

class Date
{
	/**
	 * Convierte un tiempo unix en una fecha con formato.
	 * @param string  $time Tiempo unix
	 * @param boolean $hour ¿Incluir hora?
	 * @param integer $type Tipo de separación deseada.
	 */
	static function TimeDate($time = '', $hour = false, $type = DASH)
	{
		# No hay tiempo definido, usar el tiempo unix actual.
		if ( !is_numeric($time) )
			$time = time();

		switch ( $type )
		{
			# Separación por -
			case DASH:
			default:
				$date = date('d', $time) . '-' . self::GetMonth(date('m', $time)) . '-' . date('Y', $time);
			break;

			# Separación por /
			case SLASH:
				$date = date('d', $time) . '/' . self::GetMonth(date('m', $time)) . '/' . date('Y', $time);
			break;

			# Separación por la palabra "de"
			case THE:
				# El Inglés tiene su caso especial.
				if ( LANG == 'en' )
					$date = self::GetMonth(date('m', $time)) . ' ' . date('d', $time) . ', ' . date('Y', $time);
				else
					$date = date('d', $time) . ' %the% ' . ucwords(self::GetMonth(date('m', $time))) . ' %the% ' . date('Y', $time);

				$date = _l($date, 'global');
			break;
		}

		# Incluir hora.
		if ( $hour )
			$date .= ' ' . date('H:i:s', $time);

		return $date;
	}

	/**
	 * Calcula el tiempo restante/faltante para llegar al tiempo especificado.
	 * @param mixed  $date  	Tiempo unix o cadena de tiempo.
	 * @param boolean $onlyNum  ¿Devolver solo la cantidad?
	 */
	static function CalculateTime($date, $onlyNum = false)
	{
		Lang::SetSection('global');

		$int = array('%second%', '%minute%', '%hour%', '%day%', '%week%', '%month%', '%year%');
		$dur = array(60, 60, 24, 7, 4.35, 12, 12);

		# El tiempo no es númerico, intentar transformarlo.
		if ( !is_numeric($date) )
			$date = strtotime($date);

		# El formato de tiempo no es válida.
		if ( !$date )
			return false;

		$now 	= time();
		$time 	= $date;

		# A partir de ahora, calculos matematicos básicos. (Ni dios entiende esto)

		if ( $now > $time )
		{
			$dif = $now - $time;
			$str = '%ago%';
		}
		else if ( $now == $time )
			return _l('%now%');
		else
		{
			$dif = $time - $now;
			$str = '%within%';
		}

		for ( $j = 0; $dif >= $dur[$j] && $j < count($dur) - 1; ++$j )
			$dif /= $dur[$j];

		$dif = round($dif);

		if ( $dif !== 1 )
		{
			$int[5] 	.= "e";
			$int[$j] 	.= "s";
		}

		# FIXME: Esto tiene un problema de codificación...
		$result = ( $onlyNum ) ? _l("$dif") . ' ' .  _l("$int[$j]") : _l("$str $dif") . " " . _l("$int[$j]");
		return $result;
	}

	/**
	 * Convierte el mes númerico de un formato de fecha o tiempo unix
	 * en cadena. Es decir 02 lo converteria a Febrero.
	 * @param mixed $date 		Tiempo unix o formato de fecha (Separación: -, / o "de")
	 * @param integer $month  	Ubicación desde 0 a 2 en donde se encontraría el mes en un array.
	 * @param integer $year 	Ubicación desde 0 a 2 en donde se encontraría el año en un array.
	 * @param integer $day 		Ubicación desde 0 a 2 en donde se encontraría el día en un array.
	 */
	static function MonthString($date, $month = 1, $year = 2, $day = 0)
	{
		# Es tiempo unix, sencillo.
		if ( is_numeric($date) )
			return self::GetMonth($date);

		# La fecha contiene separación por -
		# Ejemplo: 04-05-1995
		if ( Contains($date, '-') )
		{
			$sep 	= '-';
			$split 	= explode('-', $date);
		}

		# La fecha contiene separación por /
		# Ejemplo: 04/05/1995
		if ( Contains($date, '/') )
		{
			$sep 	= '/';
			$split 	= explode('/', $date);
		}

		# La fecha contiene separación por "de"
		# Ejemplo: 04 de 05 de 1995
		if ( Contains($date, _l('%the%', '', 'global')) )
		{

			$sep 	= _l(' %the% ', '', 'global');
			$split 	= explode(_l(' %the% ', '', 'global'), $date);
		}

		# Obtenemos el mes.
		$newMonth = self::GetMonth($split[$month]);

		# Variable temporal para devolver en el mismo orden que se obtuvo el formato.
		# FIXME: ¿Hacer algo mejor?
		$tmp[$month] 	= $newMonth;
		$tmp[$year] 	= $split[$year];
		$tmp[$day] 		= $split[$day];

		return $tmp[0] . $sep . $tmp[1] . $sep . $tmp[2];
	}

	/**
	 * Obtiene una lista de los meses traducidos en el idioma del visitante.
	 * @return array Lista
	 */
	static function GetListMonths()
	{
		Lang::SetSection('global');

		$result 	= array();
		$calendar 	= array(
          '01' => '%january%',
          '02' => '%february%',
          '03' => '%march%',
          '04' => '%april%',
          '05' => '%may%',
          '06' => '%june%',
          '07' => '%july%',
          '08' => '%august%',
		  '09' => '%september%',
          '10' => '%october%',
          '11' => '%november%',
		  '12' => '%december%'
		);

		foreach ( $calendar as $key => $value )
			$result[$key] = _l($value);

		return $result;
	}

	/**
	 * Convierte el valor numérico de un mes en una cadena en el idioma del visitante.
	 * @param integer $num  Mes numérico.
	 * @param boolean $c   	¿Retornar el nombre completo del mes? (De otra manera solo devolverá sus 3 primeras letras)
	 */
	static function GetMonth($num, $c = false)
	{
		Lang::SetSection('global');

		$calendar = array(
          '01' 	=> '%january%',
          '02' 	=> '%february%',
          '03' 	=> '%march%',
          '04' 	=> '%april%',
          '05' 	=> '%may%',
          '06' 	=> '%june%',
          '07' 	=> '%july%',
          '08' 	=> '%august%',
		  '09' 	=> '%september%',
          '10' 	=> '%october%',
          '11' 	=> '%november%',
		  '12'	=> '%december%',
		  '1' 	=> '%january%',
          '2' 	=> '%february%',
          '3' 	=> '%march%',
          '4' 	=> '%april%',
          '5' 	=> '%may%',
          '6' 	=> '%june%',
          '7' 	=> '%july%',
          '8' 	=> '%august%',
		  '9' 	=> '%september%'
		);

		foreach ( $calendar as $key => $month )
		{
			if ( preg_match("/$key/", $num) )
			{
				$month = strtolower(_l($month));
				return ($c) ? $month : substr($month, 0, 3);
			}
		}

		return _l('%unknow%');
	}

	/**
	 * Convierte un mes en cadena a su valor numérico.
	 * Para mayor compatibilidad entre idiomas, el mes debe ser escrito en inglés.
	 * @param string $name Mes.
	 */
	static function GetMonthNum($name)
	{
		Lang::SetSection('global');

		$calendar = array(
          '01' => 'january',
          '02' => 'february',
          '03' => 'march',
          '04' => 'april',
          '05' => 'may',
          '06' => 'june',
          '07' => 'july',
          '08' => 'august',
		  '09' => 'september',
          '10' => 'october',
          '11' => 'november',
		  '12' => 'december'
		);

		foreach ( $calendar as $key => $month )
		{
			$month = strtolower($month);

			if ( preg_match("/$month/i", $name) )
				return _l($key);

			$month = substr($month, 0, 3);

			if ( preg_match("/$month/i", $name) )
				return _l($key);
		}

		return _l('%unknow%');
	}
}
?>