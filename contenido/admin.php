<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /contenido/admin.php
//
// Controlador para administrar los contenidos
// de la plataforma, pudindo editar, crear o
// eliminar cada uno de ellos.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once("../include/includes.php");

Watchdog::comprobar_usuario();

$pagina=1;

if(isset($_GET['pagina']))
	$pagina=$_GET['pagina'];

// Conversión string-entero
$pagina=intval($pagina);
if($pagina<=0)
	$pagina=1;

$usuario = new Usuario(); // Usuario actual

$total_elementos = $usuario->num_contenidos();
$total_paginas = ceil($total_elementos/15);
if ($total_paginas == 0)
	$total_paginas=1;

if ($pagina > $total_paginas)
	$pagina = $total_paginas;

$elementos = $usuario->contenidos($pagina, 15);


Template::imprimir_cabecera("Mi contenido", "panel.css");
Template::imprimir_panel_inicio();
Template::imprimir_estado();
?>
		<div class="page-header">
			<h1>Contenido <small>página <?=$pagina?> de <?=$total_paginas?></small></h1>
		</div>
		<div class='barra-superior'>
			<a class='btn btn-success' href='/panel.php' ><i class='icon-plus'></i>&nbsp;Añadir contenido</a>
		</div>
		<? if (count($elementos)==0) {?>
		<div class='panel-sin-contenidos thumbnail'>
			No hay contenido enlazado aún...
		</div>
		<? } else { 
			foreach ($elementos as $elemento) 
			{
				Template::imprimir_elemento_contenido($elemento);
			}
			?>
			<div class="pagination pagination-centered pagination-large">
				<ul>
					<? if ($pagina==1) { ?>
					<li class='disabled'><a href='javascript:void(0);'>« Anterior</a></li>
					<? } else { ?>
					<li><a href='?pagina=<?=$pagina-1?>'>« Anterior</a></li>
					<? } ?>
					<? if ($pagina==$total_paginas) { ?>
					<li class='disabled'><a href='javascript:void(0);'>Siguiente »</a></li>
					<? } else { ?>
					<li><a href='?pagina=<?=$pagina+1?>'>Siguiente »</a></li>
					<? } ?>
				</ul>
			</div>
			<?
		} ?>
<?
Template::imprimir_panel_final();
Template::imprimir_pie();

require_once("../include/finales.php");
?>