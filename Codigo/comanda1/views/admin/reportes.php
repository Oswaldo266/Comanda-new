<?php
require_once __DIR__ . '/../layout/header.php';

// Obtener datos para reportes
$db = Database::getConnection();

// Ventas hoy
$sql_ventas = "SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha_pago) = CURDATE() AND estado = 'pagado'";
$result = $db->query($sql_ventas);
$ventas_hoy = $result->fetch_assoc()['total'];

// Pedidos activos
$sql_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE estado IN ('pendiente', 'confirmado', 'en_preparacion')";
$result = $db->query($sql_pedidos);
$pedidos_activos = $result->fetch_assoc()['total'];

// Productos vendidos hoy
$sql_productos_vendidos = "SELECT 
    p.nombre as producto,
    SUM(pd.cantidad) as total_vendido,
    SUM(pd.subtotal) as total_ingresos,
    MAX(ped.fecha_creacion) as ultima_venta
FROM pedido_detalles pd
JOIN productos p ON pd.producto_id = p.id
JOIN pedidos ped ON pd.pedido_id = ped.id
WHERE DATE(ped.fecha_creacion) = CURDATE()
GROUP BY p.id, p.nombre
ORDER BY total_vendido DESC";

$productos_vendidos = $db->query($sql_productos_vendidos);

// Historial de pedidos
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
LIMIT 50";

$historial_pedidos = $db->query($sql_historial);
?>

<div id="reportes" class="content-section">
    <h2>📊 Reportes del Sistema</h2>
    
    <!-- Reporte Diario -->
    <div class="report-section">
        <h3>📅 Reporte Diario</h3>
        <div class="dashboard-cards">
            <div class="card">
                <h4>Ventas del Día</h4>
                <div class="card-content">
                    <p class="metric-value">$<?php echo number_format($ventas_hoy, 2); ?></p>
                    <p class="metric-label">Total vendido hoy</p>
                    <button class="btn" onclick="generarReporteDiario()">Generar Reporte</button>
                    <button class="btn btn-download" onclick="descargarPDF('diario')">📥 Descargar PDF</button>
                </div>
            </div>
            
            <div class="card">
                <h4>Pedidos del Día</h4>
                <div class="card-content">
                    <p class="metric-value"><?php echo $pedidos_activos; ?></p>
                    <p class="metric-label">Pedidos activos hoy</p>
                    <button class="btn" onclick="verPedidosHoy()">Ver Detalles</button>
                </div>
            </div>
        </div>

        <!-- Productos vendidos hoy -->
        <div class="table-container" style="margin-top: 20px;">
            <h4>🍽️ Productos Vendidos Hoy</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Total Vendido</th>
                        <th>Última Venta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($productos_vendidos->num_rows > 0): ?>
                        <?php while($row = $productos_vendidos->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['producto']; ?></strong></td>
                                <td><?php echo $row['total_vendido']; ?> unidades</td>
                                <td>$<?php echo number_format($row['total_ingresos'], 2); ?></td>
                                <td><?php echo date('H:i', strtotime($row['ultima_venta'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No hay ventas hoy</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Historial Completo de Pedidos -->
    <div class="report-section">
        <h3>📋 Historial Completo de Pedidos</h3>
        <button class="btn" onclick="cargarHistorialCompleto()">Cargar Historial</button>
        <button class="btn btn-download" onclick="descargarPDF('historial')">📥 Descargar PDF</button>
        
        <div class="table-container" style="margin-top: 20px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Mesa</th>
                        <th>Mesero</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($historial_pedidos->num_rows > 0): ?>
                        <?php while($row = $historial_pedidos->fetch_assoc()): ?>
                            <?php
                            $estado_color = '';
                            switch($row['estado']) {
                                case 'entregado': $estado_color = '🟢 Entregado'; break;
                                case 'cancelado': $estado_color = '🔴 Cancelado'; break;
                                case 'en_preparacion': $estado_color = '🟡 En Preparación'; break;
                                default: $estado_color = $row['estado'];
                            }
                            ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo $row['numero_mesa']; ?></td>
                                <td><?php echo $row['mesero']; ?></td>
                                <td>$<?php echo number_format($row['total'], 2); ?></td>
                                <td><?php echo $estado_color; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_creacion'])); ?></td>
                                <td>
                                    <button class='btn-sm' onclick='verDetallesPedido(<?php echo $row['id']; ?>)'>Ver Detalles</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>