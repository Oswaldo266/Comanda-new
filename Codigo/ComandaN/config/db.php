<?php
class Database {
    private $host = "localhost";
    private $db_name = "sistema_comanda_digital_v1";
    private $username = "root"; // cambia si tienes otro usuario
    private $password = "";     // cambia si tienes contraseña
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                  $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>