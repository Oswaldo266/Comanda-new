<?php
require_once 'models/PedidoModel.php';

class CajaController {
    public function index() {
        $pedidoModel = new PedidoModel();
        // Traemos pedidos listos para cobrar
        $pedidos = $pedidoModel->obtenerPedidosListosParaCobrar();

        $data = [
            'pedidos' => $pedidos,
            'assets_url' => BASE_URL . "public/css/"
        ];

        require_once 'views/caja/caja.php';
    }

    public function registrarPago() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pedido_id = $_POST['pedido_id'];
            $metodo_pago = $_POST['metodo_pago'];
            $usuario_id = 1; // ID del cajero (ejemplo, luego se obtiene del login)

            $pedidoModel = new PedidoModel();
            $pedidoModel->registrarVenta($pedido_id, $metodo_pago, $usuario_id);

            echo json_encode(['success' => true]);
        }
    }
}
?>