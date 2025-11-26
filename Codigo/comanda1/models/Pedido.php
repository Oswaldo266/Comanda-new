<?php
// models/Pedido.php
class Pedido {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    public function getPedidosActivos() {
        $sql = "SELECT COUNT(*) as total FROM pedidos WHERE estado IN ('pendiente', 'confirmado', 'en_preparacion')";
        $result = $this->db->query($sql);
        return $result->fetch_assoc()['total'];
    }
}
?>