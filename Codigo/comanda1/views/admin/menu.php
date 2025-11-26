<?php
require_once __DIR__ . '/../layout/header.php';
?>

<div id="menu" class="content-section">
    <h2>🍽️ Gestión de Menú</h2>
    
    <!-- Formulario para agregar producto -->
    <div class="report-section">
        <h3>➕ Agregar Nuevo Producto</h3>
        <form method="POST" class="form-grid">
            <input type="hidden" name="accion" value="agregar_producto">
            <input type="hidden" name="seccion_activa" value="menu">
            <div class="form-group">
                <label>Nombre del Producto:</label>
                <input type="text" name="nombre" required placeholder="Ej: Lomo Saltado">
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion" required placeholder="Descripción del producto..."></textarea>
            </div>
            <div class="form-group">
                <label>Precio:</label>
                <input type="number" name="precio" step="0.01" required placeholder="Ej: 35.00">
            </div>
            <div class="form-group">
                <label>Categoría:</label>
                <select name="categoria_id" required>
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($data['categorias'] as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Stock Inicial:</label>
                <input type="number" name="stock" required min="0" placeholder="Ej: 10">
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Agregar Producto</button>
            </div>
        </form>
    </div>

    <!-- Lista de productos -->
    <div class="report-section">
        <h3>📋 Productos del Menú</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['productos'] as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo $row['nombre']; ?></strong></td>
                            <td><?php echo $row['descripcion']; ?></td>
                            <td>$<?php echo number_format($row['precio'], 2); ?></td>
                            <td><?php echo $row['stock']; ?></td>
                            <td><?php echo $row['categoria']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="accion" value="eliminar_producto">
                                    <input type="hidden" name="producto_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="seccion_activa" value="menu">
                                    <button type="submit" class="btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este producto?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>