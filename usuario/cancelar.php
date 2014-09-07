<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/cancelar.php
//
// Controlador para cancelar la cuenta de un
// usuario y eliminar sus datos, cuentas 
// enlazadas y contenido.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "../include/includes.php";

Watchdog::comprobar_usuario();

$usuario = new Usuario(); // Cargar los datos del usuario autneticado.
$estado=0; // Estado de la ejecución

if (isset($_POST['actual_password']))
{
	if ($usuario->eliminar_cuenta($_POST['actual_password']))
		$estado=1;
}

Template::imprimir_cabecera("Editar cuenta", "usuario/editar.css");

if($estado==1)
{
	?>
	<div class='editar-centro editar-completado'>
		<div class='thumbnail editar-centro-completado'>
			<p class='lead'>¡Bueno...! Las despedidas nunca fueron fáciles, pero esperamos poder mejor en lo que no te gustaba de nuestro servicio.</p>
			<p class='lead'>Si cambias de opinión, siempre puedes volver a crearte una cuenta... ¡Sin rencores!</p>
			<p class='lead'>Ahora puedes ir al <a href='/'>inicio</a></p>
		</div>
	</div>
	<?
}
else
{

	Template::imprimir_panel_inicio();
	Template::imprimir_estado();
?>
		<div class="page-header">
			<h1>Tu cuenta <small>borrar cuenta</small></h1>
		</div>
		<div class='lead'>Si de verdad deseas cerrar tu cuenta y anular tus credenciales de acceso, introduce tu contraseña para verificar tu identidad y proceder al borrado de los datos.</div>
		<div class='lead text-error'><small><b>Nota:</b> esta acción es irrevocable y todos tus datos se perderán.</small></div>

		<div class='thumbnail editar-centro'>
			<form action='/usuario/cancelar.php' method='post' class='form-horizontal' autocomplete="off">
				<fieldset>
					<div class="control-group">
						<label class="control-label" for="actual_password">Contrase&ntilde;a actual</label>
						<div class="controls">
							<input type="password" id="actual_password" name="actual_password" class="input-block-level" placeholder="Contraseña actual (requerido)" maxlength="30" required="required">
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn btn-danger"><i class='icon-remove'></i>&nbsp;Borrar mi cuenta</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
<?
	Template::imprimir_panel_final();
}

Template::imprimir_pie();

require_once "../include/finales.php";
?>