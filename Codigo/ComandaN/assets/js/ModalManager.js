class ModalManager {
    constructor(cartManager, orderManager) {
        this.cartManager = cartManager;
        this.orderManager = orderManager;
        this.initModals();
    }

    initModals() {
        this.initCartModal();
        this.initTicketModal();
    }

    initCartModal() {
        const floatingCartEl = document.getElementById('floating-cart');
        const cartModalEl = document.getElementById('cart-modal');
        const closeCartEl = document.getElementById('close-cart');
        const headerCartEl = document.getElementById('header-cart');
        const checkoutBtn = document.querySelector('.checkout-btn');

        if (floatingCartEl) {
            floatingCartEl.addEventListener('click', () => this.showCartModal());
        }

        if (headerCartEl) {
            headerCartEl.addEventListener('click', () => this.showCartModal());
        }

        if (closeCartEl) {
            closeCartEl.addEventListener('click', () => this.hideCartModal());
        }

        if (cartModalEl) {
            cartModalEl.addEventListener('click', (e) => {
                if (e.target === cartModalEl) this.hideCartModal();
            });
        }

        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.handleCheckout());
        }
    }

    initTicketModal() {
        const ticketModalEl = document.getElementById('ticket-modal');
        const closeTicketModal = document.querySelector('.close-modal');
        const descargarTicketBtn = document.getElementById('descargar-ticket');
        const seguirPidiendoBtn = document.getElementById('seguir-pidiendo');
        const cerrarTicketBtn = document.getElementById('cerrar-ticket');

        if (closeTicketModal) {
            closeTicketModal.addEventListener('click', () => this.hideTicketModal());
        }

        if (descargarTicketBtn) {
            descargarTicketBtn.addEventListener('click', () => this.orderManager.downloadTicket());
        }

        if (seguirPidiendoBtn) {
            seguirPidiendoBtn.addEventListener('click', () => this.hideTicketModal());
        }

        if (cerrarTicketBtn) {
            cerrarTicketBtn.addEventListener('click', () => window.location.href = 'index.php');
        }

        if (ticketModalEl) {
            ticketModalEl.addEventListener('click', (e) => {
                if (e.target === ticketModalEl) this.hideTicketModal();
            });
        }
    }

    showCartModal() {
        const cartModalEl = document.getElementById('cart-modal');
        if (cartModalEl) {
            cartModalEl.style.display = 'flex';
        }
    }

    hideCartModal() {
        const cartModalEl = document.getElementById('cart-modal');
        if (cartModalEl) {
            cartModalEl.style.display = 'none';
        }
    }

    showTicketModal() {
        const ticketModalEl = document.getElementById('ticket-modal');
        const ticketBodyEl = document.getElementById('ticket-body');
        
        if (ticketModalEl && ticketBodyEl) {
            const ticketContent = this.orderManager.generateTicket();
            ticketBodyEl.innerHTML = `<pre style="font-family: monospace; text-align:center;">${ticketContent}</pre>`;
            ticketModalEl.style.display = 'flex';
        }
    }

    hideTicketModal() {
        const ticketModalEl = document.getElementById('ticket-modal');
        if (ticketModalEl) {
            ticketModalEl.style.display = 'none';
        }
    }

    handleCheckout() {
        if (this.cartManager.getCart().length === 0) {
            NotificationManager.show('Tu carrito está vacío');
            return;
        }

        this.orderManager.saveOrder();
        this.showTicketModal();
        this.cartManager.clearCart();
        this.hideCartModal();
    }
}