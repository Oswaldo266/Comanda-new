<?php
require_once 'config/db.php';

class ProductoModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function obtenerProductosActivos() {
        $sql = "SELECT p.*, c.nombre AS categoria 
                FROM productos p 
                JOIN categorias_menu c ON p.categoria_id = c.id
                WHERE p.activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>