<?php
require_once 'models/ProductoModel.php';
require_once 'models/PedidoModel.php';

class MenuController {
    public function index() {
        $mesa = isset($_GET['mesa']) ? intval($_GET['mesa']) : 1;

        $productoModel = new ProductoModel();
        $productos = $productoModel->obtenerProductosActivos();

        $data = [
            'mesa' => $mesa,
            'productos' => $productos,
            'assets_url' => ASSETS_URL
        ];

        require_once 'views/menu/menu.php';
    }

    public function confirmar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mesa_id = $_POST['mesa_id'];
            $items = json_decode($_POST['items'], true);

            $pedidoModel = new PedidoModel();
            $usuario_id = 2; // temporal, luego se usará login de mesero/cliente
            $pedido_id = $pedidoModel->crearPedido($mesa_id, $usuario_id, $items);

            echo "<p>Pedido #$pedido_id creado correctamente para mesa $mesa_id</p>";
            echo "<a href='".BASE_URL."mesa'>Volver a Mesas</a>";
        }
    }
     public function solicitarAsistencia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mesa_id = $_POST['mesa_id'] ?? null;
            
            if ($mesa_id) {
                // Aquí puedes:
                // 1. Guardar en la base de datos
                // 2. Enviar notificación push
                // 3. Integrar con sistema de meseros
                
                // Por ahora, solo log y respuesta exitosa
                error_log("Asistencia solicitada para mesa: " . $mesa_id);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Asistencia registrada para mesa ' . $mesa_id,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                exit();
            }
        }
        
        // Si hay error
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error al solicitar asistencia'
        ]);
        exit();
    }
}
?>