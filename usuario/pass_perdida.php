<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/pass_perdida.php
//
// Controlador para manejar la regeneración de
// la contraseña de un usuario mediante un e-mail
// cuendo éste la pierda.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "../include/includes.php";

if (Usuario::esta_autenticado())
{
	header("Location: /panel.php");
	DB::desconectar();
	die();
}

$estado=0;

if(isset($_POST['email']) && isset($_POST['hash'])==false)
{
	// Si se recibe el mail por POST pero no el hash, significa que hay que mandar el correo.
	if (Usuario::enviar_mail_password($_POST['email']))
	{
		$estado=1; // Estado=1 cuando se ha enviado el e-mail de confirmación
	}
}
else
{
	if (isset($_GET['email']) && isset($_GET['hash']))
	{
		// TODO: Aquí se debería de comprobar la validez de email-hash pero como se
		// va a comprobar cuando se intente cambiar la contraseña, no es necesario.

		$email = $_GET['email'];
		$hash = $_GET['hash'];

		$estado=2; // Estado=2 cuendo se entra desde el mail y se muestra el form de contraseña.
	}
	else
	{
		if (isset($_POST['email']) && isset($_POST['hash']) && isset($_POST['password']) && isset($_POST['repeat_password']))
		{
			$email = $_POST['email'];
			$hash = $_POST['hash'];

			if (Usuario::cambiar_password_perdida($email, $hash, $_POST['password'], $_POST['repeat_password']))
			{
				// Si todo ha ido bien pasar al estado 3, de confirmación de cambios
				$estado=3;
			}
			else
			{
				// Si algo ha fallado volver al paso 2 para mostrar el error y permitir subsanarlo
				$estado=2;
			}
		}
	}
}

Template::imprimir_cabecera("Recuperar contraseña", "usuario/registro.css");

if ($estado==0) // No ha comenzado el proceso: pedir el mail
{
?>
		<div class='registro-centro'>
			<?Template::imprimir_estado()?>
			<p class='lead'>Antes de recuperar tu contraseña, debemos verificar que eres el dueño de la misma, por lo que enviaremos un e-mail de confirmación a tu dirección registrada.</p>
			<p class='lead'>Para continuar con la recuperación de tu contraseña, introduce la dirección de e-mail con la que te registraste en <?=Config::obtener("nombre_app"); ?>.</p>
			<div class='thumbnail registro-contenedor'>
				<form action='/usuario/pass_perdida.php' method='post' class='form-horizontal'>
					<fieldset>
						<div class="control-group">
							<label class="control-label" for="email">E-mail</label>
							<div class="controls">
								<input type="email" id="email" name="email" class="input-block-level" placeholder="E-mail" value="" maxlength="255" required="required">
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<button type="submit" class="btn btn-primary">Continuar&nbsp;<i class='icon-chevron-right'></i></button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
<?
}

if ($estado==1) // Se acaba de enviar el mail
{
	?>
		<div class='registro-centro registro-completado'>
			<?Template::imprimir_estado()?>
			<div class='thumbnail registro-contenedor'>
				<p class='lead'>Se ha enviado un e-mail a <b><?=$_POST['email']?></b>  con las instrucciones para recuperar la contraseña de la cuenta.</p>
			</div>
		</div>
	<?
}

if ($estado==2) // Se llega del e-mail y se muestra el form de cambiar contraseña
{
	?>
		<div class='registro-centro'>
			<?Template::imprimir_estado()?>
			<p class='lead'>A continuación puedes introducir la nueva contraseña que quieres para usar en tu cuenta de usuario.</p>
			<div class='thumbnail registro-contenedor'>
				<form action='/usuario/pass_perdida.php' method='post' class='form-horizontal'>
					<input type="hidden" id="email" name="email" value="<?=$email?>">
					<input type="hidden" id="hash" name="hash" value="<?=$hash?>">
					<fieldset>
						<div class="control-group">
							<label class="control-label" for="password">Contrase&ntilde;a</label>
							<div class="controls">
								<input type="password" id="password" name="password" class="input-block-level" placeholder="Contraseña" maxlength="30" required="required">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="repeat_password">Repita la contrase&ntilde;a</label>
							<div class="controls">
								<input type="password" id="repeat_password" name="repeat_password" class="input-block-level" placeholder="Repita la contraseña" maxlength="30" required="required">
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<button type="submit" class="btn btn-primary">Cambiar contraseña&nbsp;<i class='icon-chevron-right'></i></button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	<?
}

if ($estado==3) // Se ha cambiado la contraseña correctamente
{
	?>
		<div class='registro-centro registro-completado'>
			<?Template::imprimir_estado()?>
			<div class='thumbnail registro-contenedor'>
				<p class='lead'>La contraseña de tu cuenta ha sido cambiada. Ahora puedes <a href='/usuario/login.php'>iniciar sesión</a> si quieres.</p>
			</div>
		</div>
	<?
}
Template::imprimir_pie();
require_once "../include/finales.php";
?>