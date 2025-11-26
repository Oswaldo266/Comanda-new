// Variables globales
const cart = [];
const cartItemsContainer = document.getElementById('cartItemsContainer');
const cartTotal = document.getElementById('cartTotal');
const cartBadge = document.getElementById('cartBadge');
const cartData = document.getElementById('cartData');
const floatingCart = document.getElementById('floatingCart');
const cartModal = document.getElementById('cartModal');
const closeCart = document.getElementById('closeCart');
const checkoutBtn = document.getElementById('checkoutBtn');
const pedidoForm = document.getElementById('pedidoForm');
const orderModal = document.getElementById('orderModal');
const closeOrderModal = document.getElementById('closeOrderModal');
const orderDetails = document.getElementById('orderDetails');
const downloadTicketBtn = document.getElementById('downloadTicket');
const continueOrderingBtn = document.getElementById('continueOrdering');
const closeTicketBtn = document.getElementById('closeTicket');

// Event Listeners para botones "Agregar"
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const nombre = btn.dataset.nombre;
        const precio = parseFloat(btn.dataset.precio);

        // Buscar si ya existe en carrito
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.cantidad++;
        } else {
            cart.push({ 
                id, 
                nombre, 
                precio, 
                cantidad: 1 
            });
        }
        
        renderCart();
        showNotification(`${nombre} agregado al carrito`);
    });
});

// Abrir modal del carrito
floatingCart.addEventListener('click', () => {
    cartModal.style.display = 'flex';
});

// Cerrar modal del carrito
closeCart.addEventListener('click', () => {
    cartModal.style.display = 'none';
});

// Cerrar modal al hacer clic fuera
cartModal.addEventListener('click', (e) => {
    if (e.target === cartModal) {
        cartModal.style.display = 'none';
    }
});

// Finalizar pedido
checkoutBtn.addEventListener('click', () => {
    if (cart.length === 0) {
        showNotification('El carrito está vacío');
        return;
    }
    
    // Enviar pedido a mesero y cocina
    enviarPedido();
});

// Función para enviar pedido a mesero y cocina
function enviarPedido() {
    // Actualizar el campo hidden con los datos del carrito
    cartData.value = JSON.stringify(cart);
    
    // Enviar el formulario (esto enviará a mesero y cocina)
    fetch(pedidoForm.action, {
        method: 'POST',
        body: new FormData(pedidoForm)
    })
    .then(response => {
        // No intentamos parsear como JSON, solo verificamos si la respuesta fue exitosa
        if (response.ok) {
            console.log('Pedido enviado exitosamente a mesero y cocina');
            // Mostrar modal de confirmación después de enviar el pedido
            showOrderConfirmation();
        } else {
            console.error('Error al enviar pedido');
            showNotification('Error al enviar el pedido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión');
        // Aún así mostramos la confirmación para no interrumpir el flujo del usuario
        showOrderConfirmation();
    });
}

// Mostrar confirmación de pedido
function showOrderConfirmation() {
    // Cerrar modal del carrito
    cartModal.style.display = 'none';
    
    // Generar detalles del pedido
    const now = new Date();
    const fecha = now.toLocaleDateString();
    const hora = now.toLocaleTimeString();
    
    let orderHTML = `
        <div class="ticket-header">
        <h3>TAQUERÍA EL INFORMÁTICO</h3>
        <div class="separator">---</div>
        <p>Mesa: ${document.querySelector('h1').textContent.match(/Mesa (\d+)/)[1]}</p>
        <p>Fecha: ${fecha}    Hora: ${hora}</p>
        <div class="separator">---</div>
        <div class="order-items">
    `;
    
    let total = 0;
    cart.forEach(item => {
        const itemTotal = item.precio * item.cantidad;
        total += itemTotal;
        orderHTML += `
            <div class="order-item">
            ${item.nombre}    x${item.cantidad} $${itemTotal.toFixed(2)}
            </div>
        `;
    });
    
    orderHTML += `
        </div>
        <div class="separator">---</div>
        <div class="order-total">
            TOTAL: $${total.toFixed(2)}
        </div>
        <div class="separator">---</div>
        <p class="thank-you">¡Gracias por tu pedido!</p>
        </div>
    `;
    
    orderDetails.innerHTML = orderHTML;
    orderModal.style.display = 'flex';
}

// Descargar ticket
downloadTicketBtn.addEventListener('click', () => {
    // Aquí iría la lógica para descargar el ticket
    // Por ahora solo mostramos un mensaje
    showNotification('Funcionalidad de descargar ticket en desarrollo');
});

// Seguir pidiendo
continueOrderingBtn.addEventListener('click', () => {
    orderModal.style.display = 'none';
    // Limpiar carrito después de finalizar pedido
    cart.length = 0;
    renderCart();
    // Redirigir al menú principal (sin mesa específica)
    window.location.href = MENU_URL;
});

// Cerrar ticket
closeTicketBtn.addEventListener('click', () => {
    orderModal.style.display = 'none';
    // Limpiar carrito después de finalizar pedido
    cart.length = 0;
    renderCart();
    // Redirigir al menú principal (sin mesa específica)
    window.location.href = MENU_URL;
});

// Cerrar modal de pedido
closeOrderModal.addEventListener('click', () => {
    orderModal.style.display = 'none';
});

// Cerrar modal al hacer clic fuera
orderModal.addEventListener('click', (e) => {
    if (e.target === orderModal) {
        orderModal.style.display = 'none';
    }
});

// Función para renderizar el carrito
function renderCart() {
    // Limpiar contenedor
    cartItemsContainer.innerHTML = '';
    
    let total = 0;
    
    // Si el carrito está vacío
    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<div class="empty-cart-message">Tu carrito está vacío</div>';
        cartTotal.textContent = '$0.00';
        cartBadge.textContent = '0';
        cartData.value = '';
        return;
    }
    
    // Renderizar cada item del carrito
    cart.forEach(item => {
        const itemTotal = item.precio * item.cantidad;
        total += itemTotal;
        
        const cartItemElement = document.createElement('div');
        cartItemElement.className = 'cart-item';
        cartItemElement.innerHTML = `
            <div class="cart-item-info">
            <div class="cart-item-name">${item.nombre}</div>
            <div class="cart-item-price">$${item.precio.toFixed(2)}</div>
            <div class="cart-item-controls">
                <button class="quantity-btn decrease-btn" data-id="${item.id}">-</button>
                <span class="quantity-display">${item.cantidad}</span>
                <button class="quantity-btn increase-btn" data-id="${item.id}">+</button>
                <button class="cart-item-remove" data-id="${item.id}">
                <i class="fas fa-trash"></i>
                </button>
            </div>
            </div>
            <div class="cart-item-total">$${itemTotal.toFixed(2)}</div>
        `;
        
        cartItemsContainer.appendChild(cartItemElement);
    });
    
    // Actualizar totales
    cartTotal.textContent = `$${total.toFixed(2)}`;
    cartBadge.textContent = cart.reduce((sum, item) => sum + item.cantidad, 0);
    cartData.value = JSON.stringify(cart);
    
    // Agregar event listeners a los botones de control de cantidad
    addCartItemEventListeners();
}

// Función para agregar event listeners a los controles del carrito
function addCartItemEventListeners() {
    // Botones de aumentar cantidad
    document.querySelectorAll('.increase-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const item = cart.find(item => item.id === id);
            if (item) {
                item.cantidad++;
                renderCart();
            }
        });
    });
    
    // Botones de disminuir cantidad
    document.querySelectorAll('.decrease-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const item = cart.find(item => item.id === id);
            if (item) {
                if (item.cantidad > 1) {
                    item.cantidad--;
                } else {
                    // Eliminar item si la cantidad es 1
                    const index = cart.findIndex(item => item.id === id);
                    if (index !== -1) {
                        cart.splice(index, 1);
                    }
                }
                renderCart();
            }
        });
    });
    
    // Botones de eliminar
    document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const index = cart.findIndex(item => item.id === id);
            if (index !== -1) {
                const removedItem = cart.splice(index, 1)[0];
                showNotification(`${removedItem.nombre} eliminado del carrito`);
                renderCart();
            }
        });
    });
}

// Función para mostrar notificaciones
function showNotification(message) {
    // Eliminar notificación anterior si existe
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Crear nueva notificación
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Eliminar notificación después de 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Inicializar carrito
document.addEventListener('DOMContentLoaded', function() {
    renderCart();
});