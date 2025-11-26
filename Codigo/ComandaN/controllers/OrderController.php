<?php
require_once 'models/OrderModel.php';

class OrderController {
    private $orderModel;

    public function __construct() {
        $this->orderModel = new OrderModel();
    }

    /**
     * Guardar pedido desde el menú (POST)
     * Se espera JSON: { mesa: 1, items: [{nombre, cantidad, precio_unitario}, ...] }
     */
    public function save() {
        header('Content-Type: application/json');

        // Obtener datos enviados desde JS
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['mesa']) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }

        try {
            $pedidoId = $this->orderModel->saveOrder($data['mesa'], $data['items']);
            echo json_encode(['status' => 'success', 'pedido_id' => $pedidoId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtener todos los pedidos pendientes (para cocina)
     */
    public function getPendingOrders() {
        header('Content-Type: application/json');

        try {
            $pedidos = $this->orderModel->getPendingOrders();
            echo json_encode(['status' => 'success', 'pedidos' => $pedidos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Marcar un pedido como completado
     * Se espera JSON: { pedido_id: 1 }
     */
    public function complete() {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['pedido_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID del pedido']);
            return;
        }

        try {
            $rows = $this->orderModel->completeOrder($data['pedido_id']);
            echo json_encode(['status' => 'success', 'updated_rows' => $rows]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
