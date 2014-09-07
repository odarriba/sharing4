<?php
require_once "include/includes.php";

if (Usuario::esta_autenticado() == true)
{
	header("Location: /panel.php");
	DB::desconectar();
	die();
}

Template::imprimir_cabecera(null, "index.css");
Template::imprimir_estado();
?>
			<div class='panel-superior'></div>
			<div class="row">
				<div class="span8">
					<div class='titular'>
						<h2 class="titular-cabecera">Comparte lo que quieras,</h2>
						<h3><span class="muted">que nosotros nos ocupamos del resto.</span></h3>
						<p class="lead">Con una simple URL podrás publicar en todas tus redes sociales los contenidos que quieras.</p>
					</div>
				</div>
				<div class='span4'>
					<div class='well registro'>
						<form action='/usuario/registro.php' method='post'>
							<fieldset>
								<legend>&iquest; Primera vez por aqu&iacute; ?</legend>
								<input type="text" name="nombre" placeholder="Nombre" class='input-block-level' required='required'>
								<input type="email" name="email" placeholder="E-mail" class='input-block-level' required='required'>
    							<button type="submit" class="btn btn-success btn-large btn-block">&iexcl;Registrarme!</button>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<hr class='divider' />
			<div class="row menu-principal">
				<div class="span2" style='text-align: center;'>
					<p class='lead'><a href='/quienes_somos.php' title='Quiénes somos'>Quiénes somos</a></p>
				</div>
				<div class="span2" style='text-align: center;'>
					<p class='lead'><a href='/politica_datos.php' title='Política de datos'>Política de datos</a></p>
				</div>
				<div class="span8" style='text-align:right'>
					<a href='https://twitter.com/sharing_for'><img src="/img/twitter.png" class='img-twitter-logo' title='Cuenta de Twitter' alt='Cuenta de Twitter'/></a>
				</div>
			</div>

			<div class="row">
				<div class="span4">
					<h2>Seguridad</h2>
					<p>Además de facilitar la difusión de los contenidos, estos pueden protegerse de la manera que creas más adecuada. <br /><br />Contenidos que puede estar desprotegidos, protegidos por contraseña o por una clave criptográfica, tal como desees.</p>
				</div><!-- /.span4 -->
				<div class="span4">
					<h2>Lo que quieras</h2>
					<p>Tanto si es un enlace de propósito general, como si es una imagen o un vídeo, todo lo que quieras puedes compartirlo (siempre que esté en Internet).<br /><br />Aquí la única limitación la pones tu.</p>
				</div><!-- /.span4 -->
				<div class="span4">
					<h2>Hacia donde quieras</h2>
					<p>Puedes elegir en cuáles de tus perfiles en las distintas redes quieres publicar un cierto contenido.<br /><br />Porque comprendemos que la audiencia no tiene por qué ser la misma en los distintos entornos.</p>
				</div><!-- /.span4 -->
			</div><!-- /.row -->

			

<?
Template::imprimir_pie();

require_once "include/finales.php";
?>