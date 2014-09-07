<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /contenido/crear.php
//
// Controlador para crear contenido en la 
// plataforma asociado al usuario actual.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "../include/includes.php";

Watchdog::comprobar_usuario();

if ($_POST['tipo']=="link")
{
	if (isset($_POST['url']) && isset($_POST['titulo']) && isset($_POST['seguridad']))
	{
		if ($_REQUEST['seguridad']=="1" && !isset($_REQUEST['password']))
		{
			$_SESSION['error']="Debe introducirse una contraseña válida.";
			header("Location: /panel.php");
		}

		$contenido = Contenido::nuevo($_POST['titulo'], $_POST['url'], intval($_POST['seguridad']), $_POST['password']);
		if (!$contenido)
		{
			$_SESSION['error']="Por alguna razón desconocida no se pudo completar la creación.";
			header("Location: /panel.php");
		}
		else
		{
			$_SESSION['info']="Enlace creado correctamente.";
			header("Location: /contenido/compartir.php?id=".$contenido->id);
		}
	}
	else
	{
		$_SESSION['error']="Deben introducirse todos los campos.";
		header("Location: /panel.php");
	}
}

require_once "../include/finales.php";
?>