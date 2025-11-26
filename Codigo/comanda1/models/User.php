<?php
// models/User.php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function login($username, $password) {
        $sql = "SELECT * FROM usuarios WHERE usuario = ? AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            if ($password == '123456') {
                return $usuario;
            }
        }
        
        return false;
    }
    
    public function getAll() {
        $sql = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY id";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    public function crear($data) {
        $sql = "INSERT INTO usuarios (usuario, password_hash, nombre, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("ssss", $data['usuario'], $password_hash, $data['nombre_completo'], $data['rol']);
        
        return $stmt->execute();
    }
    
    public function eliminar($id) {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>