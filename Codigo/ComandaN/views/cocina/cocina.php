<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Área de Cocina</title>
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/estilos.css">
</head>
<body>
  <header>
    <h1>Pedidos en Cocina - Tacos y Postres</h1>
  </header>
  <main id="pedidosContainer">
    <?php if (empty($data['pedidos'])): ?>
      <p>No hay pedidos activos de tacos o postres</p>
    <?php else: ?>
      <?php foreach ($data['pedidos'] as $pedido): ?>
        <div class="pedido" data-pedido-id="<?php echo $pedido['id']; ?>">
          <div class="pedido-header">
            <div class="pedido-info">
              <h3>Mesa <?php echo $pedido['mesa_id']; ?> - Pedido #<?php echo $pedido['id']; ?></h3>
              <p>Hora: <?php echo date('H:i:s', strtotime($pedido['fecha_creacion'])); ?></p>
              <p>Estado: <?php echo strtoupper($pedido['estado']); ?></p>
            </div>
          </div>
          
          <ul class="detalles-lista">
            <?php foreach ($pedido['detalles'] as $detalle): ?>
              <li class="detalle-item">
                <div class="detalle-info">
                  <span class="detalle-nombre"><?php echo htmlspecialchars($detalle['nombre']); ?></span>
                  <span class="detalle-cantidad">x<?php echo $detalle['cantidad']; ?></span>
                  <span class="detalle-precio">$<?php echo number_format($detalle['subtotal'], 2); ?></span>
                </div>
                <div class="detalle-controls">
                  <span class="detalle-estado estado-<?php echo $detalle['estado']; ?>">
                    <?php echo strtoupper($detalle['estado']); ?>
                  </span>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <!-- DEFINIR LAS VARIABLES JS PRIMERO -->
  <script>
    // Define la URL base directamente
    const BASE_URL = 'http://localhost/comanda1/';
    const COCINA_URL = BASE_URL + 'cocina/obtenerPedidosActualizados'; 
    const ESTADO_URL = BASE_URL + 'cocina/actualizarDetalle';
  </script>

  <!-- LUEGO cargar el JS -->
  <script src="<?php echo ASSETS_URL; ?>js/cocina.js"></script>
</body>
</html>