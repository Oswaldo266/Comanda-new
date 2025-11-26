<?php
require_once 'config/db.php';

class PedidoModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Crear un nuevo pedido con sus detalles
     */
    public function crearPedido($mesa_id, $usuario_id, $items) {
        try {
            $this->conn->beginTransaction();

            // Insertar pedido
            $sql = "INSERT INTO pedidos (mesa_id, usuario_id, estado, total) VALUES (?, ?, 'pendiente', 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$mesa_id, $usuario_id]);
            $pedido_id = $this->conn->lastInsertId();

            $total = 0;
            foreach ($items as $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $total += $subtotal;

                $sqlDetalle = "INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal, estado) 
                               VALUES (?, ?, ?, ?, ?, 'pendiente')";
                $stmtDetalle = $this->conn->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    $pedido_id,
                    $item['id'],
                    $item['cantidad'],
                    $item['precio'],
                    $subtotal
                ]);
            }

            // Actualizar total del pedido
            $sqlUpdate = "UPDATE pedidos SET total = ? WHERE id = ?";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute([$total, $pedido_id]);

            $this->conn->commit();
            return $pedido_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Obtener pedidos activos (sin detalles)
     */
    public function obtenerPedidosActivos() {
        $sql = "SELECT * FROM pedidos WHERE estado NOT IN ('entregado','cancelado')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener pedidos activos con detalles
     */
    public function obtenerPedidosActivosConDetalles() {
        $sql = "SELECT * FROM pedidos WHERE estado NOT IN ('entregado','cancelado')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            $sqlDetalles = "SELECT d.*, p.nombre 
                            FROM pedido_detalles d 
                            JOIN productos p ON d.producto_id = p.id 
                            WHERE d.pedido_id = ?";
            $stmtDetalles = $this->conn->prepare($sqlDetalles);
            $stmtDetalles->execute([$pedido['id']]);
            $pedido['detalles'] = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
        }

        return $pedidos;
    }

    /**
     * Actualizar estado de un detalle
     */
    public function actualizarEstadoDetalle($detalle_id, $nuevoEstado) {
        $sql = "UPDATE pedido_detalles SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nuevoEstado, $detalle_id]);
    }

    /**
     * Cerrar un pedido (marcar como entregado y eliminar detalles si se desea)
     */
    public function cerrarPedido($pedido_id) {
        $sql = "UPDATE pedidos SET estado = 'entregado' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$pedido_id]);
    }
}
?>