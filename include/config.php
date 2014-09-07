<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/config.php
//
// Archivo que contiene las directrices básicas
// de configuración del sitio, que ene principio
// no son recomendables cambiar.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Clase Config
// Contiene las directrices de configuración, protegidas del exterior
// y sólo accesibles mediante el método 'obtener' para evitar ataques 
// de inyección de código que puedan cambiar sus valores.
class Config
{
	private static $config = array(
				// Parámetros generales
				"nombre_app" => "Sharing4",
				"dominio" => "www.sharing4.com",
				"url" => "http://www.sharing4.com",
				"url_corta" => "http://sh4.es/",

				// Información en la cabecera HTML
				"header_description" => "A content Hub. Compartir los contenidos con tus contactos en las redes sociales nunca había sido tan sencillo.",
				"header_author" => "Sharing4 - 2013 Todos los derechos reservados.",

				// Seguimiento
				"analytics_code" => "<script type='text/javascript'>var _gaq = _gaq || []; _gaq.push(['_setAccount', 'UA-36977161-1']); _gaq.push(['_setDomainName', 'sharing4.com']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })(); </script>",

				// Parámetros por defecto del módulo mailer
				"mailer_remite" => "no-responder@sharing4.com",
				"mailer_reply_to" => "no-responder@sharing4.com",

				// Parámetros de configuración de la conexión MySQL
				"db_servidor" => "localhost",
				"db_usuario" => "sharing4",
				"db_password" => "wKfDeFDbe4BcLUfG",
				"db_database" => "sharing4",

				// Parámetros para el watchdog
				"watchdog_guardar_bbdd" => true,
				"watchdog_email_para" => "odarriba@gmail.com",
				"watchdog_email_de" => "errores@sharing4.com",

				// Parámetros de configuración de la API de Twitter
				"twitter_consumer_key" => "YOUR_TWITTER_CONSUMER_KEY",
				"twitter_consumer_secret" => "YOUR_TWITTER_CONSUMER_SECRET",
				"twitter_callback_url" => "/auth/twitter.callback.php",
				"twitter_marca_mensajes" => " #sharing4",

				// Parámetros de configuración de la API de Facebook
				"facebook_consumer_key" => "YOUR_FACEBOOK_CONSUMER_KEY",
				"facebook_consumer_secret" => "YOUR_FACEBOOK_CONSUMER_SECRET",
				"facebook_callback_url" => "/auth/facebook.callback.php",
				"facebook_permisos" => "publish_actions",
				"facebook_marca_mensajes" => "",

				// Configuración del modelo de contenido
				"contenido_longitud_key" => 2048,
				"contenido_fuerte_key" => true,
				"contenido_url_longitud" => 6,
				"contenido_url_caracteres" => "0123456789abcdefghijklmnopqrstuvwxyz-_",

				// Configuración compartir
				"compartir_añadidos" => " #sharing4");


	// obtener($nombre)
	// Función para obtener un valor de configuración identificado por $nombre
	public static function obtener($nombre = null)
	{
		// Comprobar que se pasen los parámetros
		if ($nombre == null){
			return null;
		}

		if (isset(self::$config[$nombre])){
			// Si el parámetro de configuración existe, devolverlo
			return self::$config[$nombre];
		}

		// Si no se encontró, devolver null
		return null;
	}
}
?>