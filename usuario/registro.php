<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/registro.php
//
// Controlador para hacer el registro de usuario
// y enviar el e-mail de confirmación.
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

$estado = 0; //Indicador del estado del proceso para elegir plantilla.

if (isset($_POST['email']) && isset($_POST['nombre']) && isset($_POST['password']) && isset($_POST['repeat_password']))
{
	// Si se enviaron los datos tratar de crear el usuario
	if (Usuario::nuevo($_POST['email'], $_POST['password'], $_POST['repeat_password'], $_POST['nombre']))
	{
		// Usuario creado, estado=1
		$estado=1;
	}
}

Template::imprimir_cabecera("Crear una cuenta", "usuario/registro.css");

if ($estado==1)
{
?>
		<div class='registro-centro registro-completado'>
			<div class='thumbnail registro-contenedor'>
				<p class='lead'>Tu cuenta ha sido creada con éxito.</p>
				<p class='lead'>Recibirás un e-mail en <b><?=$_POST['email']?></b> para completar el registro.</p>
			</div>
		</div>
<?
}
else
{
	$email = "";
	$nombre = "";

	if (isset($_POST['email']))
	{
		$email = $_POST['email'];
	}

	if (isset($_POST['nombre']))
	{
		$nombre = $_POST['nombre'];
	}
	?>
		<div class='registro-centro'>
			<?Template::imprimir_estado()?>
			<p class='lead'>Para crear tu cuenta, rellena el formulario que ves a continuación con los datos que necesitamos para crear tu cuenta en <?=Config::obtener("nombre_app"); ?>.</p>
			<p class='lead'>No te preocupes, si algo está mal te avisaremos antes de que pase nada.</p>
			<div class='thumbnail registro-contenedor'>
				<form action='/usuario/registro.php' method='post' class='form-horizontal' autocomplete="off">
					<fieldset>
						<div class="control-group">
							<label class="control-label" for="nombre">Nombre</label>
							<div class="controls">
								<input type="text" id="nombre" name="nombre" class="input-block-level" placeholder="Nombre" value="<?=$nombre?>" maxlength="255" required="required">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label" for="email">E-mail</label>
							<div class="controls">
								<input type="email" id="email" name="email" class="input-block-level" placeholder="E-mail" value="<?=$email?>" maxlength="255" required="required">
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
							</div>
						</div>
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
								<button type="submit" class="btn btn-primary">Crear la cuenta&nbsp;<i class='icon-chevron-right'></i></button>
							</div>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	<?
}
Template::imprimir_pie();
require_once "../include/finales.php";
?>