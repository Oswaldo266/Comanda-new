<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Área de Meseros</title>
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/estilos.css">
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/mesero-ayuda.css">
</head>
<body>
  <header>
    <h1>Pedidos Activos</h1>
    <button id="botonAyuda">Ayuda</button>
  </header>

  <main id="pedidosContainer">
    <?php if (empty($data['pedidos'])): ?>
      <p>No hay pedidos activos</p>
    <?php else: ?>
      <?php foreach ($data['pedidos'] as $pedido): ?>
        <div class="pedido" data-pedido-id="<?php echo $pedido['id']; ?>">
          <h3>Mesa <?php echo $pedido['mesa_id']; ?> - Pedido #<?php echo $pedido['id']; ?></h3>
          <p>Estado general: <?php echo strtoupper($pedido['estado']); ?></p>
          <p>Total: $<?php echo number_format($pedido['total'], 2); ?></p>

          <!-- Detalles del pedido -->
          <ul>
            <?php foreach ($pedido['detalles'] as $detalle): ?>
              <li>
                <?php echo htmlspecialchars($detalle['nombre']); ?> 
                x<?php echo $detalle['cantidad']; ?> 
                - $<?php echo number_format($detalle['subtotal'], 2); ?>
                <select data-pedido="<?php echo $pedido['id']; ?>" data-detalle="<?php echo $detalle['id']; ?>">
                  <option value="pendiente" <?php if($detalle['estado']=='pendiente') echo 'selected'; ?>>Pendiente</option>
                  <option value="en_preparacion" <?php if($detalle['estado']=='en_preparacion') echo 'selected'; ?>>Preparando</option>
                  <option value="listo" <?php if($detalle['estado']=='listo') echo 'selected'; ?>>Listo</option>
                  <option value="entregado" <?php if($detalle['estado']=='entregado') echo 'selected'; ?>>Entregado</option>
                </select>
              </li>
            <?php endforeach; ?>
          </ul>

          <button onclick="cerrarPedido(<?php echo $pedido['id']; ?>)">Cerrar Pedido</button>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <!-- Popup de ayuda -->
  <div class="ayuda-popup" id="ayudaPopup">
    <button class="cerrar-ayuda" id="cerrarAyuda">&times;</button>
    <h3>Ayuda Solicitada</h3>
    <div id="listaMesas">
      <!-- Aquí se mostrarán las mesas que pidieron ayuda -->
    </div>
  </div>

  <!-- Variables globales para JavaScript -->
  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const MESERO_URL = '<?php echo BASE_URL; ?>mesero/';
  </script>

  <script src="<?php echo ASSETS_URL; ?>js/mesero.js"></script>
  <script src="<?php echo ASSETS_URL; ?>js/mesero-ayuda.js"></script>
</body>
</html>