<?php
require_once __DIR__ . '/../layout/header.php';
?>

<div id="mesas" class="content-section">
    <h2>🪑 Gestión de Mesas</h2>
    
    <!-- Formulario para agregar mesa -->
    <div class="report-section">
        <h3>➕ Agregar Nueva Mesa</h3>
        <form method="POST" class="form-grid">
            <input type="hidden" name="accion" value="agregar_mesa">
            <input type="hidden" name="seccion_activa" value="mesas">
            <div class="form-group">
                <label>Número de Mesa:</label>
                <input type="text" name="numero_mesa" required placeholder="Ej: M07">
            </div>
           
            <div class="form-group">
                <label>Ubicación:</label>
                <input type="text" name="ubicacion" required placeholder="Ej: Terraza, Interior">
            </div>
            <div class="form-group">
                <button type="submit" class="btn">Agregar Mesa</button>
            </div>
        </form>
    </div>

    <!-- Lista de mesas existentes -->
    <div class="report-section">
        <h3>📋 Mesas Existentes</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Número</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['mesas'] as $row): ?>
                        <?php
                        $estado_color = '';
                        switch($row['estado']) {
                            case 'libre': $estado_color = '🟢 Libre'; break;
                            case 'ocupada': $estado_color = '🔴 Ocupada'; break;
                            case 'reservada': $estado_color = '🟡 Reservada'; break;
                            default: $estado_color = $row['estado'];
                        }
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo $row['numero_mesa']; ?></strong></td>
                            <td><?php echo $row['ubicacion']; ?></td>
                            <td><?php echo $estado_color; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="accion" value="eliminar_mesa">
                                    <input type="hidden" name="mesa_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="seccion_activa" value="mesas">
                                    <button type="submit" class="btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta mesa?')">Eliminar</button>
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