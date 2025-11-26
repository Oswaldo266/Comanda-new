
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar en la página del menú
    if (document.querySelector('.add-to-cart')) {
        const cartManager = new CartManager();
        const orderManager = new OrderManager(cartManager);
        const modalManager = new ModalManager(cartManager, orderManager);
        const assistanceManager = new AssistanceManager(); // NUEVO

        // Inicializar carrito
        cartManager.init();

        // Configurar número de mesa en la interfaz
        const mesaNumeroEl = document.getElementById('mesa-numero');
        const cartMesaNumeroEl = document.getElementById('cart-mesa-numero');
        if (mesaNumeroEl) mesaNumeroEl.textContent = cartManager.getMesaNumero();
        if (cartMesaNumeroEl) cartMesaNumeroEl.textContent = cartManager.getMesaNumero();

        // Agregar event listeners a los botones de agregar al carrito
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(btn =>
            btn.addEventListener('click', () => 
                cartManager.addToCart(btn.dataset.item, btn.dataset.price)
            )
        );
    }
});