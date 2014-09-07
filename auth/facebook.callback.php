<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/facebook.callback.php
//
// Controlador para gestionar las respuestas de 
// la API de Facebook en los procesos de login
// o registro.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

if (isset($_REQUEST['state']) && isset($_REQUEST['code']))
{
	if ($_SESSION['fb_estado']==Auth::ESTADO_LOGIN)
	{
	
		if (!Auth::do_login(Auth::SERVICIO_FACEBOOK, $_REQUEST['state'], $_REQUEST['code']))
		{
			header("Location: /usuario/login.php");
		}
		else
		{
			header("Location: /panel.php");
		}

		DB::desconectar();
		session_write_close();
		die();
	}
	else if ($_SESSION['fb_estado']==Auth::ESTADO_REGISTRO)
	{
		Auth::do_registro(Auth::SERVICIO_FACEBOOK, $_REQUEST['state'], $_REQUEST['code']);
		
		header("Location: /auth/admin.php");

		DB::desconectar();
		session_write_close();
		die();
	}
}

if (isset($_REQUEST['error']))
{
	if ($_SESSION['fb_estado']==Auth::ESTADO_REGISTRO)
	{
		$_SESSION['error']="No se ha autorizado a ".Config::obtener("nombre_app")." para acceder a tu cuenta de Facebook.";
		header("Location: /auth/admin.php");
	}
	else if ($_SESSION['fb_estado']==Auth::ESTADO_LOGIN)
		{
			$_SESSION['error']="No se ha autorizado a ".Config::obtener("nombre_app")." para acceder a tu cuenta de Facebook.";
			header("Location: /usuario/login.php");
		}

	DB::desconectar();
	session_write_close();
	die();
}

header("Location: /");

require_once ("../include/finales.php");
?>