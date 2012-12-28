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

class Date
{
	// Convertir tiempo Unix a tiempo en cadena.
	// - $time (Int): Tiempo Unix.
	// - $hour (Bool): ¿Incluir hora?
	// - $type (1, 2, 3): Tipo de separaci?n.
	static function TimeDate($time = '', $hour = false, $type = 1)
	{
		if(!is_numeric($time))
			$time = time();
			
		if(!is_numeric($type) OR $type < 1 OR $type > 3)
			$type = 1;
		
		if($type == 1)
			$date = date('d', $time) . '-' . self::GetMonth(date('m', $time)) . '-' . date('Y', $time);

		if($type == 2)
			$date = date('d', $time) . '/' . self::GetMonth(date('m', $time)) . '/' . date('Y', $time);

		if($type == 3)
		{
			if(LANG == 'en')
				$date = self::GetMonth(date('m', $time)) . ' ' . date('d', $time) . ', ' . date('Y', $time);
			else
				$date = date('d', $time) . ' %the% ' . ucwords(self::GetMonth(date('m', $time))) . ' %the% ' . date('Y', $time);

			$date = _l($date, 'global');
		}
		
		if($hour)
			$date .= ' - ' . date('H:i:s', $time);
			
		return $date;
	}

	// Calcular tiempo restante/faltante.
	// - $date: Tiempo Unix o cadena de tiempo.
	// - $num: Devolver solo el numero y tipo.
	static function CalculateTime($date, $num = false)
	{
		Lang::SetSection('global');

		$int = array('%second%', '%minute%', '%hour%', '%day%', '%week%', '%month%', '%year%');
		$dur = array(60, 60, 24, 7, 4.35, 12, 12);
		
		if(!is_numeric($date))
			$date = strtotime($date);
		
		$now 	= time();
		$time 	= $date;
		
		if($now > $time)
		{
			$dif = $now - $time;
			$str = '%ago%';
		}
		else if($now == $time)
			return _l('%now%');
		else
		{
			$dif = $time - $now;
			$str = '%within%';
		}
		
		for($j = 0; $dif >= $dur[$j] && $j < count($dur) - 1; $j++)
			$dif /= $dur[$j];
			
		$dif = round($dif);
		
		if($dif != 1)
		{
			$int[5] 	.= "e";
			$int[$j] 	.= "s";
		}
		
		return ($num) ? _l("$dif") . ' ' .  strtolower(_l("$int[$j]")) : _l("$str $dif") . " " . strtolower(_l("$int[$j]"));
	}

	// Convertir el mes numerico de una fecha a mes en letras.
	// - $date: Cadena de fecha con separación -, / ? de
	static function MonthString($date)
	{
		if(is_numeric($date))
			return self::GetMonth($date);

		if(Contains($date, '-'))
			$split = explode('-', $date);

		if(Contains($date, '/'))
			$split = explode('/', $date);

		if(Contains($date, _l('%the%', 'global')))
			$split = explode(_l(' %the% ', 'global'), $date);
		
		$month = self::GetMonth($t[1]);		
		return "$t[0]-$month-$t[2]";
	}

	// Obtener una lista (array) de los meses traducidos al idioma del visitante.
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

		foreach($calendar as $key => $value)
			$result[$key] = _l($value);

		return $result;
	}

	// Convertir valor numérico a un mes del año.
	// - $num (Int): Valor numérico.
	// - $c (Bool): ¿Retornar todo el mes?
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
		
		foreach($calendar as $key => $month)
		{
			if(preg_match("/$key/", $num))
			{
				$month = strtolower(_l($month));
				return ($c) ? $month : substr($month, 0, 3);
			}
		}
		
		return _l('%unknow%');
	}
	
	// Convertir mes de un a?o a su valor num?rico.
	// - $name: Mes de a?o.
	static function GetMonthNum($name)
	{
		Lang::SetSection('global');

		$calendar = array(
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
		
		foreach($calendar as $key => $month)
		{
			$month = strtolower($month);

			if(preg_match("/$month/i", $name))
				return _l($key);
				
			$month = substr($month, 0, 3);
			
			if(preg_match("/$month/i", $name))
				return _l($key);
		}

		return _l('%unknow%');
	}
}
?>