<?php
require_once 'models/PedidoModel.php';

class MeseroController {
    public function index() {
        $pedidoModel = new PedidoModel();
        $pedidos = $pedidoModel->obtenerPedidosActivosConDetalles();

        $data = [
            'pedidos' => $pedidos,
            'assets_url' => BASE_URL . "public/css/"
        ];

        require_once 'views/mesero/mesero.php';
    }

    // Nuevo método para obtener pedidos actualizados (AJAX)
    public function obtenerPedidosActualizados() {
        $pedidoModel = new PedidoModel();
        $pedidos = $pedidoModel->obtenerPedidosActivosConDetalles();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'pedidos' => $pedidos
        ]);
    }

    // Actualizar estado de un detalle
    public function actualizarDetalle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detalle_id = $_POST['detalle_id'];
            $nuevoEstado = $_POST['estado'];

            $pedidoModel = new PedidoModel();
            $pedidoModel->actualizarEstadoDetalle($detalle_id, $nuevoEstado);

            echo json_encode(['success' => true]);
        }
    }

    // Cerrar pedido completo
    public function cerrarPedido() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pedido_id = $_POST['pedido_id'];

            $pedidoModel = new PedidoModel();
            $pedidoModel->cerrarPedido($pedido_id);

            echo json_encode(['success' => true]);
        }
    }
}
?>