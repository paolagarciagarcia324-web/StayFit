<?php

class Database
{
    private $host = "127.0.0.1"; // Servidor de la base de datos
    private $port = "3306"; // Puerto de MySQL
    private $db_name = "stayfit"; // Nombre de la base de datos
    private $username = "root"; // Usuario de MySQL
    private $password = ""; // Contraseña de MySQL

    public $conn; // Variable de conexión

    public function conectar()
    {
        $this->conn = null; // Reinicia la conexión

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8"; // Cadena de conexión

            $this->conn = new PDO($dsn, $this->username, $this->password); // Crea la conexión PDO

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Activa errores por excepción

        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage()); // Muestra error de conexión
        }

        return $this->conn; // Retorna la conexión
    }
}

?>