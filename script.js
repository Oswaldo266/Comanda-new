document.addEventListener('DOMContentLoaded', function() {
    const cart = [];
    let total = 0;
    
    
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
        
       
        total = cart.reduce((sum, item) => sum + item.price, 0);
        cartTotalEl.textContent = `$${total.toFixed(2)}`;
        
        
        cartBadgeEl.textContent = cart.length;
        headerCartCountEl.textContent = cart.length;
        
        
        document.querySelectorAll('.cart-item-remove').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = parseInt(this.getAttribute('data-id'));
                removeFromCart(itemId);
            });
        });
    }
    
    
    function addToCart(name, price) {
        const newItem = {
            id: Date.now(), // ID único
            name: name,
            price: parseFloat(price)
        };
        
        cart.push(newItem);
        updateCart();
        
        
        showNotification(`${name} agregado al carrito`);
    }
    
    
    function removeFromCart(itemId) {
        const itemIndex = cart.findIndex(item => item.id === itemId);
        if (itemIndex !== -1) {
            const removedItem = cart.splice(itemIndex, 1)[0];
            updateCart();
            showNotification(`${removedItem.name} eliminado del carrito`);
        }
    }
    
    
    function showNotification(message) {
        
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.classList.add('notification');
        notification.textContent = message;
        document.body.appendChild(notification);
        
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
  
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemName = this.getAttribute('data-item');
            const itemPrice = this.getAttribute('data-price');
            addToCart(itemName, itemPrice);
        });
    });
    
   
    floatingCartEl.addEventListener('click', function() {
        cartModalEl.style.display = 'flex';
    });
    
    
    headerCartEl.addEventListener('click', function() {
        cartModalEl.style.display = 'flex';
    });
    
    
    closeCartEl.addEventListener('click', function() {
        cartModalEl.style.display = 'none';
    });
    
    
    cartModalEl.addEventListener('click', function(e) {
        if (e.target === cartModalEl) {
            cartModalEl.style.display = 'none';
        }
    });
    
    
    checkoutBtn.addEventListener('click', function() {
        if (cart.length === 0) {
            showNotification('Tu carrito está vacío');
            return;
        }
        
        
        showNotification(`Redirigiendo al pago... Total: $${total.toFixed(2)}`);
        
        
        setTimeout(() => {
            alert(`¡Gracias por tu compra! Total pagado: $${total.toFixed(2)}`);
            
            
            cart.length = 0;
            updateCart();
            cartModalEl.style.display = 'none';
        }, 2000);
    });
    
  
    updateCart();
});