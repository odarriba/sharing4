<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/template.php
//
// Archivo que contiene las partes comunes de 
// interfaz de casi toda la web para poder 
// pintarlas conf acilidad y tenerlas centradas
// para futuros cambios.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Clase Template
// Contiene todos los elementos comunes de la interfaz que se requieren pintar en la mayoría de las ocasiones.
class Template
{
	// Función para imprimir las cabeceras HTML de la salida.
	// No imprime nada de la UI, sólo las cabeceras, precargas CSS y código HTML inicial.
	public static function imprimir_cabecera($titulo = null, $css_particular="principal.css")
	{
		?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<? if ($titulo == null) { ?>
		<title><?=Config::obtener("nombre_app"); ?> - A Content Hub</title>
		<? } else { ?>
		<title><?=Config::obtener("nombre_app"); ?> - <?=$titulo?></title>
		<? } ?>
		<meta name="description" content="<?=Config::obtener("header_description"); ?>">
		<meta name="author" content="<?=Config::obtener("header_author"); ?>">
		<meta property="og:image" content="<?=Config::obtener("url"); ?>/img/logo/facebook.png"/>
		<meta property="og:title" content="Sharing4"/>
		<meta property="og:url" content="<?=Config::obtener("url"); ?>"/>
		<meta property="og:description" content="A Content Hub"/>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />

		<!-- Le HTML5 shim, para soportar HTML5 en IE 6-8 -->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- CSS Bootstrap -->
		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="/css/font-awesome.css" rel="stylesheet">
		<link href="/css/common.css" rel="stylesheet">
		<link href="/css/<?=$css_particular?>" rel="stylesheet">

		<link rel="shortcut icon" href="/favicon.ico">
	</head>
	<body>
		<div class="navbar-wrapper">
			<div class="container">
				<div class="navbar navbar-inverse">
					<div class="navbar-inner">
						<a class="brand" href="/"><img src='/img/logo/blanco.png' title='<?=Config::obtener("nombre_app"); ?>' alt='<?=Config::obtener("nombre_app"); ?>' /></a>
						<div class=''>
							<ul class="nav pull-right">
								<li class="dropdown">
									<? if (Usuario::esta_autenticado() == false) { ?>
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">Iniciar sesión <b class="caret"></b></a>
									<div class="dropdown-menu inicio-sesion">
										<form action='/usuario/login.php' method='post'>
											<fieldset>
												<legend>Inicio de sesión</legend>
												<input name='email' type="email" placeholder="E-mail" class='input-block-level' required='required'>
												<input name='password' type="password" placeholder="Contraseña" class='input-block-level' required='required'>
												<div>
													<div class='pull-right'>
														<p><a href='/usuario/pass_perdida.php'>Recuperar contraseña</a></p>
													</div>
													<div class='pull-left'>
														<label class="checkbox">
															<input type="checkbox" id="recordar" value="true"> Recordarme
														</label>
													</div>
												</div>
	    										<button type="submit" class="btn btn-block">Iniciar sesión <i class='icon-chevron-right'></i></button>
	    										<hr class='divider' />
	    										<a href='/auth/facebook.login.php' class='btn btn-primary btn-block'>Iniciar sesión con <b>Facebook</b></a>
	    										<a href='/auth/twitter.login.php' class='btn btn-info btn-block'>Iniciar sesión con <b>Twitter</b></a>
											</fieldset>
										</form>
									</div>
									<? } else { 
										$usuario = new Usuario(); ?>
									<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class='icon-user'></i>&nbsp;<?=$usuario->get("nombre")?> <b class="caret"></b></a>
									<ul class="dropdown-menu">
										<li><a href='/usuario/editar.php'><i class='icon-pencil'></i>&nbsp;Editar cuenta</a></li>
										<li class='divider'></li>
										<li><a href='/usuario/logout.php'><i class='icon-off'></i>&nbsp;Cerrar sesión</a></li>
									</ul>
									<? } ?>
								</li>
							</ul>
						</div>
					</div><!-- /.navbar-inner -->
				</div><!-- /.navbar -->

			</div> <!-- /.container -->
		</div><!-- /.navbar-wrapper -->
		<div class="container central">
		<?
	}

	// Función para imprimir los mensjaes de estado de la web
	public static function imprimir_estado()
	{
		if (isset($_SESSION['error'])){ ?>
				<div class="alert alert-error">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<?=$_SESSION['error']?>
				</div>
		<? } 

		if (isset($_SESSION['alert'])){ ?>
				<div class="alert">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<?=$_SESSION['alert']?>
				</div>
		<? } 

		if (isset($_SESSION['info'])){ ?>
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<?=$_SESSION['info']?>
				</div>
		<? } 
	}

	// Función para imprimir el cierre de la cabecera inicial HTML.
	// También imprime los JS finales.
	public static function imprimir_pie(){
		?>
			<!-- FOOTER -->
			<footer>
				<span class='pull-left'><img src='/img/copy.gif' alt='(C) 2013 <?=Config::obtener("nombre_app"); ?>' /></span>
				<span class='pull-right'><img src='/img/madein.gif' alt='Made in EPI Gijón' /></span>
			</footer>

		</div><!-- /.container -->

		<!-- Los JS están al final de la página para acelerar la carga. -->
		<script src="/js/jquery.js"></script>
		<script src="/js/jquery-ui.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/application.js"></script>
		<?=Config::obtener("analytics_code"); ?>
	</body>
</html>
	<?	
		// Limpiar las variables de estado al acabar el templating
		unset($_SESSION['error']);
		unset($_SESSION['alert']);
		unset($_SESSION['info']);
	}

	// Función para imprimir el menú lateral y el principio del panel
	public static function imprimir_panel_inicio()
	{
		?>
			<div class='row'>
				<div class='span3'>
					<div class='well panel-menu'>
						<ul class="nav nav-list">
							<li><a href="/panel.php"><i class='icon-home'></i>&nbsp;Inicio</a></li>
							<li class="divider"></li>
							<li class="nav-header">Contenidos</li>
							<li><a href="/contenido/admin.php"><i class='icon-globe'></i>&nbsp;Mi contenido</a></li>
							<li class="divider"></li>
							<li class="nav-header">Cuenta</li>
							<li><a href="/auth/admin.php"><i class='icon-fullscreen'></i>&nbsp;Servicios enlazados</a></li>
							<li><a href="/usuario/editar.php"><i class='icon-wrench'></i>&nbsp;Editar cuenta</a></li>
							<li><a href="/usuario/logout.php"><i class='icon-off'></i>&nbsp;Cerrar sesión</a></li>
						</ul>
					</div>
				</div>
				<div class='span9'>
		<?
	}

	// Función que imprime el final del panel (cerrar las etiquetas abiertas)
	public static function imprimir_panel_final()
	{
		?>
				</div> <!-- .span9 -->
			</div> <!-- .row-->
		<?
	}

	//Función para imprimir un elemento de contenido
	public static function imprimir_elemento_contenido($elemento=null)
	{
		if ($elemento==null)
			return false;

		if ($elemento->tipo==Contenido::TIPO_LINK)
			$icono="icon-globe";
		else if ($elemento->tipo==Contenido::TIPO_IMAGEN)
			$icono="icon-camera-retro";
		else if ($elemento->tipo==Contenido::TIPO_VIDEO)
			$icono="icon-facetime-video";
		?>
		<div class='contenido-elemento thumbnail'>
			<div class='pull-left info'>
				<span class='lead'><i class='<?=$icono?>'></i>&nbsp;<b><?=$elemento->titulo; ?></b></span><br />
				<i>Fuente:</i>&nbsp;&nbsp;<?=$elemento->contenido?><br />
				<span class='label label-info'><?=Config::obtener("url_corta").$elemento->url ?></span>
				<? if ($elemento->proteccion == Contenido::PROTECCION_NADA) {?>
				<span class='label label-inverse'>Público</span>
				<? } ?>
				<? if ($elemento->proteccion == Contenido::PROTECCION_PASS) {?>
				<span class='label label-warning'>Contraseña</span>
				<? } ?>
				<? if ($elemento->proteccion == Contenido::PROTECCION_FICH) {?>
				<span class='label label-success'>Fichero llave</span>&nbsp;<a href='/contenido/obtener_llave.php?id=<?=$elemento->id?>' class='label label-important'><i class='icon-download-alt'></i>&nbsp;Descargar llave</a>
				<? } ?>
			</div>
			<div class='pull-right'>
				<div class='fila-botones'>
					<a href='/contenido/ver.php?id=<?=$elemento->id?>' class='btn btn-primary btn-small'><i class='icon-eye-open'></i>&nbsp;&nbsp;Detalles</a>
				</div>
				<div class='fila-botones'>
					<a href='/contenido/compartir.php?id=<?=$elemento->id?>' class='btn btn-success btn-small'><i class='icon-share'></i>&nbsp;&nbsp;Compartir link</a>
				</div>
				<div class='fila-botones-ultima'>
					<a class='btn btn-danger btn-small' href='/contenido/eliminar.php?id=<?=$elemento->id?>' data-confirm="¿Estás seguro de querer eliminar este contenido?"><i class='icon-remove'></i>&nbsp;&nbsp;Eliminar</a>
				</div>
			</div>
		</div>
		<?
	}
}
?>