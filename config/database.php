<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "angkringan_db";
    private $username = "root"; // sesuaikan dengan username database Anda
    private $password = ""; // sesuaikan dengan password database Anda
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>