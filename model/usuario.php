<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /model/usuario.php
//
// Clase que controla la creación, validación,
// y login de usuario, así como el control sobre 
// el estado de autenticación actual del usuario 
// durante toda la ejecución de la generación de 
// la página.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Modelo Usuario
// Clase que controla la creación, validación,y login de usuario, así como el control 
// sobre el estado de autenticación actual del usuario durante toda la ejecución de la 
// generación de la página.
class Usuario
{
	// Variable que indica el estado de autenticación del usuario actual
	private static $usuario_auth = null;
	private static $usuario_auth_id = null;

	// Variable para almacenar la ID del usuario cuando se instancia esta clase
	private $usuario_id = null;
	private $usuario_data = array();

	//////////////////////
	// METODOS PÚBLICOS //
	//////////////////////

	// Constructor de la clase.
	// Si se le pasa el id de un usuario se instancia para tratar a dicho 
	// usuario, si no es así se instancia con el usuario logeado.
	public function __construct($usuarioid=null)
	{
		if ($usuarioid != null && intval($usuarioid) > 0)
		{
			// Si se pasa una id de usuario, asignarlo a esta instancia.
			$this->usuario_id = intval($usuarioid);
		}
		else
		{
			if (Usuario::esta_autenticado() == true)
			{
				// Si hay un usuario logeado se toma como que es dicho usuario
				$this->usuario_id = self::$usuario_auth_id;
			}
		}

		// Si se ha podido instanciar a un usuario, cargar los datos
		// Si no hay usuario autentificado y no se ha pasado el id de otro
		// usuario no se pueden cargar datos.
		if ($this->usuario_id != null)
		{
			//Se seleccionan distintos campos dependiendo del tipo de usuario
			if ($this->usuario_id == self::$usuario_auth_id)
			{
				$query = "SELECT id, email, ip, activado, nombre FROM usuarios WHERE id=".$this->usuario_id." LIMIT 0, 1";
			}
			else
			{
				$query = "SELECT id, email, nombre FROM usuarios WHERE id=".$this->usuario_id." LIMIT 0, 1";
			}

			$peticion = new DB();
			if ($peticion->ejecutar($query))
			{
				// Sólo se da por bueno cuando hay un único resultado.
				if($peticion->num_resultados() == 1)
				{
					// Aplicar los datos cargados.
					$this->usuario_data = $peticion->resultado();
				}
				else
				{
					$wd = new Watchdog(Watchdog::err_normal, "Usuario::construct - No se ha encontrado el contenido - ID: ".$usuarioid);
					return;
				}
			}
			else
			{
				// Ha habido un error en la ejecución de la consulta SQL, que podría ser debido a inyección SQL, por lo que se notificará en WatchDog.
				$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::contruct - No se pudo completar la carga de datos del usuario - Query: ".$query);
			}
		}
	}

	// Función para obtener el valor de una variable del usuario cargado
	//
	// Devuelve el valor o NULL si noe stá disponible la variable o no existe.
	public function get($variable=null)
	{
		if ($variable==null)
			return null;

		// Devolver el valor si existe
		if (!isset($this->usuario_data[$variable]))
			return null;
		else
			return $this->usuario_data[$variable];
	}

	// Función mágica para obtener el valor de una variable del usuario cargado
	//
	// Devuelve el valor o NULL si noe stá disponible la variable o no existe.
	public function __get($variable=null)
	{
		if ($variable==null)
			return null;

		// Devolver el valor si existe
		if (!isset($this->usuario_data[$variable]))
			return null;
		else
			return $this->usuario_data[$variable];
	}

	// Función para cambiar un valor de un usuario en la base de datos siempre
	// que esté accesible para ello.
	//
	// Devuelve un booleano indicando si se cambió correctamente el valor.
	public function set($variable=null, $valor=null)
	{
		// Si no se pasan parámetros o se intenta cambiar el id del usuario, devolver false.
		if ($variable == null || $valor == null || $variable == 'id')
			return false;

		// Limpieza de valores
		$variable = mysql_real_escape_string($variable);
		if (gettype($valor)=="string") // limpiar el valor si es un string
			$valor = mysql_real_escape_string($valor);

		// Si la variable noe stá dentro de lo permitido, devolver false
		if (!isset($this->usuario_data[$variable]))
			return false;

		// Si el usuario no está fijado, devolver false.
		if ($this->usuario_id == null)
			return false;

		// Comprobar que los tipos coincidan
		if (gettype($valor) != gettype($this->usuario_data[$variable]))
			return false;

		$peticion = new DB(); // Objeto para petición a la BB.DD.

		// Comprobar el tipo de dato para diferenciar entre entero y el resto (tema comillas)
		if (gettype($valor)=="string")
			$query = "UPDATE usuarios SET ".$variable."='".$valor."' WHERE id=".$this->usuario_id;
		else
			$query = "UPDATE usuarios SET ".$variable."=".intval($valor)." WHERE id=".$this->usuario_id;

		if ($peticion->ejecutar($query))
		{
			// Si la ejecución fue correcta, cachear el cambio y devolver true
			$this->usuario_data[$variable]=$valor;
			return true;
		}
		else
		{
			// No se pudo completar la petición. Pudo ser por fallo de tipo de dato o por inyección SQL
			$wd = new WatchDog(WatchDog::err_usuario, "Usuario::set - No se pudo completar el cambio de variable: ".$query);
			return false;
		}
	}

	// Función mágica para cambiar un valor de un usuario en la base de datos siempre
	// que esté accesible para ello.
	//
	// Devuelve un booleano indicando si se cambió correctamente el valor.
	public function __set($variable=null, $valor=null)
	{
		// Si no se pasan parámetros o se intenta cambiar el id del usuario, devolver false.
		if ($variable == null || $valor == null || $variable == 'id')
			return false;

		// Limpieza de variables
		$variable = mysql_real_escape_string($variable);
		if (gettype($valor)=="string") // limpiar el valor si es un string
			$valor = mysql_real_escape_string($valor);

		// Si la variable noe stá dentro de lo permitido, devolver false
		if (!isset($this->usuario_data[$variable]))
			return false;

		// Si el usuario no está fijado, devolver false.
		if ($this->usuario_id == null)
			return false;

		// Comprobar que los tipos coincidan
		if (gettype($valor) != gettype($this->usuario_data[$variable]))
			return false;

		$peticion = new DB(); // Objeto para petición a la BB.DD.

		// Comprobar el tipo de dato para diferenciar entre entero y el resto (tema comillas)
		if (gettype($valor)=="string")
			$query = "UPDATE usuarios SET ".$variable."='".$valor."' WHERE id=".$this->usuario_id;
		else
			$query = "UPDATE usuarios SET ".$variable."=".intval($valor)." WHERE id=".$this->usuario_id;

		if ($peticion->ejecutar($query))
		{
			// Si la ejecución fue correcta, cachear el cambio y devolver true
			$this->usuario_data[$variable]=$valor;
			return true;
		}
		else
		{
			// No se pudo completar la petición. Pudo ser por fallo de tipo de dato o por inyección SQL
			$wd = new WatchDog(WatchDog::err_usuario, "Usuario::set - No se pudo completar el cambio de variable: ".$query);
			return false;
		}
	}

	// Este método devuelve un array con las autenticaciones que tiene el usuario instanciado.
	//
	// Si falla devuelve un array vacío.
	public function auths()
	{
		if ($this->id == null) // Comprobar que el usuario instanciado existiese
			return null;

		$resultado=array();

		if ($this->usuario_id == null)
			return $resultado;

		$query="SELECT id FROM auths WHERE usuario=".$this->usuario_id." ORDER BY servicio ASC";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			for ($i=0; $i < $peticion->num_resultados(); $i++)
			{
				$res = $peticion->resultado();
				$resultado[$i] = new Auth($res['id']);
			}

			return $resultado;
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::auths - No se pudieron cargar las autenticaciones - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Este método devuelve un array con los contenidos que tiene el usuario instanciado.
	//
	// Si falla devuelve un array vacío.
	public function contenidos($pagina=1, $item_por_pagina=15)
	{
		if ($this->id == null) // Comprobar que el usuario instanciado existiese
			return null;

		// Limpiar variables
		$pagina = intval($pagina);
		$item_por_pagina = intval($item_por_pagina);

		if ($pagina < 1 || $item_por_pagina < 1)
			return array();

		$offset=$item_por_pagina*($pagina-1); // Calcular el offset

		$query = "SELECT id FROM contenidos WHERE usuario=".$this->id." ORDER BY timestamp DESC LIMIT ".$offset.", ".$item_por_pagina;
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			// Si la ejecución fue correcta, cargar los objetos instanciados de cada resultado
			$resultados = array();

			for($i=0; $i<$peticion->num_resultados(); $i++)
			{
				$res = $peticion->resultado();
				$resultados[$i]=new Contenido($res['id']);
			}

			return $resultados;
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::contenidos - No se pudieron cargar los contenidos - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función que indica el número total de contenidos del usuario instanciado
	//
	// Devuelve el valor de número de contenidos
	public function num_contenidos()
	{
		if ($this->id == null) // Comprobar que el usuario instanciado existiese
			return null;

		// Petición SQL
		$query = "SELECT COUNT(*) FROM contenidos WHERE usuario=".$this->id;
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			$res = $peticion->resultado();
			return $res['COUNT(*)'];
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::num_contenidos - No se pudo cargar el número de contenidos - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función para actualizar los datos de la cuenta autenticada.
	// Si la cuenta instanciada no es la autenticada no se realizarán cambios
	//
	// Devuelve un booleano y une stado informando de cómo acabó el proceso.
	public function actualizar_cuenta($email=null, $nombre=null, $actual_password=null, $password=null, $repeat_password=null)
	{
		if ($this->id == null) // Comprobar que el usuario instanciado existiese
			return null;

		if ($email == null || $nombre == null || $actual_password==null)
			return false; // Parámetros requeridos

		// Limpiar argumentos
		$email = mysql_real_escape_string($email);
		$nombre = mysql_real_escape_string($email);
		$actual_password = mysql_real_escape_string($actual_password);
		$password = mysql_real_escape_string($password);
		$repeat_password = mysql_real_escape_string($repeat_password);

		if (Usuario::esta_autenticado()==false)
		{
			// Para llegar a aqui se dbe estar autenticado, si no es así es un error de alta prioridad
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_critico, "Usuario::actualizar_cuenta - Se ha llegado a la ejecución sin tener credenciales válidas.");
			
			DB::desconectar();
			session_write_close();
			die();
		}

		if ($this->usuario_id != self::$usuario_auth_id)
		{
			// Para llegar a aqui se dbe estar autenticado y sólo con el usuario activo, si no es así es un error de alta prioridad
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - Se ha llegado a la ejecución en una instancia que no es del usuario autenticado.");
			
			return false;
		}

		// Validar el e-mail
		if (Usuario::comprobar_email($email)==false)
		{
			$_SESSION['error']="El e-mail introducido no es válido.";
			return false;
		}

		// Longitud del e-mail
		if (strlen($email)>255)
		{
			$_SESSION['error']="El e-mail debe tener como máximo 255 caracteres.";
			return false;
		}

		// Longitud del nombre
		if (strlen($nombre)>255)
		{
			$_SESSION['error']="El nombre debe tener como máximo 255 caracteres.";
			return false;
		}

		// Comprobaciones en caso de cambio de password
		if ($password != null || $repeat_password != null)
		{
			// Comprobar la longitud
			if (strlen($password) < 6 || strlen($password) > 30)
			{
				$_SESSION['error']="La contraseña debe tener entre 6 y 30 caracteres.";
				return false;
			}

			// Comprobar que las cadenas sean iguales
			if ($password != $repeat_password)
			{
				$_SESSION['error']="Las nuevas contraseñas deben coincidir.";
				return false;
			}
		}

		// Comprobar la no-existencia del e-mail en la BB.DD.
		$query = "SELECT id FROM usuarios WHERE email='".$email."' AND id <> ".$this->usuario_id." LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados() > 0)
			{
				// El e-mail ya está en uso
				$_SESSION['error']='El e-mail introducido ya está en uso. Por favor, elige otro distinto.';
				return false;
			}
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - No se pudo hacer la consulta de disponibilidad de e-mail - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}

		// Comprobar los credenciales para actualizar los datos en la BB.DD.
		$query = "SELECT id FROM usuarios WHERE password='".sha1(md5($actual_password))."' AND id=".$this->usuario_id." LIMIT 0, 1";

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados() == 0)
			{
				// El e-mail ya está en uso
				$_SESSION['error']='La contraseña actual introducida no es correcta.';
				return false;
			}
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - No se pudo hacer la comprobación de credenciales - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}

		$email_antiguo = $this->email;

		if ($nombre!=$this->nombre || $email!=$this->email)
		{
			// Si los datos difieren en algo, actualizar con los nuevos.
			if (!$this->set('nombre', $nombre) || !$this->set('email', $email))
			{
				// Error en el cambio de variable. Como se ha comprobado la sesión aquí no debería de haber fallado. Notificarlo
				// Redirección a error 500
				header("Location: /500.html");

				$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - No se pudieron actualizar los campos nombre y e-mail");
				
				DB::desconectar();
				session_write_close();
				die();
			}
		}

		if ($password != null || $repeat_password != null)
		{
			$query_actu = "UPDATE usuarios SET password='".sha1(md5($password))."' WHERE id=".$this->usuario_id." LIMIT 1";
			if(!$peticion->ejecutar($query_actu))
			{
				// Error en la ejecución: posible problema de inyección en MySQL
				// Redirección a error 500
				header("Location: /500.html");

				$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - No se pudo hacer el cambio de contraseña - ".$query);
				
				DB::desconectar();
				session_write_close();
				die();
			}
		}

		// Enviar e-mail de aviso al antiguo e-mail
		$titulo = "Cambios en tu cuenta";

		$texto = "Nos complace confirmarte que los cambios en tu cuenta han sido realizados con éxito. A partir de ahora tus datos figurarán como:<br />";
		$texto .= "<ul><li><b>Nombre:</b>&nbsp;".$nombre."</li><li><b>E-mail:</b>&nbsp;".$email."</li></ul>";

		if ($password != null || $repeat_password != null)
		{
			$texto .= "Adicionalmente, también ha habido un cambio en la contraseña de la cuenta.<br /><br />";
		}
		$texto .= "Si tienes alguna duda, no dudes en ponerte en contacto con nosotros a través de la ayuda de la web.<br /><br />";
		$texto .= "Un saludo,<br /><i>El equipo de ".Config::obtener("nombre_app")."</i>";

		$mail = new Mailer($email_antiguo, $titulo, $texto, Config::obtener("mailer_remite"), Config::obtener("mailer_reply_to"), false);

		$_SESSION['info']="Los datos han sido guardados con éxito.";
		return true;
	}

	// Función para eliminar la cuenta del usuario autenticado.
	// Esta función solo estará disponible para una instancia del usuario autenticado
	//
	// Devuelve un true o false+estado si falla.
	public function eliminar_cuenta($password)
	{
		if ($password==null)
			return false; // Parámetros requeridos
		
		// Limpiar string
		$password = mysql_real_escape_string($password);

		if (Usuario::esta_autenticado()==false)
		{
			// Para llegar a aqui se dbe estar autenticado, si no es así es un error de alta prioridad
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_critico, "Usuario::eliminar_cuenta - Se ha llegado a la ejecución sin tener credenciales válidas.");
			
			DB::desconectar();
			session_write_close();
			die();
		}

		if ($this->usuario_id != self::$usuario_auth_id)
		{
			// Para llegar a aqui se dbe estar autenticado y sólo con el usuario activo, si no es así es un error de alta prioridad
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::eliminar_cuenta - Se ha llegado a la ejecución en una instancia que no es del usuario autenticado.");
			
			return false;
		}

		// Comprobar los credenciales para actualizar los datos en la BB.DD.
		$query = "SELECT id FROM usuarios WHERE password='".sha1(md5($password))."' AND id=".$this->usuario_id." LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados() == 0)
			{
				// El e-mail ya está en uso
				$_SESSION['error']='La contraseña actual introducida no es correcta.';
				return false;
			}
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - No se pudo hacer la comprobación de credenciales - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}

		// Borrar los datos asociados al usuario (auths y contenidos)
		$auths = $this->auths();
		$contenidos = $this->contenidos(1, $this->num_contenidos());

		foreach ($auths as $auth) {
			$auth->eliminar();
		}

		foreach ($contenidos as $contenido) {
			$contenido->eliminar();
		}

		// Eliminar al usuario
		$query="DELETE FROM usuarios WHERE id=".$this->usuario_id." LIMIT 1";
		if ($peticion->ejecutar($query))
		{
			// Borrado correcto
			self::$usuario_auth=false;
			self::$usuario_auth_id=null;
			return true;
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::actualizar_cuenta - No se pudo eliminar al usuario - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	///////////////////////
	// MÉTODOS ESTÁTICOS //
	///////////////////////

	// Función que comprueba si en la sesión actual un usuario está autenticado
	//
	// Devuelve un booleano.
	public static function esta_autenticado()
	{
		// Si ya se comprobó se devuelve el resultado directamente
		if (self::$usuario_auth != null)
			return self::$usuario_auth;
		else
			return self::comprobar_autenticacion();
	}

	public static function login_por_id($idusuario=null)
	{
		if ($idusuario==null || intval($idusuario)==0)
			return false;

		// Comprobar la existencia de dicho usuario
		$query="SELECT id, nombre FROM usuarios WHERE id=".intval($idusuario)." LIMIT 0, 1";
		$peticion=new DB();

		if ($peticion->ejecutar($query))
		{
			// Sólo se da por bueno cuando hay un único resultado.
			if($peticion->num_resultados() == 1)
			{
				$resultado = $peticion->resultado();
				// Establecer los valores en la clase
				self::$usuario_auth = true;
				self::$usuario_auth_id = $resultado['id'];

				// Generar u registrar el nuevo idhash
				$idhash = self::generar_idhash();
				$idhash_enc = sha1(md5($idhash));
				$remember = Usuario::generar_idhash(); // Limpiar el remember con un nuevo valor

				$query2 = "UPDATE usuarios SET idhash='".$idhash_enc."', remember='".sha1(md5($remember))."' WHERE id=".$resultado['id'];

				if ($peticion->ejecutar($query2))
				{
					$_SESSION['idlogin']=self::$usuario_auth_id;
					$_SESSION['idhash']=$idhash;

					// Mensaje de estado
					$_SESSION['info']="Sesión iniciada correctamente. Bienvenido <b>".$resultado['nombre']."</b>.";
					return true;
				}
				else
				{
					// Error en la ejecución en este punto no es viable la inyección de SQL así
					// que se trata como un error de menor nivel
					header("Location: /500.html");
					$wd = new WatchDog(WatchDog::err_usuario, "Usuario::login_by_id - No se pudo completar la actualización del idhash: - ".$query2);
					
					DB::desconectar();
					session_write_close();
					die();
				}
			}
			else
			{
				// Mensaje de estado informando del error de autenticación
				$_SESSION['error']="No se ha encontrado el usuario especificado.";
				return false;
			}
				
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL

			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::login_by_id - No se pudo completar la consulta de existencia del usuario: ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función de procesamiento del login de un usuario, comprobando que esté activo.
	// Requiere un e-mail y una contraseña válidos. 
	//
	// Devuelve un booleano y un mensaje de estado en la sesión.
	public static function login($email=null, $password=null, $remember=false)
	{
		// Comprobar las variables
		if (gettype($email) != "string" || gettype($password) != "string")
		{
			// Esto no debería pasar y puede deberse a una brecha de seguridad. Registrarlo.
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::login - No se han recibido los valores esperados en la función.");
			return false;
		}
		
		// Limpiar las variables
		$email = mysql_real_escape_string($email);
		$password = mysql_real_escape_string($password);

		// Si el usuario está autenticado, cerrar la sesión para evitar colisiones en la session
		if (self::esta_autenticado())
			self::cerrar_sesion();

		// Codificar la pass
		$password = sha1(md5($password));
		$query = "SELECT * FROM usuarios WHERE (email='".$email."' AND password='".$password."')";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			// Sólo se da por bueno cuando hay un único resultado.
			if($peticion->num_resultados() == 1)
			{
				$resultado = $peticion->resultado();

				// Si no está activada la cuenta, devolver un error.
				if ($resultado['activado']==false)
				{
					// Mensaje de estado informando del error.
					$_SESSION['alert']="Tu cuenta no está confirmada. Comprueba la cuenta de e-mail con la que te registraste para ver el correo de confirmación.";
					return false;
				}

				// Establecer los valores en la clase
				self::$usuario_auth = true;
				self::$usuario_auth_id = $resultado['id'];

				// Generar u registrar el nuevo idhash
				$idhash = self::generar_idhash();
				$idhash_enc = sha1(md5($idhash));
				$remember = Usuario::generar_idhash(); // Limpiar el remember con un nuevo valor

				$query2 = "UPDATE usuarios SET idhash='".$idhash_enc."', remember='".sha1(md5($remember))."' WHERE id=".$resultado['id'];

				if ($peticion->ejecutar($query2))
				{
					// Si todo va bién registrar la session y devolver true.
					$_SESSION['idlogin']=self::$usuario_auth_id;
					$_SESSION['idhash']=$idhash;

					if ($remember==true)
					{
						setcookie("rmmbr", $remember, time()+60*60*24*7, "/");
					}

					// Mensaje de estado
					$_SESSION['info']="Sesión iniciada correctamente. Bienvenido <b>".$resultado['nombre']."</b>.";
					return true;
				}
				else
				{
					// Error en la ejecución en este punto no es viable la inyección de SQL así
					// que se trata como un error de menor nivel
					header("Location: /500.html");
					$wd = new WatchDog(WatchDog::err_usuario, "Usuario::login - No se pudo completar la actualización del idhash: - ".$query2);

					DB::desconectar();
					session_write_close();
					die();
				}
			}
			else
			{
				// Mensaje de estado informando del error de autenticación
				$_SESSION['error']="Los datos introducidos no concuerdan con ningún usuario del servicio.";
				return false;
			}
				
		}
		else
		{
			// Error en la ejecución: posible problema de inyección en MySQL

			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::login - No se pudo completar la consultar de login: - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función que, a partir de un e-mail y un hash del correo de confirmación, confirma una cuenta, haciéndola usable
	// para iniciar sesión y hacer uso del servicio
	//
	// Devuelve un booleano y un estado en caso de fallar.
	public static function confirmar_cuenta($email=null, $hash=null)
	{
		if ($email==null || $hash==null)
			return false; // parámetros obligatorios

		// Limpiar las variables
		$email = mysql_real_escape_string($email);
		$hash = mysql_real_escape_string($hash);

		$query="SELECT id FROM usuarios WHERE (email='".$email."' AND idhash='".$hash."') LIMIT 0, 1";
		$peticion = new DB();

		// Comprobar que estén los datos en la BB.DD.
		if($peticion->ejecutar($query))
		{
			if($peticion->num_resultados() > 0)
			{
				$nuevo_hash = Usuario::generar_idhash(); // generar nuevo hash
				$query_actu = "UPDATE usuarios SET idhash='".$nuevo_hash."', activado=1, ip='".$_SERVER['REMOTE_ADDR']."' WHERE (email='".$email."' AND idhash='".$hash."') LIMIT 1";

				if ($peticion->ejecutar($query_actu)) // Actualizar los datos
				{
					// Preparar el e-mail de confirmación de la activación
					$titulo = "Activación de tu cuenta completada";

					$texto = "Nos complace confirmarte que tu cuenta en <b>".Config::obtener("nombre_app")."</b> ha sido confirmada correctamente y puedes empezar a usarla en cuanto desees.<br /><br />";
					$texto .= "Si tienes alguna duda, queja o sugerencia, no dudes en ponerte en contacto con nosotros a través de la ayuda de la web.<br /><br />";
					$texto .= "Un saludo,<br /><i>El equipo de ".Config::obtener("nombre_app")."</i>";

					$mail = new Mailer($email, $titulo, $texto, Config::obtener("mailer_remite"), Config::obtener("mailer_reply_to"), false);

					return true;
				}
				else
				{
					// Petición no completada: seguramente debido a un fallo en la sentencia SQL.
					// Redirección a error 500
					header("Location: /500.html");

					$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::confirmar_cuenta - No se pudieron actualizar los datos de la cuenta - ".$query_actu);
					
					DB::desconectar();
					session_write_close();
					die();
				}
			}
			else
			{
				// Si no hubo resultados con lo obtenido, probar si ya se ha activado esa cuenta.
				$query="SELECT id FROM usuarios WHERE (email='".$email."' AND activado=1) LIMIT 0, 1";

				if($peticion->ejecutar($query))
				{
					if($peticion->num_resultados() > 0)
					{
						// Si hay resultados, la cuenta ya estaba activada.
						$_SESSION['info']="La cuenta que intenta activar ya está activa.";
						return false;
					}
					else
					{
						// Si no hay resultados, devolver un error y su información.
						$_SESSION['error']="Los datos de activación no coinciden con nada que podamos reconocer en nuestro sistema.";
						return false;
					}
				}
				else
				{
					// Petición no completada: seguramente debido a un fallo en la sentencia SQL.
					// Redirección a error 500
					header("Location: /500.html");

					$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::confirmar_cuenta - No se pudo completar la consultar de datos para confirmar que la cuenta ya estuviese activa - ".$query);
					
					DB::desconectar();
					session_write_close();
					die();
				}
			}
		}
		else
		{
			// Petición no completada: seguramente debido a un fallo en la sentencia SQL.
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::confirmar_cuenta - No se pudo completar la consultar de datos - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función para enviar un e-mail de restablecimiento de contraseña a un usuario
	// Se generará un hash único para el cambio de contraseña que caducará con
	// el cambio de contraseña o el inicio de sesión.ç
	//
	// Devuelve un booleano y el estado en la variable de sesión si falla.
	public static function enviar_mail_password($email=null)
	{
		if ($email == null)
			return false;

		// Limpiar variables
		$email = mysql_real_escape_string($email);

		if (Usuario::comprobar_email($email)==false)
		{
			// E-mail no válido
			$_SESSION['error']="El e-mail introducido no es válido.";
			return false;
		}

		// Comprobar si hay una cuenta con esos datos
		$query = "SELECT id, nombre, activado FROM usuarios WHERE email='".$email."' LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if($peticion->num_resultados() == 1)
			{
				$datos = $peticion->resultado();

				if ($datos['activado']==false)
				{
					// Si no está activado no hacer cambio de contraseña.
					$_SESSION['alert']="Primero debe hacer click sobre el enlace que enviamos en un correo de confirmación a su cuenta de e-mail. Verifique la carpeta de SPAM si no lo encuentra.";
					return false;
				}

				$nuevo_idhash = Usuario::generar_idhash();
				$nuevo_remember = Usuario::generar_idhash(); // Limpiar el remember

				$query_actu = "UPDATE usuarios SET idhash='".$nuevo_idhash."', remember='".sha1(md5($remember))."' WHERE email='".$email."' LIMIT 1";

				if ($peticion->ejecutar($query_actu))
				{
					// Enviar un mail al usuario.
					$titulo = "Recuperación de su contraseña";

					$texto = "<b>Hola ".$datos['nombre']."</b><br /><br />";
					$texto .= "Hemos recibido una solicitud de cambio de contraseña en su cuenta desde la IP ".$_SERVER['REMOTE_ADDR'].". Para realizar el cambio de contraseña sólo tiene que acceder a la siguiente dirección:<br /><br />";
					$texto .= "<a href='".Config::obtener("url")."/usuario/pass_perdida.php?email=".$email."&hash=".$nuevo_idhash."'>".Config::obtener("url")."/usuario/pass_perdida.php?email=".$email."&hash=".$nuevo_idhash."</a><br /><br />";
					$texto .= "Si usted no ha realizado esta petición, ésta se cancelará en el momento en que vuelva a iniciar sesión con su contraseña actual.<br /><br />Un saludo,<br /><i>El equipo de ".Config::obtener("nombre_app")."</i>";

					$mail = new Mailer($email, $titulo, $texto, Config::obtener("mailer_remite"), Config::obtener("mailer_reply_to"), false);

					return true;
				}
				else
				{
					// Petición no completada: seguramente debido a un fallo en la sentencia SQL.
					// Redirección a error 500
					header("Location: /500.html");

					$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::enviar_mail_password - No se pudo completar la actualización del idhash - ".$query);
					
					DB::desconectar();
					session_write_close();
					die();
				}
			}
			else
			{
				// No hay usuarios registrados con ese e-mail
				$_SESSION['error']="No hay ninguna cuenta registrada con ese e-mail.";
				return false;
			}
		}
		else
		{
			// Petición no completada: seguramente debido a un fallo en la sentencia SQL.
			// Redirección a error 500
			header("Location: /500.html");

			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::enviar_mail_password - No se pudo completar la consultar de datos - ".$query);
			
			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función para cambiar el password una vez que se recibió el e-mail con el hash y se han escrito las contraseñas
	//
	// Devuelve un booleano y un mensaje de estado si falla
	public static function cambiar_password_perdida($email=null, $hash=null, $password=null, $repeat_password=null)
	{
		if ($email==null || $hash == null || $password == null || $repeat_password == null)
			return false; // Parámetros obligatorio

		// Limpiar variables
		$email = mysql_real_escape_string($email);
		$hash = mysql_real_escape_string($hash);
		$password = mysql_real_escape_string($password);
		$repeat_password = mysql_real_escape_string($repeat_password);

		// Comprobar longitud de la contraseña
		if (strlen($password) < 6 || strlen($password) > 30)
		{
			$_SESSION['error']="La contraseña debe tener entre 6 y 30 caracteres.";
			return false;
		}

		// Comprobar que ambas contraseñas sean iguales
		if ($password != $repeat_password)
		{
			$_SESSION['error']="Las contraseñas deben coincidir.";
			return false;
		}

		// Comprobar la validez del e-mail
		if (Usuario::comprobar_email($email)==false)
		{
			$_SESSION['error']="El e-mail de la cuenta no es válido.";
			return false;
		}

		// Query de comprobación del conjunto e-mail-hash
		$query = "SELECT id, nombre, activado FROM usuarios WHERE (email='".$email."' AND idhash='".$hash."') LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados() == 1)
			{
				$datos = $peticion->resultado();

				// Comprobación de que la cuenta esté activada
				if ($datos['activado']==false)
				{
					$_SESSION['alert']="No se puede cambiar la contraseña de una cuenta no activada. Compruebe el e-mail que le hemos enviado tras el registro (puede haber ido a la carpeta de SPAM).";
					return false;
				}

				// Actualizar password
				$password_enc = sha1(md5($password));
				$query_actu = "UPDATE usuarios SET password='".$password_enc."' WHERE (email='".$email."' AND idhash='".$hash."') LIMIT 1";

				if ($peticion->ejecutar($query_actu))
				{
					// Todo correcto, enviar el e-mail de aviso de cambio.
					$titulo = "La contraseña de su cuenta ha sido cambiada";

					$texto = "<b>Hola ".$datos['nombre']."</b><br /><br />";
					$texto .= "Este correo es para notificar el cambio de contraseña de su cuenta usando el formulario de recuperación de contraseña de nuestra web.<br /><br />";
					$texto .= "Si usted no ha realizado este cambio, intente volver a cambiar la contraseña usando el mismo formulario y compruebe que nadie más ha podido acceder a su bandeja de entrada de e-mail.<br /><br />Un saludo,<br /><i>El equipo de ".Config::obtener("nombre_app")."</i>";

					$mail = new Mailer($email, $titulo, $texto, Config::obtener("mailer_remite"), Config::obtener("mailer_reply_to"), false);

					return true;
				}
				else
				{
					// Redirección a error 500
					header("Location: /500.html");

					// Error en la petición que puede deberse a un fallo de SQL o inyección de código.
					$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::cambiar_password_perdida - No se pudieron guardar los cambios en el password - ".$query);

					DB::desconectar();
					session_write_close();
					die();
				}
			}
			else
			{
				// No se encontraron datos del usuario
				$_SESSION['error']="No hay ninguna cuenta asociada a este e-mail en el sistema.";
				return false;
			}
		}
		else
		{
			// Redirección a error 500
			header("Location: /500.html");

			// Error en la petición que puede deberse a un fallo de SQL o inyección de código.
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::cambiar_password_perdida - No se pudieron comprobar los datos de usuario - ".$query);

			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función para cerrar la sesión de un usuario
	//
	// Devuelve TRUE si cierra la sesión y FALSE si no habia sesión iniciada.
	public static function cerrar_sesion()
	{
		if (Usuario::esta_autenticado() == false)
			return false;

		// Limpiar el remember
		$remember = Usuario::generar_idhash();
		$query="UPDATE usuarios SET remember='".sha1(md5($remember))."' WHERE id=".self::$usuario_auth_id;
		$peticion = new DB();

		if(!$peticion->ejecutar($query))
		{
			// Error en la petición que puede deberse a un fallo de SQL o inyección de código.
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::cerrar_sesion - No se pudo actualizar el remember - ".$query);
			// No detener el cierre de sesión
		}

		// Limpiar las variables de la clase
		self::$usuario_auth=null;
		self::$usuario_auth_id=null;

		// Destruir la session.
		session_destroy();
		session_start();

		return true;
	}

	// Función para crear un nuevo usuario en el sistema (registro) a partir de una
	// dirección de e-mail, una contraseña y un nombre.
	//
	// Devuelve un booleano y un estado en caso de error.
	public static function nuevo($email=null, $password=null, $repeat_password=null, $nombre=null)
	{
		if ($email == null || $password == null || $nombre == null)
		{
			// Aquí no se debería de llegar sin parámetros debido a que los datos deberían de pasar
			// un filtrado antes de llegar aquí.
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::nuevo - Datos recibidos no válidos.");
			return false;
		}

		// Limpiar variables
		$email = mysql_real_escape_string($email);
		$password = mysql_real_escape_string($password);
		$repeat_password = mysql_real_escape_string($repeat_password);
		$nombre = mysql_real_escape_string($nombre);

		// Comprobar la validez del e-mail
		if (Usuario::comprobar_email($email)==false)
		{
			$_SESSION['error'] = 'La dirección de e-mail es incorrecta.';
			return false;
		}

		// Comprobar la longitud del e-mail
		if (strlen($email)>255)
		{
			$_SESSION['error'] = "La dirección de e-mail debe tener un máximo de 255 caracteres.";
			return false;
		}

		// Comprobar la longitud del nombre
		if (strlen($email)>255)
		{
			$_SESSION['error'] = "El nombre debe tener un máximo de 255 caracteres.";
			return false;
		}

		// Comprobar la longitud del nombre
		if (strlen($password)>30 || strlen($password)<6)
		{
			$_SESSION['error'] = "La contraseña debe tener entre 6 y 30 caracteres.";
			return false;
		}


		// Comprobar que las contraseñas coincidan
		if ($password!=$repeat_password)
		{
			$_SESSION['error'] = "Las contraseñas introducidas no coinciden";
			return false;
		}

		// Comprobar la existencia de la dirección de e-mail en la BB.DD.
		$query="SELECT id FROM usuarios WHERE email='".$email."'";
		$peticion=new DB();

		if($peticion->ejecutar($query))
		{
			if($peticion->num_resultados() > 0)
			{
				echo $peticion->num_resultados();
				$_SESSION['error'] = "La dirección de e-mail introducida ya está registrada.";
				return false;
			}
		}
		else
		{
			// Redirección a error 500
			header("Location: /500.html");

			// Error en la petición que puede deberse a un fallo de SQL o inyección de código.
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::nuevo - No se pudo consultar la existencia del e-mail: - ".$query);

			DB::desconectar();
			session_write_close();
			die();
		}

		// Registrar al usuario y enviar el e-mail de activación
		$hash_activacion = Usuario::generar_idhash();
		$ip = $_SERVER['REMOTE_ADDR'];
		$pass_enc = sha1(md5($password));

		$query="INSERT INTO usuarios (email, password, activado, nombre, ip, idhash) VALUES ('".$email."', '".$pass_enc."', 0, '".$nombre."', '".$ip."', '".$hash_activacion."')";

		if ($peticion->ejecutar($query))
		{
			// Preparar el e-mail de activación para enviar
			$titulo = "Activación de tu cuenta en ".Config::obtener("nombre_app");

			$texto = "<b>Bienvenido, ".$nombre."</b><br /><br />";
			$texto .= "Ya casi tienes tu cuenta en ".Config::obtener("nombre_app")." creada. <br /><br />Para terminar, sólo tienes que acceder al enlace que te ponemos a continuación y tu cuenta ya estará verificada para ser usada sin ningún tipo de restricción.<br /><br />";
			$texto .= "<a href='".Config::obtener("url")."/usuario/confirmar.php?email=".$email."&hash=".$hash_activacion."'>".Config::obtener("url")."/usuario/confirmar.php?email=".$email."&hash=".$hash_activacion."</a><br /><br />";
			$texto .= "Esperamos que disfrutes de tu cuenta.<br /><br />Un saludo,<br /><i>El equipo de ".Config::obtener("nombre_app")."</i>";

			$mail = new Mailer($email, $titulo, $texto, Config::obtener("mailer_remite"), Config::obtener("mailer_reply_to"), false);

			$_SESSION['info']="Se ha enviado un e-mail a <b>".$email."</b> para confirmar la cuenta.";
			return true;
		}
		else
		{
			// Redirección a error 500
			header("Location: /500.html");

			// Error en la petición que puede deberse a un fallo de SQL o inyección de código.
			$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::nuevo - No se pudieron insertar los datos del usuario: - ".$query);

			DB::desconectar();
			session_write_close();
			die();
		}
	}

	//////////////////////
	// MÉTODOS PRIVADOS //
	//////////////////////

	// Función privada para comprobar la sesión del usuario
	//
	// Devuelve un booleano indicando la autenticación y rellena los valores static de la clase
	private static function comprobar_autenticacion()
	{
		if (isset($_SESSION['idlogin']) && isset($_SESSION['idhash']))
		{
			// Si hay datos de session se comprueban en la BB.DD.
			$hash = sha1(md5($_SESSION['idhash']));
			$query = "SELECT id FROM usuarios WHERE (id=".$_SESSION['idlogin']." AND idhash='".$hash."' AND activado=1)";
			$peticion = new DB();

			if ($peticion->ejecutar($query))
			{
				// Si se pudo ejecutar la consulta y hubo resultados, dar el login por bueno.
				if($peticion->num_resultados() > 0)
				{
					self::$usuario_auth = true;
					self::$usuario_auth_id = $_SESSION['idlogin'];
				}
				else
					self::$usuario_auth = false;
			}
			else
			{
				// Error en la ejecución: posible problema de inyección en MySQL
				$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::comprobar_autenticacion - No se pudo completar la consultar de autenticación: - ".$query);
				$_SESSION["error"]="Ha ocurrido un error interno que impide mantener tu sesión activa.";
				return false;
			}
		}
		else
		{
			// Comprobar cookies
			if (isset($_COOKIE['rmmbr']))
			{
				// Limpiar variables
				$valor = mysql_real_escape_string($_COOKIE['rmmbr']);

				// Comprobar si cuadra con alguna cuenta
				$query="SELECT id FROM usuarios WHERE remember='".sha1(md5($valor))."' LIMIT 0, 1";
				$peticion = new DB();

				if ($peticion->ejecutar($query))
				{
					if ($peticion->num_resultados() == 1)
					{
						$datos = $peticion->resultado();

						$new_idhash = Usuario::generar_idhash();

						$query_actu = "UPDATE usuarios SET idhash='".sha1(md5($new_idhash))."', ip='".$_SERVER['REMOTE_ADDR']."' WHERE id=".$datos['id']." LIMIT 1";

						if ($peticion->ejecutar($query_actu))
						{
							$_SESSION['idlogin']=$datos['id'];
							$_SESSION['idhash']=$new_idhash;
							self::$usuario_auth = true;
							self::$usuario_auth_id = $_SESSION['idlogin'];
						}
						else
						{
							// Error en la ejecución: posible problema de inyección en MySQL
							$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::comprobar_autenticacion - No se pudo actualizar el idhash tras autenticación por cookie - ".$query);
							$_SESSION["error"]="Ha ocurrido un error interno que impide mantener tu sesión activa.";
							return false;
						}
					}
					else
					{
						// Los datos de la cookie no cuadran con ningún usuario
						self::$usuario_auth=false;
					}
				}
				else
				{
					// Error en la ejecución: posible problema de inyección en MySQL
					$wd = new WatchDog(WatchDog::err_prioridad, "Usuario::comprobar_autenticacion - No se pudo comprobar la cookie de recuerdo - ".$query);
					$_SESSION["error"]="Ha ocurrido un error interno que impide mantener tu sesión activa.";
					return false;
				}
			}
			else
			{
				// Sin datos de sesion ni cookies no hay usuario logeado
				self::$usuario_auth=false;
			}
		}

		return self::$usuario_auth;
	}

	// Función privada para generar un idhash aleatorio
	//
	// Devuelve el idhash generado
	private static function generar_idhash()
	{
		$length = 40;
		$characters = "0123456789abcdefghijklmnopqrstuvwxyz";
		$string = "";

		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters)-1)];
		}

		return mysql_real_escape_string($string);
	}

	// Función para comprobar la validez de un email
	//
	// Devuelve un booleano indicando la validez del e-mail.
	private static function comprobar_email($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
}
?>