<?php
// models/Mesa.php
class Mesa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM mesas WHERE activa = 1 ORDER BY numero_mesa";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getTotalMesas() {
        $sql = "SELECT COUNT(*) as total FROM mesas WHERE activa = 1";
        $result = $this->db->query($sql);
        return $result->fetch_assoc()['total'];
    }
    
    public function getMesasOcupadas() {
        $sql = "SELECT COUNT(*) as ocupadas FROM mesas WHERE estado = 'ocupada' AND activa = 1";
        $result = $this->db->query($sql);
        return $result->fetch_assoc()['ocupadas'];
    }
    
    public function crear($data) {
        $sql = "INSERT INTO mesas (numero_mesa, ubicacion) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $data['numero_mesa'], $data['ubicacion']);
        return $stmt->execute();
    }
    
    public function eliminar($id) {
        $sql = "UPDATE mesas SET activa = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>