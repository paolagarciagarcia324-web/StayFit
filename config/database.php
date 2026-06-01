<?php

class Database
{
    private $host = "127.0.0.1";   // Servidor de la base de datos
    private $port = "3320";        // Puerto de MySQL
    private $db_name = "stayfit";  // Nombre de la base de datos
    private $username = "root";    // Usuario de MySQL
    private $password = "";        // Contraseña de MySQL

    public $conn; // Variable de conexión

    public function conectar()
    {
        $this->conn = null; // Reinicia la conexión

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $this->conn = new PDO($dsn, $this->username, $this->password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $this->conn;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>