<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/editar.php
//
// Controlador para editar la información
// asociada a la cuenta del usuario.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "../include/includes.php";

Watchdog::comprobar_usuario();

$usuario = new Usuario(); // Cargar los datos del usuario autneticado.

if (isset($_POST['nombre']) && isset($_POST['email']) && isset($_POST['actual_password']))
{
	// Se han enviado datos, procesarlos
	if ((isset($_POST['password']) || isset($_POST['repeat_password'])) && (strlen($_POST['password'])>0 || strlen($_POST['repeat_password'])>0))
	{
		// Cambiar también la contraseña
		$usuario->actualizar_cuenta($_POST['email'], $_POST['nombre'], $_POST['actual_password'], $_POST['password'], $_POST['repeat_password']);
	}
	else
	{
		// Sólo los datos principales
		$usuario->actualizar_cuenta($_POST['email'], $_POST['nombre'], $_POST['actual_password']);
	}
}

Template::imprimir_cabecera("Editar cuenta", "usuario/editar.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();
?>
		<div class="page-header">
			<h1>Tu cuenta <small>aquí podras modificarla</small></h1>
		</div>
		<div class='lead'>Para hacer cambios en tu cuenta, edita los campos que ves a continuación y luego introduce tu contraseña actual para poder saber que eres el dueño de la cuenta.</div>
		<div class='lead'><small>Si lo deseas también puedes cerrar la cuenta usando el botón de la parte inferior.</small></div>

		<div class='thumbnail editar-centro'>
			<form action='/usuario/editar.php' method='post' class='form-horizontal' autocomplete="off">
				<fieldset>
					<div class="control-group">
						<label class="control-label" for="nombre">Nombre</label>
						<div class="controls">
							<input type="text" id="nombre" name="nombre" class="input-block-level" placeholder="Nombre (requerido)" value="<?=$usuario->get('nombre')?>" maxlength="255" required="required">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="email">E-mail</label>
						<div class="controls">
							<input type="email" id="email" name="email" class="input-block-level" placeholder="E-mail (requerido)" value="<?=$usuario->get('email')?>" maxlength="255" required="required">
						</div>
					</div>
					<hr class='divider' />
					<div class="control-group">
						<label class="control-label" for="password">Nueva contrase&ntilde;a</label>
						<div class="controls">
							<input type="password" id="password" name="password" class="input-block-level" placeholder="Nueva contraseña (opcional)" maxlength="30">
							<span class="help-block"><small>Debe tener entre 6 y 30 caracteres.</small></span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="repeat_password">Repita la contrase&ntilde;a</label>
						<div class="controls">
							<input type="password" id="repeat_password" name="repeat_password" class="input-block-level" placeholder="Repita la nueva contraseña" maxlength="30">
						</div>
					</div>
					<hr class='divider' />
					<div class="control-group">
						<label class="control-label" for="actual_password">Contrase&ntilde;a actual</label>
						<div class="controls">
							<input type="password" id="actual_password" name="actual_password" class="input-block-level" placeholder="Contraseña actual (requerido)" maxlength="30" required="required">
						</div>
					</div>
					<hr class='divider' />
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn btn-primary"><i class='icon-ok'></i>&nbsp;Guardar cambios</button>
							<a href='/usuario/cancelar.php' class='btn btn-danger'><i class='icon-remove'></i>&nbsp;Borrar mi cuenta</a>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
<?
Template::imprimir_panel_final();
Template::imprimir_pie();

require_once "../include/finales.php";
?>