<?php
// models/observers/StockObserver.php
class StockObserver {
    public function update($event, $data) {
        switch($event) {
            case 'stock_bajo':
                $this->manejarStockBajo($data);
                break;
            case 'producto_agotado':
                $this->manejarProductoAgotado($data);
                break;
            case 'inventario_actualizado':
                $this->registrarCambioInventario($data);
                break;
            case 'mesa_ocupada':
                $this->registrarOcupacionMesa($data);
                break;
            case 'pedido_creado':
                $this->registrarNuevoPedido($data);
                break;
        }
    }
    
    private function manejarStockBajo($ingrediente) {
        // PATRÓN OBSERVER APLICADO AQUÍ:
        // Notificación automática cuando el stock está bajo
        $mensaje = "ALERTA_STOCK: {$ingrediente['nombre']} - ";
        $mensaje .= "Actual: {$ingrediente['cantidad_actual']}{$ingrediente['unidad_medida']} - ";
        $mensaje .= "Mínimo: {$ingrediente['cantidad_minima']}{$ingrediente['unidad_medida']}";
        
        $this->registrarEnLog('stock', $mensaje);
        $this->guardarAlertaSistema($ingrediente);
    }
    
    private function manejarProductoAgotado($producto) {
        $mensaje = "PRODUCTO_AGOTADO: {$producto['nombre']} - ID: {$producto['id']}";
        $this->registrarEnLog('productos', $mensaje);
    }
    
    private function registrarCambioInventario($data) {
        $mensaje = "INVENTARIO_ACTUALIZADO: {$data['ingrediente']} - ";
        $mensaje .= "Nueva cantidad: {$data['cantidad']} - ";
        $mensaje .= "Usuario: {$data['usuario']}";
        
        $this->registrarEnLog('inventario', $mensaje);
    }
    
    private function registrarOcupacionMesa($data) {
        $mensaje = "MESA_OCUPADA: Mesa {$data['numero_mesa']} - ";
        $mensaje .= "Ubicación: {$data['ubicacion']} - ";
        $mensaje .= "Hora: {$data['hora']}";
        
        $this->registrarEnLog('mesas', $mensaje);
    }
    
    private function registrarNuevoPedido($data) {
        $mensaje = "NUEVO_PEDIDO: #{$data['id']} - ";
        $mensaje .= "Mesa: {$data['mesa']} - ";
        $mensaje .= "Total: $" . number_format($data['total'], 2);
        
        $this->registrarEnLog('pedidos', $mensaje);
    }
    
    private function registrarEnLog($categoria, $mensaje) {
        $logFile = __DIR__ . '/../../logs/sistema.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$categoria] $mensaje\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    private function guardarAlertaSistema($ingrediente) {
        // PATRÓN OBSERVER APLICADO AQUÍ:
        // Guardar alerta en base de datos para mostrar en el panel
        $db = Database::getConnection();
        $sql = "INSERT INTO alertas_sistema (tipo, mensaje, nivel, leida) VALUES (?, ?, ?, 0)";
        $stmt = $db->prepare($sql);
        
        $tipo = 'stock_bajo';
        $mensaje = "Stock bajo en {$ingrediente['nombre']}";
        $nivel = 'alto';
        
        $stmt->bind_param("sss", $tipo, $mensaje, $nivel);
        $stmt->execute();
    }
}