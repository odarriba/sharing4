<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/registrar.php
//
// Controlador para registrar y enlazar cuentas
// externas al servicio.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

Watchdog::comprobar_usuario();

if (isset($_POST['servicio']))
{
	Auth::go_registro($_POST['servicio']);
}

Template::imprimir_cabecera("Servicios enlazados", "auth/registrar.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();
?>
		<div class="page-header">
			<h1>Enlazar servicio <small>seleccionar proveedor</small></h1>
		</div>
		<div class='lead'>Para poder enlazar una cuenta de otro servicio con <?=Config::obtener("nombre_app")?>, primero tienes que indicarnos qué servicio es el que quieres enlazar.</div>
		<div class='lead'>Posteriormente irás a la web de dicho servicio para que puedan verificar que de verdad quieres enlazar tu cuenta.</div>
		<div class='lead'><small><b>Nota:</b> si alguna vez has enlazado esta cuenta es probable que vuelvas directamente a <?=Config::obtener("nombre_app")?>.</small></div>

		<div class='thumbnail registrar-centro'>
			<form action='/auth/registrar.php' method='post' class='form-horizontal' autocomplete="off">
				<fieldset>
					<div class="control-group">
						<label class="control-label" for="actual_password">Servicio:</label>
						<div class="controls">
							<select id='servicio' name='servicio'>
								<? foreach (Auth::$SERVICIOS as $key => $valor) { ?>
								<option value='<?=$key?>'><?=$valor?></option>
								<? } ?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn btn-primary">Enlazar cuenta&nbsp;<i class='icon-chevron-right'></i></button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
<?
Template::imprimir_panel_final();
Template::imprimir_pie();

require_once ("../include/finales.php");
?>