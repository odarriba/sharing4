<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/admin.php
//
// Controlador del panel de administración de 
// los enlaces con redes externas a Sharing4. 
// Se pueden ver crear o eliminar dichos 
// enlaces.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

Watchdog::comprobar_usuario();

$usuario = new Usuario();
$auths = $usuario->auths();

Template::imprimir_cabecera("Servicios enlazados", "auth/admin.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();
?>
		<div class="page-header">
			<h1>Servicios enlazados <small>para compartir donde quieras</small></h1>
		</div>
		<div id='admin-superior' class='admin-superior'>
			<div class='pull-left'>
				<a class='btn btn-success' href='/auth/registrar.php'><i class='icon-plus'></i>&nbsp;Crear un enlace</a>
			</div>
		</div>

		<table class='table table-bordered'>
			<thead>
				<tr>
					<th class='span2'>
						<div align='center'>Servicio</div>
					</th>
					<th class='span4'>Cuenta</th>
					<th class='span3'>Fecha registro</th>
					<th class='span1'></th>
				</tr>
			</thead>
			<tbody>
				<? if (count($auths)==0) { ?>
				<tr>
					<td colspan='4'>
						<div class='admin-sin-elementos' align='center'>
							<b>¡Vaya!</b> parece que aún no hay servicios enlazados. <a href='/auth/registrar.php'>Enlazar uno</a>.
						</div>
					</td>
				</tr>
				<? } 
				else { 
					foreach($auths as $elemento) { ?>
				<tr>
					<td>
						<div align='center'>
							<? if ($elemento->servicio == Auth::SERVICIO_TWITTER) { ?>
							<a class='servicio_tw'><b><i class='icon-twitter'></i>&nbsp;Twitter</b></a>
							<? } ?>
							<? if ($elemento->servicio == Auth::SERVICIO_FACEBOOK) { ?>
							<a class='servicio_fb'><b><i class='icon-facebook-sign'></i>&nbsp;Facebook</b></a>
							<? } ?>
						</div>
					</td>
					<td>
						<? if ($elemento->servicio == Auth::SERVICIO_TWITTER) { ?>
						<i>@<?= $elemento->nombre ?></i>
						<? } ?>
						<? if ($elemento->servicio == Auth::SERVICIO_FACEBOOK) { ?>
						<i><?= $elemento->nombre ?></i>
						<? } ?>
					</td>
					<td>
						<?=date("d/m/Y \a \l\a\s H:i:s", strtotime($elemento->timestamp));?>
					</td>
					<td>
						<div align='center'>
							<a href='/auth/desenlazar.php?idauth=<?= $elemento->id ?>' class='btn btn-danger btn-mini'>Desenlazar</a>
						</div>
					</td>
				</tr>
				<? } } ?>
			</tbody>
		</table>

<?
Template::imprimir_panel_final();
Template::imprimir_pie();

require_once ("../include/finales.php");
?>