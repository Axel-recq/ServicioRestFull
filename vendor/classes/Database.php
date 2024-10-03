<?php 
class Database {
    // Cambia la configuración de la base de datos según tu servidor
    private $db_host = 'localhost';
    private $db_name = 'php_auth_api';
    private $db_username = 'root';
    private $db_password = '1234'; // Si has habilitado la contraseña en el servidor MySQL, especifica la contraseña 

    public function dbConnection() {
        try {
            $conn = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name, $this->db_username, $this->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn; 
        }
        catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
            exit;
        }
    }
}
?>
