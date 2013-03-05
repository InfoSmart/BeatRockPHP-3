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
 * @package 	Email
 * Permite el envio de correos electrónicos.
 *
*/

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

class Email extends Base
{
	/**
	 * Información del correo.
	 * @var array
	 */
	public $data 		= array();

	/**
	 * ¿Estamos preparados para enviar el correo?
	 * @return true en caso de que SI.
	 */
	public function Prepared()
	{
		return ( empty($data) ) ? false : true;
	}

	/**
	 * Prepara una instancia para el envio de un correo electrónico.
	 * @param array $data Información y configuración.
	 */
	public function __construct($data)
	{
		if ( !is_array($data) )
			return false;

		if ( $data['method'] !== 'mail' AND $data['method'] !== 'phpmailer' )
			$data['method'] = 'mail';

		if ( empty($data['from']) )
			$data['from'] = 'beatrock@infosmart.mx';

		if ( !is_bool($data['html']) )
			$data['html'] = true;

		if  ( empty($data['from.name']) )
			$data['from.name'] = ( defined('SITE_NAME') ) ? SITE_NAME : 'BeatRock';

		if( empty($data['content']) )
		{
			$data['content'] 	= 'text/html';
			$data['html'] 		= true;
		}

		$this->data = $data;
		return $this;
	}

	/**
	 * Establece un mensaje/contenido al correo.
	 * @param string $message Mensaje
	 */
	public function SetMessage($message)
	{
		if ( !self::Prepared() )
			return self::Error('email.prepared');

		$this->data['message'] = $message;
		return $this;
	}

	/**
	 * Envia el correo electrónico.
	 */
	public function Send();
	{
		if( !self::Prepared() )
			return self::Error('email.prepared');

		$data = $this->data;

		if ( $data['method'] = 'mail' )
		{
			$headers = "Return-Path: <$data[from]>\r\n";
			$headers .= "From: \"" . $data['from.name'] . "\" <$data[from]>\r\n";
			$headers .= "Reply-to: noreply <$data[from]>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: $data[content]; charset=utf-8\r\n";

			$this->message 	= stripslashes(wordwrap($data['message'], 70));
			$result 		= @mail($data['to'], $data['subject'], $data['message'], $headers);
		}
		else
		{
			$mail = new PHPMailer();

			$mail->From 	= $data['from'];
			$mail->FromName = $data['from.name'];
			$mail->Subject 	= $data['subject'];
			$mail->Body 	= $data['message'];

			$mail->AddAddress($data['to']);
			$mail->MsgHTML($data['message']);
			$mail->IsHTML($data['html']);

			if ( !empty($data['host']) AND !empty($data['host.port']) AND !empty($data['host.username']) )
			{
				$mail = IsSMTP();
				$mail->Host 	= $data['host'];
				$mail->Port 	= $data['host.port'];
				$mail->Username = $data['host.username'];
				$mail->Password = $data['host.password'];
				$mail->SMTPAuth = true;

				if( !empty($data['host.secure']) )
					$mail->SMTPSecure = $data['host.secure'];
			}

			$result = $mail->Send();
		}

		return $result;
	}

	/**
	 * Envia un correo electrónico de alerta.
	 * @param string $type [description]
	 */
	static function SendWarn($type = 'error')
	{
		global $config;

		if( empty($config['errors']['email.reports']) OR !Core::Valid($config['errors']['email.reports']) )
			return false;

		$file = 'LAST_SEND_' . strtoupper($type);
		$last = @file_get_contents(BIT . $file);

		if( time() < $last AND !empty($last) )
			return false;

		$message 	= new View(KERNEL_VIEWS_BIT . '/Mail.' . ucfirst($type), true);
		$sitename 	= ( defined('SITE_NAME') ) ? SITE_NAME : PATH;

		$mail = new Email(array(
			'method'	=> 'mail',
			'to' 		=> $config['errors']['email.reports'],
			'subject' 	=> _l('%problems%' . $sitename, 'global'),
			'message'	=> $message
		));
		$result = $mail->Send();

		if ( !$result )
		{
			$mail = new Email(array(
				'method' 		=> 'phpmailer',
				'to' 			=> $config['errors']['email.reports'],
				'subject' 		=> _l('%problems%' . $sitename, 'global'),
				'message'		=> $message,
				'host'			=> 'mail.infosmart.mx',
				'host.port' 	=> 26,
				'host.password' => ']X([=g.C{+Hi',
				'host.username' => 'beatrock@infosmart.mx'
			));
			$result = $mail->Send();
		}

		@file_put_contents(BIT . $file, (time() + (3 * 60)));
		return $result;
	}
}
?>