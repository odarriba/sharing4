<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/twitter.callback.php
//
// Controlador que gestiona las respuestas de la
// API de Twitter en casos de login o enlace de
// cuentas.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

if (isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier']))
{
	if ($_SESSION['tw_estado']==Auth::ESTADO_LOGIN)
	{
	
		if (!Auth::do_login(Auth::SERVICIO_TWITTER, $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier']))
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
	else if ($_SESSION['tw_estado']==Auth::ESTADO_REGISTRO)
	{
		Auth::do_registro(Auth::SERVICIO_TWITTER, $_REQUEST['oauth_token'], $_REQUEST['oauth_verifier']);
		
		header("Location: /auth/admin.php");

		DB::desconectar();
		session_write_close();
		die();
	}
}

if (isset($_REQUEST['denied']))
{
	if ($_SESSION['tw_estado']==Auth::ESTADO_REGISTRO)
	{
		$_SESSION['error']="No se ha autorizado a ".Config::obtener("nombre_app")." para acceder a tu cuenta de Twitter.";
		header("Location: /auth/admin.php");
	}
	else if ($_SESSION['tw_estado']==Auth::ESTADO_LOGIN)
		{
			$_SESSION['error']="No se ha autorizado a ".Config::obtener("nombre_app")." para acceder a tu cuenta de Twitter.";
			header("Location: /usuario/login.php");
		}

	DB::desconectar();
	session_write_close();
	die();
}

header("Location: /");

require_once ("../include/finales.php");
?>