<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /panel.php
//
// Controlador principal de la aplicación.
// Permite hacer las funciones básicas con el
// contenido y actúa como un punto central.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "include/includes.php";

Watchdog::comprobar_usuario();

$usuario = new Usuario();
$contenidos_recientes=$usuario->contenidos(1, 5);

Template::imprimir_cabecera("Tu cuenta", "panel.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();
?>
		<div class='thumbnail panel-nuevo'>
			<div class='ajax-loader'>
				<img src="/img/ajax-loader.gif"><span class='texto'>&nbsp;&nbsp;Generando enlace...</span>
			</div>
			<form action='/contenido/crear.php' method='post' autocomplete='off'>
				<input type='hidden' name='tipo' value='link' />
				<div class='url-contenido row-fluid'>
					<div class='span10'>
						<input type='text' id='url' name='url' placeholder='URL al contenido para compartir' class='input-block-level' required='required' />
					</div>
					<div class='span2'>
						<button type='submit' class='btn btn-primary btn-large btn-block'>Crear ></button>
					</div>
				</div>
				<div class='titulo-contenido row-fluid'>
					<div class='span2' align='right'>
						<div class='label-titulo'>Título: </div>
					</div>
					<div class='span8'>
						<input type='text' id='titulo' name='titulo' placeholder='Título para el enlace' class='input-block-level' required='required' />
					</div>
				</div>
				<div class='seguridad-contenido row-fluid'>
					<div class='span2' align='right'>
						<div class='label-seguridad'>Seguridad: </div>
					</div>
					<div class='span8'>
						<select id='seguridad' name='seguridad' class='span4'>
							<option value='0'>Ninguna</option>
							<option value='1'>Contraseña</option>
							<option value='2'>Archivo-llave</option>
						</select>
						<a href='javascript:void(0);' class='icon-question-sign icono-ayuda' rel='popover' data-placement='right' data-trigger='hover' data-html='true' data-title='<b>Tipos de seguridad</b>' data-content='<ul><li><b>Ninguna</b> - Cualquiera con el enlace accede al contenido.</li><li><b>Contraseña</b> - Se requerirá una contraseña escrita para acceder al contenido.</li><li><b>Archivo-llave</b> - Se generará un archivo clave necesario para poder acceder al contenido enlazado.</li></ul>'></a>
					</div>
				</div>
				<div class='password-contenido row-fluid'>
					<div class='span2' align='right'>
						<div class='label-password'>Contraseña: </div>
					</div>
					<div class='span8'>
						<input type='password' id='password' name='password' placeholder='Contraseña para el enlace' class='span6'/>
					</div>
				</div>
			</form>
		</div>

		<div class="page-header">
			<h1>Contenido reciente <small>los 5 últimos publicados</small></h1>
		</div>
		<? if (count($contenidos_recientes)==0) {?>
		<div class='panel-sin-contenidos thumbnail'>
			No hay contenido enlazado aún...
		</div>
		<? } else { 
			foreach ($contenidos_recientes as $elemento) 
			{
				Template::imprimir_elemento_contenido($elemento);
			}
			?>
			<a href='/contenido/admin.php' class='btn btn-large btn-block'>Ver todo el contenido</a>
			<?
		} ?>
<?
Template::imprimir_panel_final();
Template::imprimir_pie();

require_once "include/finales.php";
?>