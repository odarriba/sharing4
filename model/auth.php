<?php
require_once (__ROOT__.'/include/libs/twitteroauth/twitteroauth.php');
require_once (__ROOT__.'/include/libs/facebook/facebook.php');

/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /model/auth.php
//
// Clase que controla la creación, validación,
// y login de autenticaciones usando servicios
// externos mediante un proceso OAuth.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Modelo Auth
// Clase que controla la creación, validación y login de autenticaciones usando servicios
// externos mediante un proceso OAuth.
class Auth
{
	// Identificadores de servicio
	const SERVICIO_TWITTER = 1;
	const SERVICIO_FACEBOOK = 2;

	// Identificadores de estado
	const ESTADO_LOGIN = 1;
	const ESTADO_REGISTRO = 2;

	public static $SERVICIOS = array(Auth::SERVICIO_TWITTER => "Twitter", 
									Auth::SERVICIO_FACEBOOK => "Facebook");

	private $auth_id = null;
	private $auth_data = array();

	public function __construct($authid=null)
	{
		if ($authid != null && intval($authid) > 0)
		{
			// Si se pasa una id de usuario, asignarlo a esta instancia.
			$this->auth_id = intval($authid);
		}

		// Si se ha podido instanciar a un usuario, cargar los datos
		// Si no hay usuario autentificado y no se ha pasado el id de otro
		// usuario no se pueden cargar datos.
		if ($this->auth_id != null)
		{
			//Se seleccionan distintos campos dependiendo del tipo de usuario
			$query = "SELECT * FROM auths WHERE id=".$this->auth_id." LIMIT 0, 1";

			$peticion = new DB();
			if ($peticion->ejecutar($query))
			{
				// Sólo se da por bueno cuando hay un único resultado.
				if($peticion->num_resultados() == 1)
				{
					// Aplicar los datos cargados.
					$this->auth_data = $peticion->resultado();
				}
			}
			else
			{
				// Ha habido un error en la ejecución de la consulta SQL, que podría ser debido a inyección SQL, por lo que se notificará en WatchDog.
				$wd = new WatchDog(WatchDog::err_prioridad, "Auth::contructor - No se pudo completar la carga de datos de la autenticacion: ".$query);
			}
		}
	}

	// Función para obtener el valor de una variable de la autenticación cargada
	//
	// Devuelve el valor o NULL si noe stá disponible la variable o no existe.
	public function __get($variable=null)
	{
		if (!isset($this->auth_data[$variable]))
			return null;
		else
			return $this->auth_data[$variable];
	}

	public function publicar($texto, $contenido)
	{
		// Comprobar que pertenece al usuario actual
		$usuario = new Usuario();
		$link = Config::obtener("url_corta").$contenido->url;

		if ($this->usuario == $usuario->id)
		{
			if ($this->servicio == Auth::SERVICIO_TWITTER)
			{
				$twitter = new TwitterOAuth(Config::obtener("twitter_consumer_key"), Config::obtener("twitter_consumer_secret"), $this->token, $this->secret);

				// Construcción del mensaje a publicar
				$link = Config::obtener("twitter_marca_mensajes")." ".$link;
				$long_link = strlen($link);

				$long_max = 140-$long_link;

				if (strlen($texto) > $long_max)
				{
					// Si el texto es demasiado largo, recortar hasta que quepa (añadiendo '...')
					$texto = substr($texto, 0, $long_max-3 )."..."; 
				}

				$twitter->post('statuses/update', array('status' => $texto.$link));

				// Comprobar el estado de la llamada para ver si se realizó correctamente
				switch ($twitter->http_code) {
					case 200:
						// Publicación correcta
						return true;

						break;
					default:
						// Informar de un error en la petición
						$wd = new WatchDog(WatchDog::err_apis, "Auth::publicar_twitter - No se pudo completar la petición de publicacion - Código de estado: ".$twitter->http_code);

						return false;
				}
			}
			else if ($this->servicio == Auth::SERVICIO_FACEBOOK)
			{
				// Objeto de la clase Facebook
				$facebook = new Facebook(array('appId'  => Config::obtener("facebook_consumer_key"), 'secret' => Config::obtener("facebook_consumer_secret"), 'cookie' => true));
				
				// En FB no debería haber problema de espacio, pero hay que configurar el array que se envía dependiendo del tipo de contenido.

				$array_envio = array();
				$array_envio['message']=$texto.Config::obtener("facebook_marca_mensajes");
				$array_envio['access_token']=$this->token;
				$array_envio['link']=$link;

				if ($contenido->tipo==Contenido::TIPO_IMAGEN && $contenido->proteccion == Contenido::PROTECCION_NADA)
					$array_envio['picture']=$link;

				try{
					$facebook->setAccessToken($this->token);
					$facebook->api("/me/feed", 'POST', $array_envio);
				} catch(FacebookApiException $e) {
					$error = $e->getResult();
					// Informar de un error en la petición
					$wd = new WatchDog(WatchDog::err_apis, "Auth::publicar_facebook - No se pudo completar la petición de publicacion - Info: ".$error);

					return false;
				}

				return true;
			}
		}
		else
		{
			$wd=new WatchDog(Watchdog::err_usuario, "Auth::publicar - Se ha intentado enviar un mensaje a una red enlazada de otro usuario.");
			return false;
		}
	}

	public function eliminar()
	{
		// Comprobar que pertenece al usuario actual
		$usuario = new Usuario();

		if ($this->usuario == $usuario->id)
		{
			$query="DELETE FROM auths WHERE id=".$this->auth_id." LIMIT 1";
			$peticion = new DB();

			if ($peticion->ejecutar($query))
			{
				$_SESSION['info']='El enlace ha sido eliminado correctamente.';
				return true;
			}
			else
			{
				// Informar de un error en la petición
				header("Location: /500.html");
				$wd = new WatchDog(WatchDog::err_apis, "Auth::eliminar - No se pudo completar la eliminación del enlace - ".$query);

				DB::desconectar();
				session_write_close();
				die();
			}
		}
		else
		{
			$_SESSION['error']="Ese enlace no pertenece a tu cuenta.";
			return false;
		}
	}

	/////////////////////////
	// FUNCIONES ESTÁTICAS //
	/////////////////////////

	// Función que va a la página de autenticación de cada servicio OAuth
	// Si el usuario ya ha iniciado sesión, redirigir al panel.
	// 
	// No devuelve nada porque redirecciona siempre a no ser que se especifique un servicio no válido
	public static function go_login($servicio=null)
	{
		// Si ya está autenticado, volver atrás
		if (Usuario::esta_autenticado())
		{
			header("Location: /panel.php");
			$_SESSION['alert']="Ya estás autenticado.";
			DB::desconectar();
			session_write_close();
			die();
		}

		// Ir al login con Twitter
		if ($servicio == self::SERVICIO_TWITTER)
		{
			// Objeto de la clase Twitter
			$twitter = new TwitterOAuth(Config::obtener("twitter_consumer_key"), Config::obtener("twitter_consumer_secret"));
 
			// Pedir tokens de autenticación
			$request_token = $twitter->getRequestToken(Config::obtener("url").Config::obtener("twitter_callback_url"));

			$_SESSION['tw_oauth_token'] = $token = $request_token['oauth_token'];
			$_SESSION['tw_oauth_token_secret'] = $request_token['oauth_token_secret'];
 
			// Comprobar el estado de la llamada para ver si se realizó correctamente
			switch ($twitter->http_code) {
				case 200:
					// Ir a la página de autorización de Twitter
					$url = $twitter->getAuthorizeURL($token);
					$_SESSION['tw_estado']=self::ESTADO_LOGIN; // Fijar el estado para el callback

					header('Location: ' . $url);

					break;
				default:
					// Informar de un error en la petición
					$wd = new WatchDog(WatchDog::err_apis, "Auth::go_login_twitter - No se pudo completar la petición de token temporal de login - Código de estado: ".$twitter->http_code);

					header("Location: /500.html");
			}

			DB::desconectar();
			session_write_close();
			die();
		}

		// Ir al login con Facebook
		if ($servicio == self::SERVICIO_FACEBOOK)
		{
			// Objeto de la clase Facebook
			$facebook = new Facebook(array('appId'  => Config::obtener("facebook_consumer_key"), 'secret' => Config::obtener("facebook_consumer_secret"), 'cookie' => true));
 
			try 
			{
				// Obtener la URL de login
				$url = $facebook->getLoginUrl(array('scope' => Config::obtener("facebook_permisos"), 'redirect_uri' => Config::obtener("url").Config::obtener("facebook_callback_url")));

				$_SESSION['fb_estado']=self::ESTADO_LOGIN; // Fijar el estado para el callback
				header('Location: ' . $url);

			} catch(FacebookApiException $e) {
				$error = $e->getResult();
				// Informar de un error en la petición
				$wd = new WatchDog(WatchDog::err_apis, "Auth::go_login_facebook - No se pudo completar la petición de token temporal de login - Info: ".$error);

				header("Location: /500.html");
			}

			DB::desconectar();
			session_write_close();
			die();
		}

		return;
	}

	// Función que procesa el login del usuario a partir de un indicador de servicio, un token y un verifier de OAuth
	//
	// Redirecciona a donde sea necesario o devuelve un booleano indicando el estado.
	public static function do_login($servicio=null, $token=null, $verifier=null)
	{
		if ($servicio==null || $token==null || $verifier==null)
			return false;

		// Si ya está autenticado, volver atrás
		if (Usuario::esta_autenticado())
		{
			header("Location: /panel.php");
			$_SESSION['alert']="Ya estás autenticado.";

			DB::desconectar();
			session_write_close();
			die();
		}

		// Logeo con Facebook
		if ($servicio==Auth::SERVICIO_TWITTER)
		{
			// Comprobar que los tokens recibidos son los que se mandaron
			if ($_SESSION['tw_oauth_token'] != $token) 
			{
				$_SESSION['error'] = 'Los tokens recibidos de Twitter no son válidos.';
				return false;
			}

			// Crear el objeto de twitter
			$twitter = new TwitterOAuth(Config::obtener("twitter_consumer_key"), Config::obtener("twitter_consumer_secret"), $_SESSION['tw_oauth_token'], $_SESSION['tw_oauth_token_secret']);

			$access_token = $twitter->getAccessToken($verifier);

			// Comprobar el estado de la petición
			if ($twitter->http_code == 200) 
			{
				// Cargar los datos
				$token=$access_token['oauth_token'];
				$secret=$access_token['oauth_token_secret'];
				$nombre=$access_token['screen_name'];
				$user_id=$access_token['user_id'];

				// Buscar el user_id y el servicio en la BB.DD. de auths.
				$query = "SELECT id, usuario, token, secret FROM auths WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_TWITTER.") LIMIT 0, 1";
				$peticion = new DB();

				if ($peticion->ejecutar($query))
				{
					if ($peticion->num_resultados() == 1)
					{
						$resultado = $peticion->resultado();

						// Posible actualización de datos.
						if ($resultado['secret'] != $secret || $resultado['nombre'] != $nombre || $resultado['token']!= $token)
						{
							// Si algo no coincide, actualizar los valores
							$query_actu = "UPDATE usuarios SET secret='".$secret."', token='".$token."', nombre='".$nombre."' WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_TWITTER.") LIMIT 1";

							if (!$peticion->ejecutar($query))
							{
								// Este error se logea pero no tiene mucha importancia, ya que no afecta al logeo
								$wd = new WatchDog(WatchDog::err_apis, "Auth::do_login_twitter - No se pudo completar la consulta actualización de la autenticacion - ".$query_actu);
							}
						}

						// Iniciar sesión
						$idusuario = $resultado['usuario'];
						return Usuario::login_por_id($idusuario);
					}
					else
					{
						$_SESSION['error']="No hay ningun usuario asociado a la cuenta de Twitter de <b>@".$nombre."</b>.";
						return false;
					}
				}
				else
				{
					// Fallo en la petición SQL. Puede deberse a un fallo de inyección de código.
					header("Location: /500.html");
					$wd = new WatchDog(WatchDog::err_apis, "Auth::do_login_twitter - No se pudo completar la consulta de credenciales en la BB.DD. - ".$query);

					DB::desconectar();
					session_write_close();

					die();
				}
			} 
			else 
			{
				$_SESSION['error']="Ha ocurrido un error al comprobar los credenciales. ¿Ha autorizado el acceso a su cuenta?";
				return false;
			}
		}

		// Logeo con Facebook
		if ($servicio==Auth::SERVICIO_FACEBOOK)
		{
			// Objeto de la clase Facebook
			$facebook = new Facebook(array('appId'  => Config::obtener("facebook_consumer_key"), 'secret' => Config::obtener("facebook_consumer_secret"), 'cookie' => true));

			$user_id = $facebook->getUser();

			if ($user_id) 
			{
				try {
					// Con facebook la obtención de los valores es muy distinta
					$user_profile = $facebook->api('/me');
					$token=$facebook->getAccessToken();
					$nombre=$user_profile['name'];

					// NOTA: Al parecer FB no aplica un secret en el token de autenticación

				} catch (FacebookApiException $e) {
					$error = $e->getResult();
					// Informar de un error en la petición
					$wd = new WatchDog(WatchDog::err_apis, "Auth::do_login_facebook - No se pudo completar la adquisición de datos del usuario remoto - Info: ".$error);

					header("Location: /500.html");
				}

				// Buscar el user_id y el servicio en la BB.DD. de auths.
				$query = "SELECT id, usuario FROM auths WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_FACEBOOK.") LIMIT 0, 1";
				$peticion = new DB();

				if ($peticion->ejecutar($query))
				{
					if ($peticion->num_resultados() == 1)
					{
						$resultado = $peticion->resultado();

						// Posible actualización de datos.
						if ($resultado['nombre'] != $nombre || $resultado['token']!= $token)
						{
							// Si algo no coincide, actualizar los valores
							$query_actu = "UPDATE usuarios SET token='".$token."', nombre='".$nombre."' WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_FACEBOOK.") LIMIT 1";

							if (!$peticion->ejecutar($query))
							{
								// Este error se logea pero no tiene mucha importancia, ya que no afecta al logeo
								$wd = new WatchDog(WatchDog::err_apis, "Auth::do_login_facebook - No se pudo completar la consulta actualización de la autenticacion - ".$query_actu);
							}
						}

						// Iniciar sesión
						$idusuario = $resultado['usuario'];
						return Usuario::login_por_id($idusuario);
					}
					else
					{
						$_SESSION['error']="No hay ningun usuario asociado a la cuenta de Facebook de <b>".$nombre."</b>.";
						return false;
					}
				}
				else
				{
					// Fallo en la petición SQL. Puede deberse a un fallo de inyección de código.
					header("Location: /500.html");
					$wd = new WatchDog(WatchDog::err_apis, "Auth::do_login_facebook - No se pudo completar la consulta de credenciales en la BB.DD. - ".$query);

					DB::desconectar();
					session_write_close();

					die();
				}
			}
			else
			{
				$_SESSION['error']="No se ha podido acceder a la cuenta de Facebook.";
				return false;
			}
		}
	}

	// Función que inicia el proceso de registro para un servicio en particular
	//
	// Redirecciona a la página de registro del servicio elegido
	public static function go_registro($servicio=null)
	{
		// El usuario debe estar autenticado
		Watchdog::comprobar_usuario();

		// Inicio del enlace con Facebook
		if ($servicio == self::SERVICIO_TWITTER)
		{
			// Objeto de la clase Twitter
			$twitter = new TwitterOAuth(Config::obtener("twitter_consumer_key"), Config::obtener("twitter_consumer_secret"));
 
			// Pedir tokens de autenticación
			$request_token = $twitter->getRequestToken(Config::obtener("url").Config::obtener("twitter_callback_url"));

			// Guarda los tokens de autenticación para el siguiente paso
			$_SESSION['tw_oauth_token'] = $token = $request_token['oauth_token'];
			$_SESSION['tw_oauth_token_secret'] = $request_token['oauth_token_secret'];
 
			// Comprobar el estado de la llamada para ver si se realizó correctamente
			switch ($twitter->http_code) {
				case 200:
					// Ir a la página de autorización de Twitter
					$url = $twitter->getAuthorizeURL($token);
					$_SESSION['tw_estado']=self::ESTADO_REGISTRO; // Fijar el estado para el callback

					header('Location: ' . $url);

					break;
				default:
					// Informar de un error en la petición
					$wd = new WatchDog(WatchDog::err_apis, "Auth::go_registro_twitter - No se pudo completar la petición de token temporal de registro - Código de estado: ".$twitter->http_code);

					header("Location: /500.html");
			}

			DB::desconectar();
			session_write_close();
			die();
		}

		// Inicio del enlace con Facebook
		if ($servicio == self::SERVICIO_FACEBOOK)
		{
			// Objeto de la clase Facebook
			$facebook = new Facebook(array('appId'  => Config::obtener("facebook_consumer_key"), 'secret' => Config::obtener("facebook_consumer_secret"), 'cookie' => true));
 
			try 
			{
				// Obtener la URL de facebook
				$url = $facebook->getLoginUrl(array('scope' => Config::obtener("facebook_permisos"), 'redirect_uri' => Config::obtener("url").Config::obtener("facebook_callback_url")));

				$_SESSION['fb_estado']=self::ESTADO_REGISTRO; // Fijar el estado para el callback
				header('Location: ' . $url);

			} catch(FacebookApiException $e) {
				$error = $e->getResult();
				// Informar de un error en la petición
				$wd = new WatchDog(WatchDog::err_apis, "Auth::go_registro_facebook - No se pudo completar la petición de token temporal de login - Info: ".$error);

				header("Location: /500.html");
			}

			DB::desconectar();
			session_write_close();
			die();
		}

		return;
	}

	// Función que procesa el registro de un servicio OAuth para el usuario actual
	//
	// Devuelve un booleano indicando el estado
	public static function do_registro($servicio=null, $token=null, $verifier=null)
	{
		if ($servicio==null || $token==null || $verifier==null)
			return false;

		// El usuario debe estar autenticado
		Watchdog::comprobar_usuario();

		// Registro con Twitter
		if ($servicio==Auth::SERVICIO_TWITTER)
		{
			// Comprobar que los tokens recibidos son los que se mandaron
			if ($_SESSION['tw_oauth_token'] !== $token) 
			{
				$_SESSION['error'] = 'Los tokens recibidos de Twitter no son válidos.';
				return false;
			}

			// Crear el objeto de twitter
			$twitter = new TwitterOAuth(Config::obtener("twitter_consumer_key"), Config::obtener("twitter_consumer_secret"), $_SESSION['tw_oauth_token'], $_SESSION['tw_oauth_token_secret']);

			$access_token = $twitter->getAccessToken($verifier);

			// Comprobar el estado de la petición
			if ($twitter->http_code == 200) 
			{
				// Obtener los datos recibidos
				$token=$access_token['oauth_token'];
				$secret=$access_token['oauth_token_secret'];
				$nombre=$access_token['screen_name'];
				$user_id=$access_token['user_id'];
				$usuario = new Usuario();

				$query = "SELECT id FROM auths WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_TWITTER.") LIMIT 0, 1";
				$peticion = new DB();

				if ($peticion->ejecutar($query))
				{
					// Comprobar si la cuenta ya estaba en el sistema
					if ($peticion->num_resultados() == 0)
					{
						$query = "INSERT INTO auths (usuario, servicio, user_id, token, secret, nombre) VALUES (".$usuario->id.", ".Auth::SERVICIO_TWITTER.", '".$user_id."', '".$token."', '".$secret."', '".$nombre."')";
						if ($peticion->ejecutar($query))
						{
							$_SESSION['info']="La cuenta de <b>@".$nombre."</b> se ha enlazado correctamente";
							return true;
						}
						else
						{
							// Fallo en la petición SQL. Puede deberse a un fallo de inyección de código.
							header("Location: /500.html");
							$wd = new WatchDog(WatchDog::err_prioridad, "Auth::do_registro_twitter - No se pudo completar la inserción de credenciales en la BB.DD. - ".$query);

							DB::desconectar();
							session_write_close();

							die();
						}
					}
					else
					{
						$resultado=$peticion->resultado();

						// Si pertenece a este usuario y hubo cambios, actualizar los valores
						if ($resultado['usuario']==$usuario->id && ($resultado['secret'] != $secret || $resultado['nombre'] != $nombre || $resultado['token']!= $token))
						{
							$query_actu = "UPDATE usuarios SET secret='".$secret."', token='".$token."', nombre='".$nombre."' WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_TWITTER.") LIMIT 1";

							if (!$peticion->ejecutar($query))
							{
								// Este error se logea pero no tiene mucha importancia, ya que no afecta al logeo
								$wd = new WatchDog(WatchDog::err_apis, "Auth::do_registro_twitter - No se pudo completar la consulta actualización de la autenticacion - ".$query_actu);
							}
						}

						$_SESSION['error']="La cuenta de Twitter de <b>@".$nombre."</b> que se intenta enlazar ya está enlazada en alguna cuenta.";
						return false;
					}
				}
				else
				{
					// Fallo en la petición SQL. Puede deberse a un fallo de inyección de código.
					header("Location: /500.html");
					$wd = new WatchDog(WatchDog::err_prioridad, "Auth::do_login_twitter - No se pudo completar la consulta de credenciales en la BB.DD. - ".$query);

					DB::desconectar();
					session_write_close();

					die();
				}
			} 
			else 
			{
				$_SESSION['error']="Ha ocurrido un error al comprobar los credenciales. ¿Ha autorizado el acceso a su cuenta?";
				return false;
			}
		}

		// Registro con Facebook
		if ($servicio==Auth::SERVICIO_FACEBOOK)
		{
			// Objeto de la clase Facebook
			$facebook = new Facebook(array('appId'  => Config::obtener("facebook_consumer_key"), 'secret' => Config::obtener("facebook_consumer_secret"), 'cookie' => true));

			$user_id = $facebook->getUser(); // Obtener la información

			if ($user_id) 
			{
				try {
					// Con facebook la obtención de los valores es muy distinta
					$user_profile = $facebook->api('/me');
					$token=$facebook->getAccessToken();
					$nombre=$user_profile['name'];

					// NOTA: Al parecer FB no aplica un secret en el token de autenticación

				} catch (FacebookApiException $e) {
					$error = $e->getResult();
					// Informar de un error en la petición
					$wd = new WatchDog(WatchDog::err_apis, "Auth::do_registro_facebook - No se pudo completar la adquisición de datos del usuario remoto - Info: ".$error);

					header("Location: /500.html");
				}

				$usuario = new Usuario(); // Objeto del usuario actual para coger su ID

				$query = "SELECT id FROM auths WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_FACEBOOK.") LIMIT 0, 1";
				$peticion = new DB();

				if ($peticion->ejecutar($query))
				{
					// Comprobar si la cuenta ya estaba en el sistema.
					if ($peticion->num_resultados() == 0)
					{
						$query = "INSERT INTO auths (usuario, servicio, user_id, token, secret, nombre) VALUES (".$usuario->id.", ".Auth::SERVICIO_FACEBOOK.", '".$user_id."', '".$token."', '0', '".$nombre."')";
						if ($peticion->ejecutar($query))
						{
							$_SESSION['info']="La cuenta de <b>".$nombre."</b> se ha enlazado correctamente";
							return true;
						}
						else
						{
							// Fallo en la petición SQL. Puede deberse a un fallo de inyección de código.
							header("Location: /500.html");
							$wd = new WatchDog(WatchDog::err_prioridad, "Auth::do_registro_facebook - No se pudo completar la inserción de credenciales en la BB.DD. - ".$query);

							DB::desconectar();
							session_write_close();

							die();
						}
					}
					else
					{
						$resultado=$peticion->resultado();

						// Si pertenece a este usuario y hubo cambios en token/nombre, actualizar los valores
						if ($resultado['usuario']==$usuario->id && ($resultado['nombre'] != $nombre || $resultado['token']!= $token))
						{
							$query_actu = "UPDATE usuarios SET token='".$token."', nombre='".$nombre."' WHERE (user_id='".$user_id."' AND servicio=".Auth::SERVICIO_FACEBOOK.") LIMIT 1";

							if (!$peticion->ejecutar($query))
							{
								// Este error se logea pero no tiene mucha importancia, ya que no afecta al logeo
								$wd = new WatchDog(WatchDog::err_apis, "Auth::do_registro_facebook - No se pudo completar la consulta actualización de la autenticacion - ".$query_actu);
							}
						}

						$_SESSION['error']="La cuenta de Facebook de <b>".$nombre."</b> ya está enlazada en alguna cuenta.";
						return false;
					}
				}
				else
				{
					// Fallo en la petición SQL. Puede deberse a un fallo de inyección de código.
					header("Location: /500.html");
					$wd = new WatchDog(WatchDog::err_prioridad, "Auth::do_login_facebook - No se pudo completar la consulta de credenciales en la BB.DD. - ".$query);

					DB::desconectar();
					session_write_close();

					die();
				}
			}
			else
			{
				$_SESSION['error']="No se ha podido acceder a la cuenta de Facebook.";
				return false;
			}
		}
	}
}