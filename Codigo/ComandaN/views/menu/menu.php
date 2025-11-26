<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú - Mesa <?php echo $data['mesa']; ?></title>
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/estilos.css">
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/help-buttons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header>
    <h1>Taquería El Informático - Mesa <?php echo $data['mesa']; ?></h1>
  </header>

  <main>
    <?php 
    $categorias = [];
    foreach ($data['productos'] as $producto) {
        $categorias[$producto['categoria']][] = $producto;
    }
    ?>

    <?php foreach ($categorias as $categoria => $productos): ?>
      <section class="menu-section">
        <h2><?php echo htmlspecialchars($categoria); ?></h2>
        <div class="menu-items">
          <?php foreach ($productos as $p): ?>
            <div class="menu-item">
              <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
              <p><?php echo htmlspecialchars($p['descripcion']); ?></p>
              <p class="price">$<?php echo number_format($p['precio'], 2); ?></p>
              <button class="add-to-cart" 
                      data-id="<?php echo $p['id']; ?>" 
                      data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>" 
                      data-precio="<?php echo $p['precio']; ?>">
                Agregar
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </main>

  <!-- Botones flotantes de ayuda y asistencia -->
  <div class="help-buttons">
    <button class="help-btn" id="helpBtn" title="Ayuda">
      <i class="fas fa-question-circle"></i>
    </button>
    <button class="assistance-btn" id="assistanceBtn" title="Solicitar Asistencia">
      <i class="fas fa-bell"></i>
    </button>
  </div>

  <!-- Modal de ayuda -->
  <div class="modal" id="helpModal">
    <div class="modal-content help-modal">
      <div class="modal-header">
        <h1>¿Cómo usar el menú?</h1>
        <span class="close-modal" id="closeHelpModal">&times;</span>
      </div>
      <div class="help-content">
        <ul>
          <li><i class="fas fa-mouse-pointer"></i> Haz clic en "Agregar" para añadir items a tu pedido</li>
          <li><i class="fas fa-shopping-cart"></i> Usa el carrito para ver y modificar tu pedido</li>
          <li><i class="fas fa-plus-minus"></i> Puedes ajustar cantidades con los botones + y -</li>
          <li><i class="fas fa-paper-plane"></i> Presiona "FINALIZAR PEDIDO" cuando termines</li>
        </ul>
      </div>
      <div class="modal-buttons">
        <button class="modal-btn" id="closeHelpBtn">Entendido</button>
      </div>
    </div>
  </div>

  <!-- Carrito flotante -->
  <div class="floating-cart" id="floatingCart">
    <i class="fas fa-shopping-cart"></i>
    <span class="cart-badge" id="cartBadge">0</span>
  </div>

  <!-- Modal del carrito -->
  <div class="cart-modal" id="cartModal">
    <div class="cart-content">
      <div class="cart-header">
        <h2>Tu Pedido - Mesa <?php echo $data['mesa']; ?></h2>
        <span class="close-cart" id="closeCart">&times;</span>
      </div>
      <div class="cart-items" id="cartItemsContainer">
        <!-- Los items del carrito se insertarán aquí dinámicamente -->
      </div>
      <div class="cart-total">
        <span>Total:</span>
        <span id="cartTotal">$0.00</span>
      </div>
      
      <!-- Formulario para enviar pedido a mesero y cocina -->
      <form method="POST" action="<?php echo BASE_URL; ?>menu/confirmar" id="pedidoForm">
        <input type="hidden" name="mesa_id" value="<?php echo $data['mesa']; ?>">
        <input type="hidden" name="items" id="cartData">
        <button type="button" class="checkout-btn" id="checkoutBtn">FINALIZAR PEDIDO</button>
      </form>
    </div>
  </div>

  <!-- Modal de confirmación de pedido -->
  <div class="modal" id="orderModal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Ticket de Pedido</h2>
        <span class="close-modal" id="closeOrderModal">&times;</span>
      </div>
      <div class="order-details" id="orderDetails">
        <!-- Los detalles del pedido se insertarán aquí -->
      </div>
      <div class="modal-buttons">
        <button class="modal-btn" id="downloadTicket">Descargar Ticket</button>
        <button class="modal-btn" id="continueOrdering">Seguir Pidiendo</button>
        <button class="modal-btn" id="closeTicket">Cerrar Ticket</button>
      </div>
    </div>
  </div>

  <!-- Variables globales para JavaScript -->
  <script>
    // Variables globales para URLs
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const MENU_URL = '<?php echo BASE_URL; ?>menu';
    const COCINA_URL = '<?php echo BASE_URL; ?>cocina/actualizarDetalle';
  </script>

  <script src="<?php echo ASSETS_URL; ?>js/carrito.js"></script>
  <script src="<?php echo ASSETS_URL; ?>js/help-buttons.js"></script>
</body>
</html>