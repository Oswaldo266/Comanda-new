<?php
require_once __DIR__ . '/../layout/header.php';
?>

<div id="inventario" class="content-section">
    <h2>📦 Control de Inventario</h2>
    
    <!-- Inventario de ingredientes -->
    <div class="report-section">
        <h3>🥕 Ingredientes e Insumos</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ingrediente</th>
                        <th>Categoría</th>
                        <th>Cantidad Actual</th>
                        <th>Mínimo</th>
                        <th>Unidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['ingredientes'] as $ingrediente): ?>
                        <?php
                        $estado = $ingrediente['cantidad_actual'] <= $ingrediente['cantidad_minima'] ? '🔴 Stock Bajo' : '🟢 Normal';
                        $color_class = $ingrediente['cantidad_actual'] <= $ingrediente['cantidad_minima'] ? 'stock-bajo' : '';
                        ?>
                        <tr class="<?php echo $color_class; ?>">
                            <td><?php echo $ingrediente['id']; ?></td>
                            <td><strong><?php echo $ingrediente['nombre']; ?></strong></td>
                            <td><?php echo $ingrediente['categoria']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="accion" value="actualizar_inventario">
                                    <input type="hidden" name="ingrediente_id" value="<?php echo $ingrediente['id']; ?>">
                                    <input type="hidden" name="seccion_activa" value="inventario">
                                    <input type="number" name="cantidad_actual" value="<?php echo $ingrediente['cantidad_actual']; ?>" step="0.001" style="width: 80px; padding: 4px;">
                                    <button type="submit" class="btn-sm">Actualizar</button>
                                </form>
                            </td>
                            <td><?php echo $ingrediente['cantidad_minima']; ?></td>
                            <td><?php echo $ingrediente['unidad_medida']; ?></td>
                            <td><?php echo $estado; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alertas de stock bajo -->
    <div class="report-section">
        <h3>⚠️ Alertas de Stock Bajo</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ingrediente</th>
                        <th>Cantidad Actual</th>
                        <th>Mínimo Requerido</th>
                        <th>Diferencia</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['alertas_stock'])): ?>
                        <?php foreach ($data['alertas_stock'] as $row): ?>
                            <?php $diferencia = $row['cantidad_minima'] - $row['cantidad_actual']; ?>
                            <tr style="background-color: #ffe6e6;">
                                <td><strong><?php echo $row['nombre']; ?></strong></td>
                                <td><?php echo $row['cantidad_actual']; ?> <?php echo $row['unidad_medida']; ?></td>
                                <td><?php echo $row['cantidad_minima']; ?> <?php echo $row['unidad_medida']; ?></td>
                                <td>Faltan <?php echo $diferencia; ?> <?php echo $row['unidad_medida']; ?></td>
                                <td>
                                    <button class="btn-sm btn-danger" onclick="alert('Contactar a proveedor: <?php echo $row['proveedor']; ?>')">Pedir</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">✅ Todo el stock está en niveles normales</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>