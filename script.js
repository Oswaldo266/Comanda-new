document.addEventListener('DOMContentLoaded', function() {
    const cart = [];
    let total = 0;
    
    // Elementos del DOM
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
    
    // Función para actualizar el carrito
    function updateCart() {
        // Limpiar el carrito
        cartItemsEl.innerHTML = '';
        
        if (cart.length === 0) {
            cartItemsEl.innerHTML = '<p class="empty-cart-message">No has agregado items a tu pedido</p>';
            cartTotalEl.textContent = '$0.00';
            cartBadgeEl.textContent = '0';
            headerCartCountEl.textContent = '0';
            return;
        }
        
        // Agregar items al carrito
        cart.forEach(item => {
            const cartItemEl = document.createElement('div');
            cartItemEl.classList.add('cart-item');
            
            cartItemEl.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                </div>
                <span class="cart-item-remove" data-id="${item.id}">
                    <i class="fas fa-times"></i>
                </span>
            `;
            
            cartItemsEl.appendChild(cartItemEl);
        });
        
        // Calcular total
        total = cart.reduce((sum, item) => sum + item.price, 0);
        cartTotalEl.textContent = `$${total.toFixed(2)}`;
        
        // Actualizar contadores
        cartBadgeEl.textContent = cart.length;
        headerCartCountEl.textContent = cart.length;
        
        // Agregar event listeners a los botones de eliminar
        document.querySelectorAll('.cart-item-remove').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = parseInt(this.getAttribute('data-id'));
                removeFromCart(itemId);
            });
        });
    }
    
    // Función para agregar al carrito
    function addToCart(name, price) {
        const newItem = {
            id: Date.now(), // ID único
            name: name,
            price: parseFloat(price)
        };
        
        cart.push(newItem);
        updateCart();
        
        // Mostrar notificación
        showNotification(`${name} agregado al carrito`);
    }
    
    // Función para eliminar del carrito
    function removeFromCart(itemId) {
        const itemIndex = cart.findIndex(item => item.id === itemId);
        if (itemIndex !== -1) {
            const removedItem = cart.splice(itemIndex, 1)[0];
            updateCart();
            showNotification(`${removedItem.name} eliminado del carrito`);
        }
    }
    
    // Función para mostrar notificación
    function showNotification(message) {
        // Eliminar notificación existente si hay una
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.classList.add('notification');
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Eliminar la notificación después de 3 segundos
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Event listeners para botones "Agregar"
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemName = this.getAttribute('data-item');
            const itemPrice = this.getAttribute('data-price');
            addToCart(itemName, itemPrice);
        });
    });
    
    // Event listener para el botón de carrito flotante
    floatingCartEl.addEventListener('click', function() {
        cartModalEl.style.display = 'flex';
    });
    
    // Event listener para el icono de carrito en el header
    headerCartEl.addEventListener('click', function() {
        cartModalEl.style.display = 'flex';
    });
    
    // Event listener para cerrar el modal
    closeCartEl.addEventListener('click', function() {
        cartModalEl.style.display = 'none';
    });
    
    // Cerrar modal al hacer clic fuera del contenido
    cartModalEl.addEventListener('click', function(e) {
        if (e.target === cartModalEl) {
            cartModalEl.style.display = 'none';
        }
    });
    
    // Event listener para el botón de pago
    checkoutBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            showNotification('Tu carrito está vacío');
            return;
        }
        
        // Simular proceso de pago
        showNotification(`Redirigiendo al pago... Total: $${total.toFixed(2)}`);
        
        // Aquí iría la lógica real de redirección a la pasarela de pago
        setTimeout(() => {
            alert(`¡Gracias por tu compra! Total pagado: $${total.toFixed(2)}`);
            
            // Vaciar carrito después de la compra
            cart.length = 0;
            updateCart();
            cartModalEl.style.display = 'none';
        }, 2000);
    });
    
    // Inicializar carrito
    updateCart();
});