<?
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /usuario/logout.php
//
// Controlador que cierra la sesión del usuario.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

require_once "../include/includes.php";

Usuario::cerrar_sesion();

header("Location: /");

require_once "../include/finales.php"
?>