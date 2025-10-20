<?php
// models/Inventario.php
class Inventario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM ingredientes WHERE activo = 1 ORDER BY nombre";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getStockBajo() {
        $sql = "SELECT * FROM ingredientes WHERE cantidad_actual <= cantidad_minima AND activo = 1";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    public function actualizarCantidad($id, $cantidad) {
        $sql = "UPDATE ingredientes SET cantidad_actual = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("di", $cantidad, $id);
        return $stmt->execute();
    }
    
    public function getIngrediente($id) {
        $sql = "SELECT * FROM ingredientes WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>