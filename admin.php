<?php
// Archivo: admin.php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];

// CONSULTAS CORREGIDAS
$sql_ventas = "SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha_pago) = CURDATE() AND estado = 'pagado'";
$result = $conexion->query($sql_ventas);
$ventas_hoy = $result->fetch_assoc()['total'];

$sql_pedidos = "SELECT COUNT(*) as total FROM pedidos WHERE estado IN ('pendiente', 'confirmado', 'en_preparacion')";
$result = $conexion->query($sql_pedidos);
$pedidos_activos = $result->fetch_assoc()['total'];

$sql_mesas_total = "SELECT COUNT(*) as total FROM mesas WHERE activa = 1";
$result = $conexion->query($sql_mesas_total);
$total_mesas = $result->fetch_assoc()['total'];

$sql_mesas_ocupadas = "SELECT COUNT(*) as ocupadas FROM mesas WHERE estado = 'ocupada' AND activa = 1";
$result = $conexion->query($sql_mesas_ocupadas);
$mesas_ocupadas = $result->fetch_assoc()['ocupadas'];

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        // Guardar la sección activa antes de procesar
        $seccion_activa = 'dashboard'; // por defecto
        
        if (isset($_POST['seccion_activa'])) {
            $seccion_activa = $_POST['seccion_activa'];
        } elseif ($_POST['accion'] === 'eliminar_mesa' || $_POST['accion'] === 'agregar_mesa') {
            $seccion_activa = 'mesas';
        } elseif ($_POST['accion'] === 'eliminar_producto' || $_POST['accion'] === 'agregar_producto') {
            $seccion_activa = 'menu';
        } elseif ($_POST['accion'] === 'eliminar_usuario' || $_POST['accion'] === 'agregar_usuario') {
            $seccion_activa = 'usuarios';
        } elseif ($_POST['accion'] === 'actualizar_inventario') {
            $seccion_activa = 'inventario';
        }
        
        switch($_POST['accion']) {
            case 'agregar_mesa':
                $numero_mesa = $_POST['numero_mesa'];
                $ubicacion = $_POST['ubicacion'];
                
                $sql = "INSERT INTO mesas (numero_mesa, ubicacion) VALUES (?, ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ss", $numero_mesa, $ubicacion);
                $stmt->execute();
                break;
                
            case 'eliminar_mesa':
                $mesa_id = $_POST['mesa_id'];
                $sql = "UPDATE mesas SET activa = 0 WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $mesa_id);
                $stmt->execute();
                break;
                
            case 'agregar_producto':
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $precio = $_POST['precio'];
                $categoria_id = $_POST['categoria_id'];
                $stock = $_POST['stock'];
                
                $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssdii", $nombre, $descripcion, $precio, $categoria_id, $stock);
                $stmt->execute();
                break;
                
            case 'eliminar_producto':
                $producto_id = $_POST['producto_id'];
                $sql = "UPDATE productos SET activo = 0 WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $producto_id);
                $stmt->execute();
                break;
                
            case 'agregar_usuario':
                $usuario = $_POST['usuario'];
                $password = $_POST['password'];
                $nombre = $_POST['nombre_completo'];
                $rol = $_POST['rol'];
                
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuarios (usuario, password_hash, nombre, rol) VALUES (?, ?, ?, ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ssss", $usuario, $password_hash, $nombre, $rol);
                $stmt->execute();
                break;
                
            case 'eliminar_usuario':
                $usuario_id_eliminar = $_POST['usuario_id'];
                $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $usuario_id_eliminar);
                $stmt->execute();
                break;
                
            case 'actualizar_inventario':
                $ingrediente_id = $_POST['ingrediente_id'];
                $cantidad_actual = $_POST['cantidad_actual'];
                
                $sql = "UPDATE ingredientes SET cantidad_actual = ? WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("di", $cantidad_actual, $ingrediente_id);
                $stmt->execute();
                break;
        }
        
        // Recargar la página para ver los cambios, manteniendo la sección activa
        header("Location: admin.php?seccion=" . $seccion_activa);
        exit();
    }
}

// Obtener la sección activa desde la URL o establecer por defecto
$seccion_activa = isset($_GET['seccion']) ? $_GET['seccion'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Comanda Digital</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="header">
        <h1>Sistema de Comanda Digital - Panel de Administración</h1>
        <div class="user-info">
            <span id="userName"><?php echo $usuario_nombre; ?></span>
            <span id="userRole">(<?php echo $usuario_rol; ?>)</span>
            <span id="dbStatus" class="db-status connected" title="Base de datos conectada">● BD Conectada</span>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li class="active" onclick="showSection('dashboard')">📊 Dashboard</li>
                <li onclick="showSection('mesas')">🪑 Gestión de Mesas</li>
                <li onclick="showSection('menu')">🍽️ Gestión de Menú</li>
                <li onclick="showSection('usuarios')">👥 Gestión de Usuarios</li>
                <li onclick="showSection('inventario')">📦 Control de Inventario</li>
                <li onclick="showSection('reportes')">📊 Reportes</li>
            </ul>
        </div>
        
        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section">
                <h2>📊 Información General</h2>
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>💰 Ventas Hoy</h3>
                        <div class="card-content">
                            <p class="metric-value">$<?php echo number_format($ventas_hoy, 2); ?></p>
                            <p class="metric-label">Total de ventas del día de hoy</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>📦 Pedidos Activos</h3>
                        <div class="card-content">
                            <p class="metric-value"><?php echo $pedidos_activos; ?></p>
                            <p class="metric-label">Pedidos en proceso</p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <h3>🪑 Mesas Ocupadas</h3>
                        <div class="card-content">
                            <p class="metric-value"><?php echo $mesas_ocupadas; ?>/<?php echo $total_mesas; ?></p>
                            <p class="metric-label">Mesas en uso actualmente</p>
                        </div>
                    </div>
                </div>
                
                <!-- Productos desde la Base de Datos -->
                <div class="report-section">
                    <h3>🍽️ Productos del Menú</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_productos = "SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias_menu c ON p.categoria_id = c.id WHERE p.activo = 1 ORDER BY p.id";
                                $result = $conexion->query($sql_productos);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $estado = $row['stock'] > 0 ? '🟢 Disponible' : '🔴 Sin stock';
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td><strong>{$row['nombre']}</strong></td>
                                                <td>{$row['descripcion']}</td>
                                                <td>$" . number_format($row['precio'], 2) . "</td>
                                                <td>{$row['stock']}</td>
                                                <td>{$estado}</td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gestión de Mesas -->
            <div id="mesas" class="content-section" style="display:none;">
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
                                <?php
                                $sql_mesas = "SELECT * FROM mesas WHERE activa = 1 ORDER BY numero_mesa";
                                $result = $conexion->query($sql_mesas);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $estado_color = '';
                                        switch($row['estado']) {
                                            case 'libre': $estado_color = '🟢 Libre'; break;
                                            case 'ocupada': $estado_color = '🔴 Ocupada'; break;
                                            case 'reservada': $estado_color = '🟡 Reservada'; break;
                                            default: $estado_color = $row['estado'];
                                        }
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td><strong>{$row['numero_mesa']}</strong></td>
                                                <td>{$row['ubicacion']}</td>
                                                <td>{$estado_color}</td>
                                                <td>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='accion' value='eliminar_mesa'>
                                                        <input type='hidden' name='mesa_id' value='{$row['id']}'>
                                                        <input type='hidden' name='seccion_activa' value='mesas'>
                                                        <button type='submit' class='btn-sm btn-danger' onclick='return confirm(\"¿Está seguro de eliminar esta mesa?\")'>Eliminar</button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gestión de Menú -->
            <div id="menu" class="content-section" style="display:none;">
                <h2>🍽️ Gestión de Menú</h2>
                
                <!-- Formulario para agregar producto -->
                <div class="report-section">
                    <h3>➕ Agregar Nuevo Producto</h3>
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="accion" value="agregar_producto">
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
                                <?php
                                $sql_categorias = "SELECT * FROM categorias_menu WHERE activa = 1";
                                $result = $conexion->query($sql_categorias);
                                while($categoria = $result->fetch_assoc()) {
                                    echo "<option value='{$categoria['id']}'>{$categoria['nombre']}</option>";
                                }
                                ?>
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
                                <?php
                                $sql_productos = "SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias_menu c ON p.categoria_id = c.id WHERE p.activo = 1 ORDER BY p.id";
                                $result = $conexion->query($sql_productos);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td><strong>{$row['nombre']}</strong></td>
                                                <td>{$row['descripcion']}</td>
                                                <td>$" . number_format($row['precio'], 2) . "</td>
                                                <td>{$row['stock']}</td>
                                                <td>{$row['categoria']}</td>
                                                <td>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='accion' value='eliminar_producto'>
                                                        <input type='hidden' name='producto_id' value='{$row['id']}'>
                                                        <button type='submit' class='btn-sm btn-danger' onclick='return confirm(\"¿Está seguro de eliminar este producto?\")'>Eliminar</button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gestión de Usuarios -->
            <div id="usuarios" class="content-section" style="display:none;">
                <h2>👥 Gestión de Usuarios</h2>
                
                <!-- Formulario para agregar usuario -->
                <div class="report-section">
                    <h3>➕ Agregar Nuevo Usuario</h3>
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="accion" value="agregar_usuario">
                        <div class="form-group">
                            <label>Usuario:</label>
                            <input type="text" name="usuario" required placeholder="Ej: mesero2">
                        </div>
                        <div class="form-group">
                            <label>Contraseña:</label>
                            <input type="password" name="password" required placeholder="Contraseña">
                        </div>
                        <div class="form-group">
                            <label>Nombre Completo:</label>
                            <input type="text" name="nombre_completo" required placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="form-group">
                            <label>Rol:</label>
                            <select name="rol" required>
                                <option value="mesero">Mesero</option>
                                <option value="cocina">Cocina</option>
                                <option value="caja">Caja</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Agregar Usuario</button>
                        </div>
                    </form>
                </div>

                <!-- Lista de usuarios -->
                <div class="report-section">
                    <h3>📋 Usuarios del Sistema</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_usuarios = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY id";
                                $result = $conexion->query($sql_usuarios);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $estado = $row['activo'] ? '🟢 Activo' : '🔴 Inactivo';
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td><strong>{$row['usuario']}</strong></td>
                                                <td>{$row['nombre']}</td>
                                                <td>{$row['rol']}</td>
                                                <td>{$estado}</td>
                                                <td>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='accion' value='eliminar_usuario'>
                                                        <input type='hidden' name='usuario_id' value='{$row['id']}'>
                                                        <button type='submit' class='btn-sm btn-danger' onclick='return confirm(\"¿Está seguro de eliminar este usuario?\")'>Eliminar</button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Control de Inventario -->
            <div id="inventario" class="content-section" style="display:none;">
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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_ingredientes = "SELECT * FROM ingredientes WHERE activo = 1 ORDER BY nombre";
                                $result = $conexion->query($sql_ingredientes);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $estado = $row['cantidad_actual'] <= $row['cantidad_minima'] ? '🔴 Stock Bajo' : '🟢 Normal';
                                        $color_class = $row['cantidad_actual'] <= $row['cantidad_minima'] ? 'stock-bajo' : '';
                                        echo "<tr class='{$color_class}'>
                                                <td>{$row['id']}</td>
                                                <td><strong>{$row['nombre']}</strong></td>
                                                <td>{$row['categoria']}</td>
                                                <td>
                                                    <form method='POST' style='display:inline;'>
                                                        <input type='hidden' name='accion' value='actualizar_inventario'>
                                                        <input type='hidden' name='ingrediente_id' value='{$row['id']}'>
                                                        <input type='number' name='cantidad_actual' value='{$row['cantidad_actual']}' step='0.001' style='width: 80px; padding: 4px;'>
                                                        <button type='submit' class='btn-sm'>Actualizar</button>
                                                    </form>
                                                </td>
                                                <td>{$row['cantidad_minima']}</td>
                                                <td>{$row['unidad_medida']}</td>
                                                <td>{$estado}</td>
                                                <td>
                                                    <span class='btn-sm'>Editar</span>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No hay ingredientes registrados</td></tr>";
                                }
                                ?>
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
                                <?php
                                $sql_alerta = "SELECT * FROM ingredientes WHERE cantidad_actual <= cantidad_minima AND activo = 1";
                                $result = $conexion->query($sql_alerta);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $diferencia = $row['cantidad_minima'] - $row['cantidad_actual'];
                                        echo "<tr style='background-color: #ffe6e6;'>
                                                <td><strong>{$row['nombre']}</strong></td>
                                                <td>{$row['cantidad_actual']} {$row['unidad_medida']}</td>
                                                <td>{$row['cantidad_minima']} {$row['unidad_medida']}</td>
                                                <td>Faltan {$diferencia} {$row['unidad_medida']}</td>
                                                <td>
                                                    <button class='btn-sm btn-danger' onclick='alert(\"Contactar a proveedor: {$row['proveedor']}\")'>Pedir</button>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>✅ Todo el stock está en niveles normales</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reportes -->
            <div id="reportes" class="content-section" style="display:none;">
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
                                <?php
                                $sql_ventas_hoy = "SELECT 
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
                                
                                $result = $conexion->query($sql_ventas_hoy);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                                <td><strong>{$row['producto']}</strong></td>
                                                <td>{$row['total_vendido']} unidades</td>
                                                <td>$" . number_format($row['total_ingresos'], 2) . "</td>
                                                <td>" . date('H:i', strtotime($row['ultima_venta'])) . "</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No hay ventas hoy</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reporte Mensual -->
                <div class="report-section">
                    <h3>📈 Reporte Mensual</h3>
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="accion" value="generar_reporte_mensual">
                        <div class="form-group">
                            <label>Mes:</label>
                            <select name="mes" required>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Año:</label>
                            <input type="number" name="anio" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Generar Reporte</button>
                            <button type="button" class="btn btn-download" onclick="descargarPDF('mensual')">📥 Descargar PDF</button>
                        </div>
                    </form>

                    <?php
                    if (isset($_POST['accion']) && $_POST['accion'] === 'generar_reporte_mensual') {
                        $mes = $_POST['mes'];
                        $anio = $_POST['anio'];
                        
                        $sql_mensual = "SELECT 
                            DATE(ped.fecha_creacion) as fecha,
                            COUNT(*) as total_pedidos,
                            SUM(ped.total) as total_ventas,
                            AVG(ped.total) as promedio_venta
                        FROM pedidos ped
                        WHERE MONTH(ped.fecha_creacion) = ? AND YEAR(ped.fecha_creacion) = ?
                        AND ped.estado = 'entregado'
                        GROUP BY DATE(ped.fecha_creacion)
                        ORDER BY fecha DESC";
                        
                        $stmt = $conexion->prepare($sql_mensual);
                        $stmt->bind_param("ii", $mes, $anio);
                        $stmt->execute();
                        $result_mensual = $stmt->get_result();
                        ?>
                        
                        <div class="table-container" style="margin-top: 20px;">
                            <h4>Historial de Pedidos Mensual - <?php echo DateTime::createFromFormat('!m', $mes)->format('F') . " $anio"; ?></h4>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Total Pedidos</th>
                                        <th>Ventas Totales</th>
                                        <th>Promedio por Pedido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_general = 0;
                                    $total_pedidos = 0;
                                    
                                    if ($result_mensual->num_rows > 0) {
                                        while($row = $result_mensual->fetch_assoc()) {
                                            $total_general += $row['total_ventas'];
                                            $total_pedidos += $row['total_pedidos'];
                                            echo "<tr>
                                                    <td>" . date('d/m/Y', strtotime($row['fecha'])) . "</td>
                                                    <td>{$row['total_pedidos']}</td>
                                                    <td>$" . number_format($row['total_ventas'], 2) . "</td>
                                                    <td>$" . number_format($row['promedio_venta'], 2) . "</td>
                                                  </tr>";
                                        }
                                        
                                        echo "<tr style='background-color: #f8f9fa; font-weight: bold;'>
                                                <td>TOTAL</td>
                                                <td>{$total_pedidos}</td>
                                                <td>$" . number_format($total_general, 2) . "</td>
                                                <td>$" . number_format($total_general / $total_pedidos, 2) . "</td>
                                              </tr>";
                                    } else {
                                        echo "<tr><td colspan='4'>No hay datos para este mes</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    }
                    ?>
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
                                <?php
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
                                
                                $result = $conexion->query($sql_historial);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $estado_color = '';
                                        switch($row['estado']) {
                                            case 'entregado': $estado_color = '🟢 Entregado'; break;
                                            case 'cancelado': $estado_color = '🔴 Cancelado'; break;
                                            case 'en_preparacion': $estado_color = '🟡 En Preparación'; break;
                                            default: $estado_color = $row['estado'];
                                        }
                                        
                                        echo "<tr>
                                                <td>#{$row['id']}</td>
                                                <td>{$row['numero_mesa']}</td>
                                                <td>{$row['mesero']}</td>
                                                <td>$" . number_format($row['total'], 2) . "</td>
                                                <td>{$estado_color}</td>
                                                <td>" . date('d/m/Y H:i', strtotime($row['fecha_creacion'])) . "</td>
                                                <td>
                                                    <button class='btn-sm' onclick='verDetallesPedido({$row['id']})'>Ver Detalles</button>
                                                </td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Al cargar la página, mostrar la sección activa
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const seccion = urlParams.get('seccion');
        
        if (seccion) {
            showSection(seccion);
        } else {
            showSection('dashboard');
        }
    });

    function showSection(sectionId) {
        // Ocultar todas las secciones
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Mostrar la sección seleccionada
        document.getElementById(sectionId).style.display = 'block';
        
        // Actualizar menú activo
        document.querySelectorAll('.sidebar-menu li').forEach(item => {
            item.classList.remove('active');
        });
        
        // Encontrar y activar el elemento del menú correspondiente
        const menuItems = document.querySelectorAll('.sidebar-menu li');
        for (let item of menuItems) {
            if (item.textContent.includes(getMenuText(sectionId))) {
                item.classList.add('active');
                break;
            }
        }
    }

    function getMenuText(sectionId) {
        const menuMap = {
            'dashboard': 'Dashboard',
            'mesas': 'Gestión de Mesas',
            'menu': 'Gestión de Menú',
            'usuarios': 'Gestión de Usuarios',
            'inventario': 'Control de Inventario',
            'reportes': 'Reportes'
        };
        return menuMap[sectionId] || '';
    }

    function logout() {
        if (confirm('¿Está seguro que desea cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    }

    function descargarPDF(tipo) {
        window.open('generar_pdf.php?tipo=' + tipo, '_blank');
    }

    function generarReporteDiario() {
        alert('Generando reporte diario...');
    }

    function verPedidosHoy() {
        alert('Mostrando pedidos de hoy...');
    }

    function cargarHistorialCompleto() {
        alert('Cargando historial completo...');
    }

    function verDetallesPedido(pedidoId) {
        alert('Mostrando detalles del pedido #' + pedidoId);
    }
</script>
</body>
</html>
