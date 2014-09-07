<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/desenlazar.php
//
// Controlador para eliminar el enlace a una red
// externa de la base de datos.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

Watchdog::comprobar_usuario();

if (isset($_GET['idauth']))
{
	$auth=new Auth($_GET['idauth']);

	$auth->eliminar();
}

header("Location: /auth/admin.php");

require_once ("../include/finales.php");
?>