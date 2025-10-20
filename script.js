document.addEventListener('DOMContentLoaded', function() {
    const cart = [];
    let total = 0;
    const mesaNumero = new URLSearchParams(window.location.search).get('mesa') || '?';
    
    const mesaNumeroEl = document.getElementById('mesa-numero');
    const cartMesaNumeroEl = document.getElementById('cart-mesa-numero');
    if (mesaNumeroEl) mesaNumeroEl.textContent = mesaNumero;
    if (cartMesaNumeroEl) cartMesaNumeroEl.textContent = mesaNumero;

    const cartItemsEl = document.getElementById('cart-items');
    const cartTotalEl = document.getElementById('cart-total-amount');
    const cartBadgeEl = document.querySelector('.cart-badge');
    const headerCartCountEl = document.querySelector('.cart-count');
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const floatingCartEl = document.getElementById('floating-cart');
    const cartModalEl = document.getElementById('cart-modal');
    const closeCartEl = document.getElementById('close-cart');
    const headerCartEl = document.getElementById('header-cart');
    const checkoutBtn = document.querySelector('.checkout-btn');
    
    const ticketModalEl = document.getElementById('ticket-modal');
    const ticketBodyEl = document.getElementById('ticket-body');
    const closeTicketModal = document.querySelector('.close-modal');
    const descargarTicketBtn = document.getElementById('descargar-ticket');
    const seguirPidiendoBtn = document.getElementById('seguir-pidiendo');
    const cerrarTicketBtn = document.getElementById('cerrar-ticket');

    // Actualiza el carrito
    function updateCart() {
        cartItemsEl.innerHTML = '';
        if (cart.length === 0) {
            cartItemsEl.innerHTML = '<p class="empty-cart-message">No has agregado items a tu pedido</p>';
            cartTotalEl.textContent = '$0.00';
            cartBadgeEl.textContent = '0';
            headerCartCountEl.textContent = '0';
            return;
        }
        cart.forEach(item => {
            const itemEl = document.createElement('div');
            itemEl.classList.add('cart-item');
            itemEl.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">$${(item.price * item.quantity).toFixed(2)}</div>
                    <div class="cart-item-controls">
                        <button class="quantity-btn minus-btn" data-id="${item.id}">-</button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn plus-btn" data-id="${item.id}">+</button>
                        <span class="cart-item-remove" data-id="${item.id}">
                            <i class="fas fa-trash"></i>
                        </span>
                    </div>
                </div>
            `;
            cartItemsEl.appendChild(itemEl);
        });
        total = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
        cartTotalEl.textContent = `$${total.toFixed(2)}`;
        const count = cart.reduce((sum, i) => sum + i.quantity, 0);
        cartBadgeEl.textContent = count;
        headerCartCountEl.textContent = count;

        document.querySelectorAll('.minus-btn').forEach(btn =>
            btn.addEventListener('click', () => changeQty(btn.dataset.id, -1))
        );
        document.querySelectorAll('.plus-btn').forEach(btn =>
            btn.addEventListener('click', () => changeQty(btn.dataset.id, 1))
        );
        document.querySelectorAll('.cart-item-remove').forEach(btn =>
            btn.addEventListener('click', () => removeFromCart(btn.dataset.id))
        );
    }

    function addToCart(name, price) {
        const found = cart.find(i => i.name === name);
        if (found) found.quantity++;
        else cart.push({ id: Date.now(), name, price: parseFloat(price), quantity: 1 });
        updateCart();
        showNotification(`${name} agregado`);
    }

    function changeQty(id, delta) {
        const item = cart.find(i => i.id == id);
        if (!item) return;
        item.quantity += delta;
        if (item.quantity <= 0) removeFromCart(id);
        updateCart();
    }

    function removeFromCart(id) {
        const idx = cart.findIndex(i => i.id == id);
        if (idx >= 0) cart.splice(idx, 1);
        updateCart();
    }

    function showNotification(msg) {
        const n = document.createElement('div');
        n.className = 'notification';
        n.textContent = msg;
        document.body.appendChild(n);
        setTimeout(() => n.remove(), 3000);
    }

    // Abrir carrito
    floatingCartEl.addEventListener('click', () => (cartModalEl.style.display = 'flex'));
    headerCartEl.addEventListener('click', () => (cartModalEl.style.display = 'flex'));
    closeCartEl.addEventListener('click', () => (cartModalEl.style.display = 'none'));
    cartModalEl.addEventListener('click', e => {
        if (e.target === cartModalEl) cartModalEl.style.display = 'none';
    });

    // Confirmar pedido (mostrar ticket)
    checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) return showNotification('Tu carrito está vacío');
        mostrarTicket();
    });

    // Mostrar ticket visual
    function mostrarTicket() {
        const now = new Date();
        const fecha = now.toLocaleDateString();
        const hora = now.toLocaleTimeString();
        ticketBodyEl.innerHTML = `
            <pre style="font-family: monospace; text-align:center;">
TAQUERÍA EL INFORMÁTICO
----------------------------------------
Mesa: ${mesaNumero}
Fecha: ${fecha}   Hora: ${hora}
----------------------------------------
${cart.map(i => `${i.name.padEnd(20)} x${i.quantity}  $${(i.price * i.quantity).toFixed(2)}`).join('\n')}
----------------------------------------
TOTAL: $${total.toFixed(2)}
----------------------------------------
¡Gracias por tu pedido!
</pre>
        `;
        ticketModalEl.style.display = 'flex';
    }

    // Descargar ticket como PDF
    descargarTicketBtn.addEventListener('click', () => {
        const contenido = ticketBodyEl.innerText;
        const blob = new Blob([contenido], { type: "application/pdf" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `Ticket_Mesa_${mesaNumero}.pdf`;
        link.click();
    });

    // Seguir pidiendo
    seguirPidiendoBtn.addEventListener('click', () => {
        ticketModalEl.style.display = 'none';
    });

    // Cerrar ticket (limpiar pedido)
    cerrarTicketBtn.addEventListener('click', () => {
        alert('Pedido cerrado correctamente.');
        localStorage.clear();
        window.location.href = 'index.html';
    });

    closeTicketModal.addEventListener('click', () => (ticketModalEl.style.display = 'none'));

    addToCartButtons.forEach(btn =>
        btn.addEventListener('click', () => addToCart(btn.dataset.item, btn.dataset.price))
    );

    updateCart();
});
