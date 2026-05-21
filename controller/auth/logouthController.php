<?php

session_start(); // Inicia la sesión

class LogouthController
{
    public function salir()
    {
        session_unset(); // Limpia variables de sesión
        session_destroy(); // Destruye la sesión

        header('Location: ../../views/auth/login.php'); // Redirige al login
        exit; // Detiene ejecución
    }
}

$controller = new LogouthController(); // Crea controlador
$controller->salir(); // Ejecuta cierre de sesión

?>