<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/login.php
//
// Controlador para hacer el login de usuario
// usando el conjunto de email/contraseña.
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

if (isset($_POST['email']) && isset($_POST['password']))
{
	$remember=false;

	if (isset($_POST['remember']) && $_POST['remember']==true)
		$remember=true;

	// Comprobación de las credenciales
	if (Usuario::login($_POST['email'], $_POST['password'], $remember))
	{
		header("Location: /panel.php");
		DB::desconectar();
		session_write_close();
		die();
	}
}

Template::imprimir_cabecera("Iniciar sesión", "usuario/login.css");
?>
		<div class='login-centro'>
			<?Template::imprimir_estado()?>
			<p class='lead'>Para inciar sesión, introduce los datos de tu cuenta a continuación y pulsa el botón de iniciar sesión.</p>
			<p class='lead'>Si lo prefieres, también puedes iniciar sesión utilizando una de las redes sociales que ves a continuación.</p>
			<div class='thumbnail login-contenedor'>
				<form action='/usuario/login.php' method='post'>
					<fieldset>
						<input id='email' name='email' type="email" placeholder="E-mail" required='required' class='input-block-level'>
						<input id='password' name='password' type="password" placeholder="Contraseña" required='required' class='input-block-level'>
						<div>
							<div class='pull-right'>
								<p><a href='/usuario/pass_perdida.php'>¿Has perdido tu contraseña?</a></p>
							</div>
							<div class='pull-left'>
								<label class="checkbox">
									<input type="checkbox" id="recordar" value="true"> Recordarme
								</label>
							</div>
						</div>
						<button type="submit" class="btn btn-block btn-large">Iniciar sesión <i class='icon-chevron-right'></i></button>
						<hr class='divider' />
						<div class='row-fluid'>
							<div class='span6'>
								<a href='/auth/facebook.login.php' class='btn btn-primary btn-large btn-block'>Iniciar sesión con <b>FB</b></a>
							</div>
							<div class='span6'>
								<a href='/auth/twitter.login.php' class='btn btn-info btn-large btn-block'>Iniciar sesión con <b>Twitter</b></a>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
<?
Template::imprimir_pie();
require_once "../include/finales.php";
?>