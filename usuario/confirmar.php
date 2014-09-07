<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/confirmar.php
//
// Controlador para manejar la confirmación de
// cuentas de usuario cuando se registran.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "../include/includes.php";

// Si el usuario está autenticado, llevarlo al panel
if (Usuario::esta_autenticado())
{
	header("Location: /panel.php");
	DB::desconectar();
	die();
}

if (isset($_GET['email']) && isset($_GET['hash']))
{
	// Si se han enviado los parámetros, tratar de confirmar la cuenta
	if (!Usuario::confirmar_cuenta($_GET['email'], $_GET['hash']))
	{
		// Si falla ir al login, donde se mostrará el error
		header("Location: /usuario/login.php");
		DB::desconectar();
		die();
	}
}
else
{
	// Si no se enviaron datos, ir al index
	header("Location: /");
	DB::desconectar();
	die();
}

Template::imprimir_cabecera("Confirmar cuenta", "usuario/registro.css");
?>
	<div class='registro-centro registro-completado'>
		<div class='thumbnail registro-contenedor'>
			<p class='lead'>Tu cuenta ha sido confirmada con éxito.</p>
			<p class='lead'>Ya mismo puedes <a href='/usuario/login.php'>iniciar sesión</a> para empezar a usar tu cuenta.</p>
		</div>
	</div>
<?
Template::imprimir_pie();
require_once "../include/finales.php";
?>