<?php
// controllers/ReportController.php
require_once __DIR__ . '/../config/database.php';

class ReportController {
    
    public function generarPDF($tipo_reporte) {
        $this->checkAuth();
        $this->generarPDFSimple($tipo_reporte);
    }
    
    private function generarPDFSimple($tipo) {
        $db = Database::getConnection();
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reporte - ' . ucfirst($tipo) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                .title { font-size: 24px; font-weight: bold; }
                .subtitle { font-size: 18px; color: #666; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .total { font-weight: bold; background-color: #e6f3ff; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">Sistema de Comanda Digital</div>
                <div class="subtitle">Reporte ' . ucfirst($tipo) . '</div>
                <div>Generado: ' . date('d/m/Y H:i:s') . '</div>
            </div>';

        switch($tipo) {
            case 'diario':
                $html .= $this->generarReporteDiarioHTML($db);
                break;
            case 'mensual':
                $html .= $this->generarReporteMensualHTML($db);
                break;
            case 'historial':
                $html .= $this->generarHistorialCompletoHTML($db);
                break;
        }

        $html .= '
            <div class="footer">
                Documento generado automáticamente por el Sistema de Comanda Digital
            </div>
        </body>
        </html>';

        // Forzar descarga como HTML 
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Ymd_His') . '.html"');
        echo $html;
        exit;
    }
    
    private function generarReporteDiarioHTML($db) {
        $html = '<h2>Ventas del Día: ' . date('d/m/Y') . '</h2>';
        
        // Ventas totales del día
        $sql_ventas = "SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha_pago) = CURDATE() AND estado = 'pagado'";
        $result = $db->query($sql_ventas);
        $ventas_hoy = $result->fetch_assoc()['total'];
        
        $html .= '<p><strong>Total de ventas hoy: $' . number_format($ventas_hoy, 2) . '</strong></p>';
        
        // Productos vendidos
        $html .= '<h3>Productos Vendidos Hoy</h3>';
        $sql_productos = "SELECT 
            p.nombre as producto,
            SUM(pd.cantidad) as total_vendido,
            SUM(pd.subtotal) as total_ingresos
        FROM pedido_detalles pd
        JOIN productos p ON pd.producto_id = p.id
        JOIN pedidos ped ON pd.pedido_id = ped.id
        WHERE DATE(ped.fecha_creacion) = CURDATE()
        GROUP BY p.id, p.nombre
        ORDER BY total_vendido DESC";
        
        $result = $db->query($sql_productos);
        
        if ($result->num_rows > 0) {
            $html .= '<table>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>';
            
            while($row = $result->fetch_assoc()) {
                $html .= '<tr>
                    <td>' . $row['producto'] . '</td>
                    <td>' . $row['total_vendido'] . ' und</td>
                    <td>$' . number_format($row['total_ingresos'], 2) . '</td>
                </tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p>No hay ventas hoy</p>';
        }
        
        return $html;
    }
    
    private function generarReporteMensualHTML($db) {
        $mes = date('m');
        $anio = date('Y');
        
        $html = '<h2>Reporte Mensual: ' . DateTime::createFromFormat('!m', $mes)->format('F') . " $anio</h2>";
        
        $sql_mensual = "SELECT 
            DATE(ped.fecha_creacion) as fecha,
            COUNT(*) as total_pedidos,
            SUM(ped.total) as total_ventas
        FROM pedidos ped
        WHERE MONTH(ped.fecha_creacion) = ? AND YEAR(ped.fecha_creacion) = ?
        AND ped.estado = 'entregado'
        GROUP BY DATE(ped.fecha_creacion)
        ORDER BY fecha DESC";
        
        $stmt = $db->prepare($sql_mensual);
        $stmt->bind_param("ii", $mes, $anio);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $html .= '<table>
                <tr>
                    <th>Fecha</th>
                    <th>Pedidos</th>
                    <th>Ventas</th>
                </tr>';
            
            $total_ventas = 0;
            $total_pedidos = 0;
            
            while($row = $result->fetch_assoc()) {
                $html .= '<tr>
                    <td>' . date('d/m/Y', strtotime($row['fecha'])) . '</td>
                    <td>' . $row['total_pedidos'] . '</td>
                    <td>$' . number_format($row['total_ventas'], 2) . '</td>
                </tr>';
                
                $total_ventas += $row['total_ventas'];
                $total_pedidos += $row['total_pedidos'];
            }
            
            $html .= '<tr class="total">
                <td><strong>TOTAL</strong></td>
                <td><strong>' . $total_pedidos . '</strong></td>
                <td><strong>$' . number_format($total_ventas, 2) . '</strong></td>
            </tr>';
            $html .= '</table>';
        } else {
            $html .= '<p>No hay datos para este mes</p>';
        }
        
        return $html;
    }
    
    private function generarHistorialCompletoHTML($db) {
        $html = '<h2>Historial Completo de Pedidos</h2>';
        
        $sql_historial = "SELECT 
            ped.id,
            ped.total,
            ped.estado,
            ped.fecha_creacion,
            m.numero_mesa,
            u.nombre as mesero
        FROM pedidos ped
        JOIN mesas m ON ped.mesa_id = m.id
        JOIN usuarios u ON ped.usuario_id = u.id
        ORDER BY ped.fecha_creacion DESC
        LIMIT 100";
        
        $result = $db->query($sql_historial);
        
        if ($result->num_rows > 0) {
            $html .= '<table>
                <tr>
                    <th>ID</th>
                    <th>Mesa</th>
                    <th>Mesero</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>';
            
            while($row = $result->fetch_assoc()) {
                $html .= '<tr>
                    <td>' . $row['id'] . '</td>
                    <td>' . $row['numero_mesa'] . '</td>
                    <td>' . $row['mesero'] . '</td>
                    <td>$' . number_format($row['total'], 2) . '</td>
                    <td>' . $row['estado'] . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($row['fecha_creacion'])) . '</td>
                </tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<p>No hay pedidos registrados</p>';
        }
        
        return $html;
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            die('No autorizado');
        }
    }
}
?>