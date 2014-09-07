<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/includes.php
//
// Archivo para incluir en las cabeceras de los
// archivos del proyecto, incluyendo así todas
// las librerías propias necesarias.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

define('__ROOT__', dirname(dirname(__FILE__))); 
require_once(__ROOT__.'/include/config.php');
require_once(__ROOT__.'/include/db.php');
require_once(__ROOT__.'/include/watchdog.php');
require_once(__ROOT__.'/include/mailer.php');
require_once(__ROOT__.'/include/template.php');

require_once(__ROOT__.'/model/usuario.php');
require_once(__ROOT__.'/model/auth.php');
require_once(__ROOT__.'/model/contenido.php');

DB::conectar();
session_start();

?>