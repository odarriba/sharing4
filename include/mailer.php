<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/mailer.php
//
// Clase para hacer la configuración y el envío
// de e-mails facilitando el trabajo con los 
// mismos en el resto del código.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once(__ROOT__.'/include/libs/class.phpmailer.php');

// Class Mailer
// Clase para hacer la configuración y el envío de e-mails facilitando el trabajo con los mismos en el resto del código.
class Mailer
{
	// Constructor que envía los mails
	function __construct ($para, $titulo, $msg, $desde, $reply_to, $es_wd = false)
	{
		// Crear el objeto PHPMailer
		$mail = new PHPMailer(true);

		try {
			// Configurar direcciones
			$mail->AddAddress($para);
			$mail->SetFrom($desde);
			$mail->AddReplyTo($reply_to);
			// Asunto
			$mail->Subject = $titulo;
			// Contenido
			$mail->MsgHTML(file_get_contents(__ROOT__.'/include/templates/mail_inicio.php').$msg.file_get_contents(__ROOT__.'/include/templates/mail_fin.php'));
			$mail->IsHTML(true);

			// Enviar
			$mail->Send();
			} 
			catch (phpmailerException $e) 
			{
				// Excepción de PHPMailer
				if ($es_wd == true)
				{
					echo $e->errorMessage();
					return false;
				}
				else
				{
					$wd = new Watchdog(Watchdog::err_prioridad, 'Error al enviar e-mail:' . $e->errorMessage());
					return false;
				}
			} catch (Exception $e) 
			{
				// Excepción de cualquier otro tipo
				if ($es_wd == true)
				{
					echo $e->getMessage();
					return false;
				}
				else
				{
					$wd = new Watchdog(Watchdog::err_prioridad, 'Error al enviar e-mail:' . $e->getMessage());
					return false;
				}
			}
			return true;
	}
}

?>