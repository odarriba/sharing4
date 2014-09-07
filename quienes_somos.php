<?php
require_once "include/includes.php";

Template::imprimir_cabecera(null, "index.css");
Template::imprimir_estado();
?>
		<div align='center'>
			<img src="/img/logo/300.png" />
		</div>
		<hr class='divider' />
		<p class='lead'><b>Sharing<span style='color: #b13679;'>4</span></b> se crea como un proyecto académico enmarcado en la asignatura <i>Servicios de Comunicaciones Básicos</i> del Grado en Ingeniería de Tecnologías y Servicios de Telecomunicaciones de la <span style='color: #b13679;'>Escuela Politécnica de Ingeniería de Gijón</span>.</p>
		<p class='lead'>Creado como un servicio para acortar URLs largas que faciliten su inclusión en distintas redes sociales, ha avanzado hasta la posibilidad de poder publicar en dichas plataformas directamente desde el servicio, así como la opción de proteger los enlaces con contraseña o archivos-llave de 2048 bits de longitud.</p>
		<p class='lead'>El desarrollo ha sido llevado a cabo por <b>Óscar de Arriba González</b> (<a href='mailto:oscar@sharing4.com'>oscar@sharing4.com</a>)</p>
		<p class='lead'>Ante cualquier duda, puedes ponerte en contacto con nosotros en <a href='mailto:contacto@sharing4.com'>contacto@sharing4.com</a></p>
		<hr class='divider' />
		<div align='center'>
			<a href='/' class='btn btn-success btn-large'>Volver al inicio</a>
		</div>

<?
Template::imprimir_pie();

require_once("include/finales.php");
?>