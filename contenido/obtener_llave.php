<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /contenido/obtener_llave.php
//
// Controlador para obtener la llave de un
// contenido protegido de esa manera. Ese archivo
// sólo será descargable por el usuario en 
// cuestión.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require("../include/includes.php");

Watchdog::comprobar_usuario();

// Comprobar que se envíe el ID
if(isset($_GET['id']))
{
	$usuario=new Usuario();
	$contenido = new Contenido($_GET['id']);

	// El contenido tiene que ser del usuario activo
	if ($contenido->usuario == $usuario->id)
	{
		if ($contenido->proteccion == Contenido::PROTECCION_FICH)
		{
			header("Content-disposition: attachment; filename=".$contenido->url.".key");
			header("Content-type: application/octet-stream");

			echo($contenido->password);
		}
		else
		{
			header("Location: /panel.php");
			$_SESSION['error']="El enlace proporcionado no es de fichero-llave.";
		}
	}
	else
	{
		header("Location: /panel.php");
		$_SESSION['error']="El enlace seleccionado no te pertenece.";
	}
}
else
{
	header("Location: /panel.php");
	$_SESSION['error']="No se ha enviado el ID de enlace";
}
require("../include/finales.php");
?>