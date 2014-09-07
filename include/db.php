<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/db.php
//
// Archivo que contiene las funcionalidades
// básicas de base de datos (conexiones,
// ejecución, etc) para intentar aislar su uso
// en el caso de cambiar de driver SQL llegado
// el punto.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

// Class DB
// Clase que aisla las llamadas al conector/driver SQL para permitir un cambio si fuese necesario.
class DB
{

	private static $conexion = null;
	private $peticion = null;

	// conectar()
	// Función para conectar con la base de datos según lo especificado
	// en la configuración.
	// Devuelve el objeto mysqli
	public static function conectar()
	{
		self::$conexion = new mysqli(Config::obtener("db_servidor"), Config::obtener("db_usuario"), Config::obtener("db_password"), Config::obtener("db_database"));

		if (self::$conexion->connect_error) 
		{
	    	header("Location: /500.html");

	    	$wd = new Watchdog(Watchdog::err_critico, 'Error de conexión a base de datos (' . self::$conexion->connect_errno . ') ' . self::$conexion->connect_error);
	    	
	    	die();
		}

		self::$conexion->set_charset("utf8");

		if (self::$conexion->errno > 0)
		{
			header("Location: /500.html");

	    	$wd = new Watchdog(Watchdog::err_critico, 'Error al cambiar el charset de la conexión a la base de datos (' . self::$conexion->errno . ') ' . self::$conexion->error);
	    	
	    	die();
		}
		
		return self::$conexion;
	}

	// Función que ejecuta una determianda consulta al driver de la BB.DD. y devuelve TRUE o FALSE
	// dependiendo de si se ejecutó la consulta o no.
	public function ejecutar($query = null)
	{
		// Comprobar que se halla pasado una query válida.
		if (gettype($query) != "string")
		{
			// Esto no debería pasar y puede deberse a una brecha de seguridad. Registrarlo.
			$wd = new WatchDog(WatchDog::err_prioridad, "DB::ejecutar - No se ha recibido una cadena válida.");
			return false;
		}

		if (self::$conexion == null)
			conectar();
		
		// Hacer la petición al driver.
		$this->peticion = self::$conexion->query($query);

		if ($this->peticion == false) 
			return false;
		else
			return true;
	}

	// Función que devuelve el número de resultados de la petición.
	public function num_resultados()
	{
		// Sólo actuar cuando hay un resultado del tipo esperado.
		if (get_class($this->peticion)=="mysqli_result")
		{	
			// Devolver el valor.
			return $this->peticion->num_rows;
		}
		else
		{
			return false;
		}
	}

	// Función que devuelve el último ID introducido por una operacion INSERT.
	public function insert_id()
	{
		return DB::$conexion->insert_id;
	}

	// Función que coloca el puntero de obtención de resultados en la posición
	// indicada, teniendo que ser un n´mero entre 0 y (num_resultados()-1).
	public function posicion($posicion = 0)
	{
		// Si no se pasa entero, error
		if (gettype($posicion) != "integer")
		{
			// Esto no debería pasar y puede deberse a una brecha de seguridad. Registrarlo.
			$wd = new WatchDog(WatchDog::err_prioridad, "DB::posicion - No se ha recibido una entero en la variable de posición.");
			return false;
		}

		// Sólo actuar cuando el objeto de petición es del tipo correcto
		if (get_class($this->peticion)=="mysqli_result")
		{
			// Poner el puntero en posición
			return $this->peticion->data_seek($posicion);
		}
		else
		{
			return false;
		}
	}

	// Función que obtiene el siguiente resultado a partir de la posición
	// del puntero de la petición.
	// Devuelve un array asociativo.
	public function resultado()
	{
		// Sólo actuar cuando el resultado es del tipo esperado
		if (get_class($this->peticion)=="mysqli_result")
		{
			// Devolver el array asociativo
			return $this->peticion->fetch_assoc();
		}
		else
		{
			return false;
		}
	}

	// Función que devuelve una matriz completa en el que cada fila es un resultado,
	// con su array asociativo dentro
	public function array_resultados()
	{
		// Comprobar que los resultados sean de un tipo válido
		if (get_class($this->peticion)=="mysqli_result")
		{
			$num_resultados = num_resultados();
			posicion(0); // Colocarse al principio de los resultados.
			$resultado = array();

			for ($i = 0; $i < $numero_reusltados; $i++)
			{
				// Cargar cada resultado
				$resultado[$i]=resultado();
			}

			return $resultado;
		}
		else
		{
			return false;
		}
	}

	// Función para liberar la memoria ocupada por la petición.
	public function liberar()
	{
		if (get_class($this->peticion)=="mysqli_result")
		{
			return $this->peticion->free();
		}
		else
		{
			return false;
		}
	}

	// desconectar()
	// Función que devuelve el resultado de la petición de desconexion
	// a la base de datos actual.
	// Devuelve un booleano
	public static function desconectar()
	{
		if(self::$conexion != null)
		{
			return self::$conexion->close();
		}

		return false;
	}
}
?>