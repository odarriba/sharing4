<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/twitter.login.php
//
// Controlador para acceder a la modalidad de
// inicio de sesión usando las credenciales de
// Twitter.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

Auth::go_login(Auth::SERVICIO_TWITTER);

require_once ("../include/finales.php");
?>