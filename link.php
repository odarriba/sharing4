<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /link.php
//
// Controlador para manejar la redirección de
// los enlaces, así como la protección de los
// mismos.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once("include/includes.php");

if (isset($_GET['url']))
{
	$contenido = Contenido::cargar_por_url($_GET['url']); // Función para cargar el objeto por URL

	if ($contenido == false) // No encontrado
	{
		// Error 404 Not Found
		header("HTTP/1.0 404 Not Found");
		header("Location: /404.html");
		DB::desconectar();
		session_write_close();
		die();
	}

	if ($contenido->proteccion == Contenido::PROTECCION_NADA)
	{
		header("Location: ".$contenido->contenido);
		DB::desconectar();
		session_write_close();
		die();
	}

	if ($contenido->proteccion == Contenido::PROTECCION_PASS && isset($_POST['password']))
	{
		if ($contenido->comprobar_proteccion($_POST['password']))
		{
			header("Location: ".$contenido->contenido);
			DB::desconectar();
			session_write_close();
			die();
		}
		else
		{
			$_SESSION['error']="La contraseña no parece correcta.";
		}
	}

	if ($contenido->proteccion == Contenido::PROTECCION_FICH && isset($_FILES['archivo']))
	{
		// Comprobar que el archivo existe y se ha cargado correctamente
		if ($_FILES['archivo']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['archivo']['tmp_name'])) 
		{ 
			// Comprobar el contenido
  			if ($contenido->comprobar_proteccion(file_get_contents($_FILES['archivo']['tmp_name'])))
			{
				header("Location: ".$contenido->contenido);
				DB::desconectar();
				session_write_close();
				die();
			}
			else
			{
				$_SESSION['error']="El archivo-llave no es correcto.";
			}
		}
		else
		{
			$_SESSION['error']="Ocurrió un error al subir el archivo-llave.";
		}
	}
}
else
{
	// No se envía url -> Ir al inicio
	header("Location: /");
	DB::desconectar();
	session_write_close();
	die();
}

Template::imprimir_cabecera("Ver enlace", "usuario/login.css");

if ($contenido->proteccion == Contenido::PROTECCION_PASS)
{
?>
		<div class='login-centro'>
			<?Template::imprimir_estado()?>
			<p class='lead'>El enlace al que intenta acceder está protegido.</p>
			<p class='lead'>Para acceder, introduzca la contraseña a continuación.</p>
			<div class='thumbnail login-contenedor'>
				<form action='/l/<?=$_GET['url']?>' method='post'>
					<fieldset>
						<input id='password' name='password' type="password" placeholder="Contraseña del enlace" required='required' class='input-block-level'>
						<button type="submit" class="btn btn-block btn-large">Continuar <i class='icon-chevron-right'></i></button>
					</fieldset>
				</form>
			</div>
		</div>
<?
}

if ($contenido->proteccion == Contenido::PROTECCION_FICH)
{
	?>
		<div class='login-centro' style='margin-top: 90px; margin-bottom: 90px;'>
			<?Template::imprimir_estado()?>
			<p class='lead'>El enlace al que intenta acceder está protegido.</p>
			<p class='lead'>Para acceder, seleccione el fichero llave a continuación.</p>
			<div class='thumbnail login-contenedor'>
				<form action='/l/<?=$_GET['url']?>' method='post' enctype="multipart/form-data">
					<fieldset>
						<input id='archivo' name='archivo' type="file" placeholder="Archivo llave" required='required'>
						<button type="submit" class="btn btn-block btn-large">Continuar <i class='icon-chevron-right'></i></button>
					</fieldset>
				</form>
			</div>
		</div>
	<?
}
Template::imprimir_pie();

require_once("include/finales.php");
?>