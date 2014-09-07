<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/watchdog.php
//
// Clase para informar de los errores que se 
// detecten en la ejecución normal de la web,
// permitiendo enviar un correo en caso de que
// el error sea de nivel crítico.
//
// Además, incluye rutinas de seguridad.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Class Watchdog
// Clase que genera informes de errores a distintos niveles de importancia
// y que puede lelgar a enviar un e-mail en caso de ser un error de nivel crítico.
class Watchdog
{
	// Diferentes niveles de prioridad
	const err_normal = 0;
	const err_usuario = 1;
	const err_apis = 2;
	const err_prioridad = 3;
	const err_critico = 4;

	// Constructor de la clase
	// Conecta a la base de datos de Watchdog y guarda el estado de error.
	// En caso de no poder conectar o no poder guardar, se envia el error
	// por mail
	function __construct($prioridad = Watchdog::err_normal, $texto = "")
	{
		// Preparar el backtrace
		$trazas = debug_backtrace();
		$trazada = "";

		foreach ($trazas as $traza) {
			$archivo = $traza['file'];
			$linea = $traza['line'];
			$funcion = $traza['function'];
			$argumentos = implode(", ", $traza['args']);

			// Guardar en la trazada
			$trazada .= "<i>".$archivo."</i> (Línea ".$linea.") - <b>".$funcion."(<i>".$argumentos."</i>)</b><br />";
		}

		if (Config::obtener("watchdog_guardar_bbdd"))
		{
			// Intentar conectar
			$conexion = new mysqli(Config::obtener("db_servidor"), Config::obtener("db_usuario"), Config::obtener("db_password"), Config::obtener("db_database"));

			// Si no se pudo conectar enviar e-mail
			if ($conexion->connect_error) 
			{
		    	return $this->enviar_mail($prioridad, $texto.' . Adicionalmente no se pudo conectar con la BB.DD. de Watchdog.');
			}

			$conexion->set_charset("utf8");
			// Si no se pudo cambiar el charset de la conexión cerrar la misma y se envía por e-mail
			if ($conexion->errno > 0) 
			{
				$conexion->close();
		    	return $this->enviar_mail($prioridad, $texto.' . Adicionalmente no se pudo cambiar el charset de la conexión con la BB.DD. de Watchdog.', $trazada);
			}

			// Guardar la información en la base de datos
			$conexion->query("INSERT INTO watchdog (nivel, descripcion, traza, ip) VALUES (".$prioridad.", '".$texto."', '".$trazada."', '".$_SERVER['REMOTE_ADDR']."')");

			// Si no se pudo guardar en la base de datos se cierra la conexión y se envía por e-mail
			if ($conexion->errno > 0) 
			{
				$conexion->close();
		    	return $this->enviar_mail($prioridad, $texto.' . Adicionalmente no se pudo guardar el error en la BB.DD. de Watchdog.', $trazada);
			}

			// Cerrar la conexión
			$conexion->close();

			// Si la prioridad es de alto nivel, enviar correo igualmente
			if ($prioridad == Watchdog::err_prioridad || $prioridad == Watchdog::err_critico)
				return $this->enviar_mail($prioridad, $texto, $trazada);

			return true;
		}
		else
		{
			return $this->enviar_mail($prioridad, $texto, $trazada);
		}
	}

	// enviar_mail($prioridad, $texto)
	// Envía la información sobre el error a la dirección de e-mail designada
	// en caso de no poder ser guardado en la BB.DD.
	function enviar_mail($prioridad = self::err_normal, $texto = "", $traza)
	{
		
		$date = new DateTime();

		switch ($prioridad) {
		    case self::err_normal:
		        $prioridad="ERROR NORMAL";
		        break;
		    case self::err_usuario:
		        $prioridad="ERROR DE USUARIO";
		        break;
		    case self::err_apis:
		        $prioridad="ERROR EN APIS EXTERNAS";
		        break;
		    case self::err_prioridad:
		    	$prioridad="ERROR DE ALTA PRIORIDAD";
		        break;
		    case self::err_critico:
		    	$prioridad="ERROR CRÍTICO";
		        break;
		}

		$titulo = Config::obtener("nombre_app")." - INFORME DE ".$prioridad;

		$cuerpo = "<i>Este e-mail ha sido generado automáticamente por el Watchdog de ".Config::obtener("nombre_app").".</i><br /><br />La información relevante al error que no ha podido ser registrado se muestra a continuación para que se pueda tener detalle del mismo y poder ser solucionado lo antes posible.".
					"<br />&nbsp;<hr size='1px'/>&nbsp;<br />".
					"<b>Nivel de prioridad:</b>&nbsp;".$prioridad.
					"<br /><br /><b>Información sobre el error:</b><br /><i>".$texto."</i>".
					"<br /><br /><b>IP afectada:</b>&nbsp;".$_SERVER['REMOTE_ADDR'].
					"<br /><br /><b>Hora del suceso:</b>&nbsp;".$date->format('d/m/Y H:i:s').
					"<br /><br /><b>Traza de ejecución:</b><br />".$traza.
					"<br />&nbsp;<hr size='1px'/>&nbsp;<br />Un saludo cordial,<br /><i>Watchdog</i>";

		$mailer = new Mailer(Config::obtener("watchdog_email_para"), $titulo, $cuerpo, Config::obtener("watchdog_email_de"), Config::obtener("watchdog_email_de"), true);
		
		return true;
	}

	///////////////////////
	// MÉTODOS ESTÁTICOS //
	///////////////////////

	// Función para comprobar que un usuario este autenticado y, si no, devolverlo
	// a la página de inicio de sesión.
	public static function comprobar_usuario()
	{
		if (Usuario::esta_autenticado() == false)
		{
			header("Location: /usuario/login.php");
			$_SESSION['error']="Primero debes iniciar sesión.";
			DB::desconectar();
			session_write_close();
			die();
		}
	}
}
?>