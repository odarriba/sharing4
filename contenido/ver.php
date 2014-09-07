<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /contenido/ver.php
//
// Controlador para ver los datos de un contenido
// dentro de la plataforma.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once("../include/includes.php");

Watchdog::comprobar_usuario();

if (isset($_GET['id']))
{
	$id = intval($_GET['id']);

	if ($id == 0) // Si se ha enviado un id no válido
	{
		header("Location: /contenido/admin.php");
		DB::desconectar();
		session_write_close();
		die();
	}

	$usuario = new Usuario();
	$elemento = new Contenido($id);

	// Sólo se pueden ver los propios
	if ($elemento->usuario != $usuario->id)
	{
		header("Location: /contenido/admin.php");
		DB::desconectar();
		session_write_close();
		die();
	}
}
else
{
	header("Location: /contenido/admin.php");
	DB::desconectar();
	session_write_close();
	die();
}

Template::imprimir_cabecera("Ver contenido", "panel.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();

if ($elemento->proteccion==Contenido::PROTECCION_NADA)
	$proteccion="Ninguna";
else if ($elemento->proteccion==Contenido::PROTECCION_PASS)
	$proteccion = "<span class='text-warning'>Contraseña</span>";
else if ($elemento->proteccion==Contenido::PROTECCION_FICH)
	$proteccion = "<span class='text-success'>Archivo-llave</span>";
?>
		<div class="page-header">
			<h1>Ver contenido <small><?=$elemento->titulo?></small></h1>
		</div>
		<table class="table table-bordered table-striped">
		  <tbody>
		    <tr>
		      <td class="span3" style='text-align: right;'><strong>Título</strong></td>
		      <td><?=$elemento->titulo?></td>
		    </tr>
		    <tr>
		      <td class="span3" style='text-align: right;'><strong>URL</strong></td>
		      <td><a href="<?=$elemento->contenido?>" target='_blank'><?=$elemento->contenido?></a></td>
		    </tr>
		    <tr>
		      <td class="span3" style='text-align: right;'><strong>Enlace público</strong></td>
		      <td><a href="javascript:void(0);"><?=Config::obtener("url_corta").$elemento->url?></a></td>
		    </tr>
		  </tbody>
		</table>
		<hr class='divider' />
		<table class="table table-bordered table-striped">
		  <tbody>
		    <tr>
		      <td class="span3" style='text-align: right;'><strong>Seguridad</strong></td>
		      <td><?=$proteccion?></td>
		    </tr>
		    <? if ($elemento->proteccion==Contenido::PROTECCION_FICH){ ?>
		    <tr>
		      <td class="span3" style='text-align: right; vertical-align: middle;'><strong>Fichero llave</strong></td>
		      <td><a href='/contenido/obtener_llave.php?id=<?=$elemento->id?>' class='btn btn-primary'><i class='icon-download-alt'></i>&nbsp;Descargar llave</a></td>
		    </tr>
		    <? } ?>
		  </tbody>
		</table>
		<hr class='divider' />
		<table class="table table-bordered table-striped">
		  <tbody>
		    </tr>
		    <tr>
		      <td class="span3" style='text-align: right;'><strong>Fecha de creación</strong></td>
		      <td><?=date("d/m/Y \a \l\a\s H:i:s", strtotime($elemento->timestamp));?></td>
		    </tr>
		    <tr>
		      <td class="span3" style='text-align: right; vertical-align: middle;'><strong>Eliminar</strong></td>
		      <td><a class='btn btn-danger' href='/contenido/eliminar.php?id=<?=$elemento->id?>' data-confirm="¿Estás seguro de querer eliminar este contenido?">Eliminar contenido</a></td>
		    </tr>
		  </tbody>
		</table>
		<hr class='divider' />
		<div align='center'>
			<a href='' class='btn btn-success' href='javascript:void(0);'>Estadísticas</a>&nbsp;&nbsp;<a href='/contenido/admin.php' class='btn'>Volver a la lista</a>
		</div>
<?
Template::imprimir_panel_final();
Template::imprimir_pie();

require_once("../include/finales.php");
?>