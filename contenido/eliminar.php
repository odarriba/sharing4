<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /contenido/eliminar.php
//
// Controlador para eliminar contenido de la
// plataforma.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require("../include/includes.php");

Watchdog::comprobar_usuario();

// Comprobar que se envie el ID
if(isset($_GET['id']))
{
	$usuario=new Usuario();
	$contenido = new Contenido($_GET['id']);

	// Comprobar que el contenido sea del usuario
	if ($contenido->usuario == $usuario->id)
	{
		if ($contenido->eliminar())
		{
			$_SESSION['info']="El enlace se ha eliminado correctamente";
			header("Location: /contenido/admin.php");
		}
		else
		{
			$_SESSION['error']="No se pudo eliminar el enlace correctamente";
			header("Location: /contenido/ver.php?id=".$contenido->id);
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