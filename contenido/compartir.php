<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /contenido/compartir.php
//
// Controlador para compartir contenido en las
// distintas redes sociales enlazadas a la cuenta
// del usuario en cuestión.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

Watchdog::comprobar_usuario();

if (isset($_GET['id']))
{
	$id = intval($_GET['id']); // Conversión string-entero

	if ($id == 0) // Id no numérica
	{
		// No se ha pasado el ID del enlace
		$_SESSION['error']='Enlace no válido.';
		header("Location: /contenido/admin.php");
		DB::desconectar();
		session_write_close();
		die();
	}

	$usuario = new Usuario();
	$auths = $usuario->auths();
	$contenido = new Contenido($id);

	// Comprobar si el enlace se encontró y si el enlace es del usuario que ha iniciado sesión
	if ($contenido->id != $id || $usuario->id != $contenido->usuario) 
	{
		// No se ha pasado el ID del enlace
		$_SESSION['error']='Enlace no válido.';
		header("Location: /contenido/admin.php");
		DB::desconectar();
		session_write_close();
		die();
	}

	if(isset($_POST['texto']))
	{
		$titulo = $_POST['texto'];
		// Se han enviado datos, comprobar que se seleccionasen cuentas
		if (isset($_POST['cuenta']))
		{	
			$cuentas = array(); // Array de las cuentas verificadas

			foreach ($_POST['cuenta'] as $cuent) 
			{
				$objeto = new Auth($cuent);

				// Comprobar que solo se usen enlaces propios
				if ($objeto->usuario == $usuario->id)
				{
					$cuentas[count($cuentas)]=$objeto;
				}
			}

			// Enviar contenido
			foreach ($cuentas as $cuenta) 
			{
				if (!$cuenta->publicar($titulo, $contenido))
				{
					// Si falla algún envío poner una advertencia.
					$_SESSION['alert']="En algunas cuentas no se pudo completar el envío.";
				}
			}

			// Si no hubo alertas poner un mensaje de información
			if (!isset($_SESSION['alert']))
				$_SESSION['info']="Se ha compartido el enlace correctamente.";

			// Volver al inicio
			header("Location: /panel.php");
			DB::desconectar();
			session_write_close();
			die();

		}
		else
		{
			// No se seleccionaron cuentas
			$_SESSION['error']="Se debe seleccionar alguna cuenta.";
		}
	}
	else
	{
		if ($contenido->tipo == Contenido::TIPO_IMAGEN)
			$titulo="[IMG] ";
		else if ($contenido->tipo == Contenido::TIPO_LINK)
			$titulo="[LINK] ";
		else if ($contenido->tipo == Contenido::TIPO_VIDEO)
			$titulo="[VIDEO] ";
		else
			$titulo = "";

		$titulo = $titulo.$contenido->titulo;
	}


}
else
{
	// No se ha pasado el ID del enlace
	$_SESSION['error']='Enlace no especificado.';
	header("Location: /contenido/admin.php");
	DB::desconectar();
	session_write_close();
	die();
}
Template::imprimir_cabecera("Compartir enlace", "panel.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();
?>
		<div class="page-header">
			<h1>Compartir <small>texto y cuentas</small></h1>
		</div>
<? if (count($auths)==0)
{
	?>
		<div class='thumbnail compartir-sin-auths alert-info'>
			<p class='lead'>Para poder compartir un enlace debes tener asociada al menos una cuenta en redes sociales, pero sin embargo parece que no tienes ninguna.<br />Si quieres puedes administrar tus enlaces con redes externas <a href='/auth/admin.php'>aquí</a></p>
		</div>
		<hr class='divider' />
		<div align='center'>
			<a href='/panel.php' class='btn btn-inverse'>Volver al inicio</a>
		</div>
	<?
}
else
{
	?>
		<p class='lead'>
			A continuación puedes elegir el texto que se publicará junto con el enlace, así como las cuentas externas en las que lo quieres compartir.<br />
			<small>Si quieres compartir textos distintos en diferentes redes, puedes compartir con unas ahora y con otras más adelante usando el botón de compartir.</small>
		</p>
		<hr class='divider' />
		<div class='pull-left compartir-lista-cuentas'>
			<legend>Cuentas disponibles</legend>
			<ul class='thumbnail lista-cuentas'>
				<? foreach ($auths as $elemento) 
				{
					if ($elemento->servicio == Auth::SERVICIO_TWITTER)
						$texto = "<span class='servicio-tw'><i class='icon-twitter'></i>&nbsp;@".$elemento->nombre."</span>";
					else if ($elemento->servicio == Auth::SERVICIO_FACEBOOK)
						$texto = "<span class='servicio-fb'><i class='icon-facebook-sign'></i>&nbsp;".$elemento->nombre."</span>";
					?>
					<li class='cuenta-externa'><input type='hidden' name='cuenta[]' value='<?=$elemento->id?>' /><?=$texto?></li>
					<?
				}
				?>
			</ul>
			<div align='center'>
				<a href='/panel.php' class='btn'><i class='icon-chevron-left'></i>&nbsp;Volver al inicio</a>
			</div>
		</div>
		<form action='/contenido/compartir.php?id=<?=$_GET['id']?>' method='post' class='pull-right compartir-lista-seleccionadas'>
			<legend>Texto a publicar</legend>
			<textarea type='text' id='texto' name='texto' placeholder='Texto a publicar' class='input-block-level' rows="3" required='required'><?=$titulo?></textarea>
			<legend>Cuentas seleccionadas</legend>
			<ul class='thumbnail lista-seleccionadas'>
				<li class='no-cuenta'><div align='center' class='lead informacion-lista '>Para seleccionar cuentas arrástralas a esta columna.</span></li>
			</ul>
			<div align='center'>
				<button type='submit' class='btn btn-primary'>Compartir enlace&nbsp;<i class='icon-chevron-right'></i></button>
			</div>
		</form>
	<?
}

Template::imprimir_panel_final();
Template::imprimir_pie();

require_once("../include/finales.php");
?>