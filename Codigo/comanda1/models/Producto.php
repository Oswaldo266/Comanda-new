<?php
// models/Producto.php
class Producto {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function getAllActive() {
        $sql = "SELECT p.*, c.nombre as categoria FROM productos p 
                LEFT JOIN categorias_menu c ON p.categoria_id = c.id 
                WHERE p.activo = 1 ORDER BY p.id";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    public function crear($data) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssdii", $data['nombre'], $data['descripcion'], $data['precio'], $data['categoria_id'], $data['stock']);
        return $stmt->execute();
    }
    
    public function eliminar($id) {
        $sql = "UPDATE productos SET activo = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>