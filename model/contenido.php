<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /model/contenido.php
//
// Clase que controla la creación, edición y 
// gestión de contenido en la plataforma de
// enlace a contenidos.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Modelo Clase
// Clase que controla la creación, edición y gestión de contenido en la plataforma de 
// enlace a contenidos.
class Contenido 
{
	private $data = array();

	const TIPO_LINK = 0;
	const TIPO_TEXTO = 1;
	const TIPO_IMAGEN = 2;
	const TIPO_VIDEO = 3;

	const PROTECCION_NADA = 0;
	const PROTECCION_PASS = 1;
	const PROTECCION_FICH = 2;

	// Listado de protocolos admitidos
	private static $protocolos = array('http', 'https', 'ftp');
	private static $protocolos_web = array('http', 'https');
	private static $protocolos_noweb = array('ftp');

	// Regexp's para reconocer sitios de vídeo
	private static $sitios_video = array("(youtube.com\/watch\?v=)\w+", "(youtu.be\/)\w+", "(vimeo.com\/)[0-9]+", "youzee.com");
	// Extensiones de imágenes para reconocer links directamente
	private static $exts_imagen = array("jpg", "jpeg", "gif", "png", "tiff", "psd", "bmp", "ico");

	////////////////////////////
	// FUNCIONES INSTANCIADAS //
	////////////////////////////

	// Función constructor
	// Carga los datos para poder acceder/modificarlos
	public function __construct($id=null)
	{
		if ($id==null || intval($id)==0)
			return;

		// Carga de datos del contenido especificado
		$query = "SELECT * FROM contenidos WHERE id=".intval($id)." LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados()==1)
			{
				// Asignar al objeto los datos obtenidos
				$this->data = $peticion->resultado();
				return;
			}
			else
			{
				// Objeto vacío -> No encontrado
				return;
			}
		}
		else
		{
			// Fallo en la selección: error de SQL
			header("Location: /500.html");
			$wd = new Watchdog(Watchdog::err_prioridad, "Contenido::construct - Error al seleccionar el contenido especificado - Query: ".$query);

			DB::desconectar();
			session_write_close();
			die();
		}
	}

	// Función mágica para obtener el valor de una propiedad ($valor = $objeto->propiedad)
	public function __get($valor)
	{
		// Devolver el valor si existe
		if (isset($this->data[$valor]))
			return $this->data[$valor];
		else
			return null; // o null si no existe
	}

	// Función mágica para establecer un valor a una propiedad ($objeto->propiedad = valor)
	public function __set($indice, $valor)
	{
		// Campos no modificables
		if ($indice == "id" || $indice == "usuario" || $indice == "url" || $indice == "proteccion" || $indice == "password")
			return null;

		// Limpieza de variables
		$indice = mysql_real_escape_string($indice); 
		if (gettype($valor)=="string") // Limpiar el valor si es un string
			$valor = mysql_real_escape_string($valor);

		// Comprobar que el usuario del contenido es el que lo intenta modificar
		$usuario = new Usuario();
		if ($usuario->id != $this->usuario)
			return null;

		// Comprobar que el indice está dentro de los posibles
		if (!isset($this->data[$indice]))
			return null;

		// Comprobar que los tipos de datos coinciden
		if (gettype($this->data[$indice]) != gettype($valor))
			return null;

		if (gettype($valor)=="string")
			$query_actu = "UPDATE contenidos SET ".$indice."='".$valor."' WHERE id=".$this->id." LIMIT 1";
		else
			$query_actu = "UPDATE contenidos SET ".$indice."=".intval($valor)." WHERE id=".$this->id." LIMIT 1";

		$peticion = new DB();

		if ($peticion->ejecutar($query_actu))
		{
			// Actualizar el valor en el objeto actual.
			$this->data[$indice]=$valor;
			return;
		}
		else
		{
			$wd = new Watchdog(Watchdog::err_prioridad, "Contenido::set - Error al actualizar la base de datos - Query: ".$query_actu);
			return;
		}
	}

	// Función para eliminar un objeto instanciado de la BB.DD.
	//
	// Devuelve un booleano indicando el estado de la operación
	public function eliminar()
	{
		$usuario = new Usuario();

		// El enlace debe pertenecer al usuario actual
		if ($this->usuario != $usuario->id)
		{
			$wd = new Watchdog(Watchdog::err_usuario, "Contenido::eliminar - Se ha tratado de eliminar un contenido que no es del usuario actual - ID: ".$this->id);
			return false;
		}

		// Eliminar el objeto
		$query="DELETE FROM contenidos WHERE id=".$this->id." AND url='".$this->url."' LIMIT 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			// Si se ejecutó correctamente, eliminar los datos del objeto en memoria
			$this->data = array();
			return true;
		}
		else
		{
			// Error en el query -> registrar
			$wd = new Watchdog(Watchdog::err_prioridad, "Contenido::eliminar - Error al eliminar el objeto de la BB.DD. - Query: ".$query);
			return false;
		}
	}

	/////////////////////////
	// FUNCIONES ESTÁTICAS //
	/////////////////////////

	// Función para crear un nuevo contenido en la BB.DD.
	//
	// Devuelve un booleano indicando el resultado
	public static function nuevo($titulo=null, $contenido=null, $proteccion=null, $password=null, $es_texto = false)
	{
		// Requerimientos mínimos de parámetros
		if ($titulo == null || $contenido == null || ($proteccion==Contenido::PROTECCION_PASS && $password == null) || strlen($titulo)==0 || strlen($contenido)==0)
			return false;

		// Limpiar valores
		$titulo = mysql_real_escape_string($titulo);
		$contenido = mysql_real_escape_string($contenido);
		$proteccion = intval($proteccion);
		$password = mysql_real_escape_string($password);

		$url = Contenido::generar_url();
		if ($url == false) // Fallo al generar la URL
			return false;

		if ($es_texto == true)
		{
			$tipo = Contenido::TIPO_TEXTO;
			$mime='0'; // Compatibilidad con el formato de la BB.DD.
		}
		else
		{
			// Si no es texto comprobar el protocolo y si no aplicar el estándar
			$protos = implode("|", Contenido::$protocolos);
			if (!preg_match("/^(".$protos."):\/{2}/", strtolower($contenido)))
			{
				$contenido = "http://".$contenido;
			}

			$mime = Contenido::determinar_mime($contenido);
			$tipo = Contenido::determinar_tipo($contenido, $mime);
		}
			
		if ($proteccion == Contenido::PROTECCION_FICH)
			$password = Contenido::generar_keyfile($titulo, $contenido);
		else if ($proteccion == Contenido::PROTECCION_NADA)
			$password = '0'; // Porque el campo en la BB.DD no puede ser nulo.
		else
			$password = sha1(md5($password));

		$usuario = new Usuario(); // Usuario actual
		

		$query = "INSERT INTO contenidos (usuario, url, titulo, contenido, proteccion, password, tipo, mime) VALUES (".$usuario->id.", '".$url."', '".$titulo."', '".$contenido."', ".$proteccion.", '".$password."', ".$tipo.", '".$mime."')";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			return new Contenido($peticion->insert_id());
		}
		else
		{
			// Error en la petición SQL. Retornar error.
			$wd = new Watchdog(Watchdog::err_prioridad, "Contenido::nuevo - Error al insertar los datos en la BB.DD. - Query: ".$query);
			return false;
		}
	}

	// Función para cargar un contenido a partir de su URL
	//
	// Devuelve el objeto cargado o FALSE si falla.
	public static function cargar_por_url($url=null)
	{
		// Comprobación de parámetro
		if ($url==null)
			return false;

		$url = mysql_real_escape_string($url); // Limpieza string

		// Buscar la URL
		$query = "SELECT * FROM contenidos WHERE url='".$url."' LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados() == 1)
			{
				// URL encontrada
				$res = $peticion->resultado();
				return new Contenido($res['id']);
			}
			else
			{
				// URL no encontrada
				return false;
			}
		}
		else
		{
			// Error en la petición SQL. Retornar error.
			$wd = new Watchdog(Watchdog::err_usuario, "Contenido::cargar_por_url - Error al buscar la url en la BB.DD. - Query: ".$query);
			return false;
		}
	}

	// Función para comprobar si se pasa la protección del contenido o no.
	//
	// Devuelve TRUE si se pasa y FALSE si no se pasa o el método falla.
	public function comprobar_proteccion($valor=null)
	{
		// Comprobar los parámetros
		if ($valor == null)
			return false;

		$valor = mysql_real_escape_string($valor); // Limpieza de string

		// Compprobar datos
		if ($this->proteccion == Contenido::PROTECCION_NADA)
			return true; // Sin protección
		else if ($this->proteccion == Contenido::PROTECCION_FICH)
		{
			// Fichero directo
			if ($valor == $this->password)
				return true;
			else
				return false;
		}
		else if ($this->proteccion == Contenido::PROTECCION_PASS)
		{
			// Password encriptada
			if (sha1(md5($valor)) == $this->password)
				return true;
			else
				return false;
		}

		return false; // Si no se cumple ninguno, dar siempre FALSE.
	}

	////////////////////////
	// FUNCIONES PRIVADAS //
	////////////////////////

	// Función para generar URL's cortas
	//
	// Devuelve una URL de 6 caracteres (config) o FALSE si falla
	private static function generar_url()
	{
		// Generar una cadena aleatoria
		$longitud = Config::obtener("contenido_url_longitud");
		$letras = Config::obtener("contenido_url_caracteres");
		$cadena = "";

		for ($p = 0; $p < $longitud; $p++) {
			$cadena .= $letras[mt_rand(0, strlen($letras)-1)];
		}

		// Comprobar si ya está siendo usada
		$query="SELECT id FROM contenidos WHERE url='".$cadena."' LIMIT 0, 1";
		$peticion = new DB();

		if ($peticion->ejecutar($query))
		{
			if ($peticion->num_resultados()==0)
				return $cadena; // Si no existe devolverla
			else
				return Contenido::generar_url(); // Si existe, volver a probar
		}
		else
		{
			// Error en la petición SQL. Retornar error.
			$wd = new Watchdog(Watchdog::err_prioridad, "Contenido::generar_url - Error al comprobar la existencia de la URL en la BB.DD. - Query: ".$query);
			return false;
		}
	}

	// Función básica para determianr si el link es hacia un vídeo, imágen o un link de cualquier otro tipo
	//
	// Devuelve el tipo de contenido con las constantes de la clase o FALSE si falla.
	private static function determinar_tipo($contenido=null, $mime=null)
	{
		if ($contenido == null || $mime==null)
			return false;

		// Si el protocolo es de web, probar posibilidad de vídeo
		$protos_web = implode("|", Contenido::$protocolos_web);

		if (preg_match("/^(".$protos_web."):\/{2}/", $contenido))
		{
			foreach (Contenido::$sitios_video as $sitio) {
				// probar con cada uno de los regexp para sitios de vídeo
				if(preg_match("/".$sitio."/", $contenido))
					return Contenido::TIPO_VIDEO; // Si coincide tomar como vídeo
			}
		}

		$extensiones = implode("|", Contenido::$exts_imagen); // Juntar las extensiones de imagen en formato compatible con regexp

		// Comprobar el tipo mime y la extensión
		if (preg_match("/(image\/)/", $mime) || preg_match("/[.](".$extensiones.")$/", $contenido))
			return Contenido::TIPO_IMAGEN;

		// Si no se dio ningún caso, devolver tipo LINK.
		return Contenido::TIPO_LINK;
	}

	// Función para cargar el tipo MIME en una URL usando cURL
	//
	// Devuelve el valor devuelto por cURL
	private static function determinar_mime($contenido)
	{
		$ch = curl_init($contenido);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	}

	// Función para generar un archivo llave personalizado
	//
	// Devuelve la cadena del archivo o FALSE si falla
	private static function generar_keyfile($titulo, $contenido)
	{
		// Comprobación de argumentos
		if ($titulo==null || $contenido==null)
			return false;

		// $letras = "0123456789abcdefghijklmnopqrstuvwxyz-_";
		// $cadena = "";

		// for ($p = 0; $p < Config::obtener("contenido_longitud_key"); $p++) {
		// 	$cadena .= $letras[mt_rand(0, strlen($letras)-1)];
		// }

		// Generar key
		$long=Config::obtener("contenido_longitud_key");
		$fuerte=Config::obtener("contenido_fuerte_key");
		$cadena = bin2hex(openssl_random_pseudo_bytes($long, $fuerte));

		$hash=array();

		$hash[0]=sha1(md5($titulo.$contenido)); // Parte única (posibilidad de colision baja) de título y contenido
		$hash[1]=$cadena; // Cadena aleatoria
		$hash[2]=md5($hash[0]."|".$hash[1]); // Esto se podría usar como suma de comprobación

		return implode("|", $hash); // Separar los hashes por '|'
	}
}

?>