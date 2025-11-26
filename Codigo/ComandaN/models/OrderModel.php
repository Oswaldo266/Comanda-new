<?php

class OrderModel {
    private $pdo;

    public function __construct() {
        // Configuración de la conexión PDO
        $host = "localhost";
        $dbname = "sistema_comanda_digital_v1"; // Cambia esto
        $user = "root";                // Usuario de XAMPP
        $pass = "";                    // Contraseña (vacía por defecto en XAMPP)
        $charset = "utf8mb4";

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Guardar pedido y detalles
    public function saveOrder($mesa, $items) {
        try {
            $this->pdo->beginTransaction();

            // Insertar pedido
            $stmt = $this->pdo->prepare("INSERT INTO pedidos (mesa_id, fecha, estado) VALUES (?, NOW(), 'pendiente')");
            $stmt->execute([$mesa]);
            $pedidoId = $this->pdo->lastInsertId();

            // Insertar detalles
            $stmtDetalle = $this->pdo->prepare("INSERT INTO pedido_detalles (pedido_id, producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtDetalle->execute([
                    $pedidoId,
                    $item['nombre'],
                    $item['cantidad'],
                    $item['precio']
                ]);
            }

            $this->pdo->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Obtener pedidos pendientes
    public function getPendingOrders() {
        $stmt = $this->pdo->query("
            SELECT pd.id AS pedido_id, p.mesa_id, pd.producto, pd.cantidad
            FROM pedido_detalles pd
            JOIN pedidos p ON pd.pedido_id = p.id
            WHERE p.estado = 'pendiente'
            ORDER BY p.id ASC
        ");
        return $stmt->fetchAll();
    }

    // Marcar pedido como completado
    public function completeOrder($pedidoId) {
        $stmt = $this->pdo->prepare("UPDATE pedidos SET estado = 'completado' WHERE id = ?");
        $stmt->execute([$pedidoId]);
        return $stmt->rowCount();
    }
}
