<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /auth/facebook.login.php
//
// Controlador para acceder a la modalidad de
// inicio de sesión usando credenciales de FB.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once ("../include/includes.php");

Auth::go_login(Auth::SERVICIO_FACEBOOK);

require_once ("../include/finales.php");
?>