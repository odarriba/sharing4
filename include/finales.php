<?php
/////////////////////////////////////////////////
// Sharing4 - A Content Hub
/////////////////////////////////////////////////
// File: /include/includes.php
//
// Realiza la desconexión de la base de datos y
// la escritura preventiva de los datos de 
// sesión.
//
// Autor: Oscar de Arriba Gonzalez 
//        <odarriba@gmail.com>
/////////////////////////////////////////////////

DB::desconectar();
session_write_close();
?>