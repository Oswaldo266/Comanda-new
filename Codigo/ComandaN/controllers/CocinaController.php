<?php
// require_once 'models/PedidoModel.php'; // Asumo que el autoload se encarga

class CocinaController {
    public function index() {
        $pedidoModel = new PedidoModel();
        // Traemos pedidos con sus detalles
        $pedidos = $pedidoModel->obtenerPedidosActivosConDetalles();

        // Filtrar solo tacos y postres
        $pedidosFiltrados = $this->filtrarTacosYPostres($pedidos);

        $data = [
            'pedidos' => $pedidosFiltrados,
            // ASSETS_URL ya está definido en el front controller, no es necesario pasarlo
        ];

        require_once 'views/cocina/cocina.php';
    }

    // Método para obtener pedidos actualizados (AJAX)
    public function obtenerPedidosActualizados() {
        $pedidoModel = new PedidoModel();
        $pedidos = $pedidoModel->obtenerPedidosActivosConDetalles();
        
        // Filtrar solo tacos y postres
        $pedidosFiltrados = $this->filtrarTacosYPostres($pedidos);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'pedidos' => $pedidosFiltrados
        ]);
        
        // 💡 Importante: Detener la ejecución para evitar que se cargue HTML adicional
        exit; 
    }

    // Función para filtrar solo tacos y postres
    private function filtrarTacosYPostres($pedidos) {
        $pedidosFiltrados = [];

        foreach ($pedidos as $pedido) {
            $detallesFiltrados = [];

            foreach ($pedido['detalles'] as $detalle) {
                $nombre = strtolower($detalle['nombre']);
                
                // Filtrar solo tacos y postres
                if (strpos($nombre, 'taco') !== false || 
                    strpos($nombre, 'postre') !== false ||
                    strpos($nombre, 'flan') !== false ||
                    strpos($nombre, 'helado') !== false ||
                    strpos($nombre, 'pastel') !== false) {
                    
                    $detallesFiltrados[] = $detalle;
                }
            }

            // Solo incluir el pedido si tiene tacos o postres
            if (!empty($detallesFiltrados)) {
                $pedidoFiltrado = $pedido;
                $pedidoFiltrado['detalles'] = $detallesFiltrados;
                $pedidosFiltrados[] = $pedidoFiltrado;
            }
        }

        return $pedidosFiltrados;
    }

    // Actualizar estado de un detalle desde cocina (AJAX)
    public function actualizarDetalle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Se debe validar que los datos existan
            $detalle_id = filter_input(INPUT_POST, 'detalle_id', FILTER_VALIDATE_INT);
            $nuevoEstado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

            if ($detalle_id && $nuevoEstado) {
                $pedidoModel = new PedidoModel();
                $pedidoModel->actualizarEstadoDetalle($detalle_id, $nuevoEstado);
                echo json_encode(['success' => true]);
            } else {
                 echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
            }
            
            // 💡 Importante: Detener la ejecución
            exit; 
        }
    }
}