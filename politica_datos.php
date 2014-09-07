<?php
require_once "include/includes.php";

Template::imprimir_cabecera(null, "index.css");
Template::imprimir_estado();
?>
		<div class='row'>
			<div class='span4' align='center'>
				<img src='/img/candado.jpg' title='Protección de datos' width='95%' />
				<small>Fotografía por <a href='http://www.flickr.com/photos/rahego/5614041674/' title='rahego' >rahego</a> bajo licencia <a href='http://creativecommons.org/licenses/by/2.0/' title='Licencia Creative Commons'>Creative Commons</a></small>
			</div>
			<div class='span8'>
				<p class='lead'><small><b>Sharing<span style='color: #b13679;'>4</span></b> sigue unos estrictos procolos de seguridad y protección de datos para asegurar que los datos de los usuarios no sean expuestos a ninguna persona ajena al servicio.</small></p>
				<p class='lead'><small>Algunos datos especialmente sensibles (contraseñas de acceso, datos de sesiones iniciadas, etc) se guardan como una suma de comprobación mediante diferentes mecanismos, asegurando que aunque hubiera un acceso no autorizado a la base de datos no se conseguiría este tipo de información.</small></p>
				<p class='lead'><small>La vinculación de la cuenta con servicios externos se realiza mediante el estándar de autenticación <i>OAuth</i> (<a href='http://oauth.net/' title='Protocolo OAuth'>mas info</a>) que asegura que las credenciales de autenticación en esos servicios no serán transmitidos a esta web, por lo que el enlace con los mismos no pone en peligro la seguridad de las cuentas de dichos servicios.</small></p>
				<p class='lead'><small>Además, este estándar de autenticación asegura que se pueda revocar el acceso a la aplicación desde el servicio remoto.</small></p>
				<p class='lead'><small>Ante cualquier duda, puedes ponerte en contacto con nosotros en <a href='mailto:contacto@sharing4.com'>contacto@sharing4.com</a></small></p>
				<hr class='divider' />
				<div align='center'>
					<a href='/' class='btn btn-success btn-large' title='Volver al inicio'>Volver al inicio</a>
				</div>
			</div>
		</div>
<?
Template::imprimir_pie();

require_once("include/finales.php");
?>