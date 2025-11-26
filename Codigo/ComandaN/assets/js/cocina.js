// assets/js/cocina.js

/**
 * Variables globales (definidas en el HTML de cocina.php):
 * const COCINA_URL - URL para obtener los pedidos (JSON)
 * const ESTADO_URL - URL para actualizar el estado de un detalle (POST)
 */

// Mapeo de estados para la progresión en la cocina
const ESTADOS_MAP = {
    'pendiente': 'en_proceso',
    'en_proceso': 'terminado',
    'terminado': 'terminado' // Una vez terminado, no avanza más
};

// Función para actualizar los pedidos automáticamente
function actualizarPedidos() {
    fetch(COCINA_URL)
        .then(response => {
            if (!response.ok) {
                // Si falla la red, lanzamos un error
                throw new Error('Error de red: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const lastUpdateElement = document.getElementById('lastUpdate');
            if (data.success) {
                actualizarVistaPedidos(data.pedidos);
                // 💡 Comprobación de existencia añadida aquí:
                if (lastUpdateElement) {
                    lastUpdateElement.textContent = 
                        'Última actualización: ' + new Date().toLocaleTimeString();
                }
            } else {
                console.error('API devolvió un error:', data.error);
            }
        })
        .catch(error => {
            const lastUpdateElement = document.getElementById('lastUpdate');
            // El error original (SyntaxError) se manejaría aquí si persiste.
            console.error('Error al actualizar pedidos:', error);
            // 💡 Comprobación de existencia añadida aquí:
            if (lastUpdateElement) {
                lastUpdateElement.textContent = 
                    'Error al actualizar: ' + new Date().toLocaleTimeString();
            }
        });
}

// Función para actualizar la vista
function actualizarVistaPedidos(pedidos) {
    const pedidosContainer = document.getElementById('pedidosContainer');
    
    if (pedidos.length === 0) {
        pedidosContainer.innerHTML = '<p class="text-center p-5 text-gray-500">No hay pedidos activos de tacos o postres</p>';
        return;
    }

    let html = '';
    
    pedidos.forEach(pedido => {
        // Formatear el estado para el display
        const estadoPedidoDisplay = pedido.estado.toUpperCase().replace('_', ' ');

        html += `
            <div class="pedido" data-pedido-id="${pedido.id}">
                <div class="pedido-header">
                    <div class="pedido-info">
                        <h3>Mesa ${pedido.mesa_id} - Pedido #${pedido.id}</h3>
                        <p>Hora: ${new Date(pedido.fecha_creacion).toLocaleTimeString()}</p>
                        <p>Estado Pedido: ${estadoPedidoDisplay}</p>
                    </div>
                </div>
                <ul class="detalles-lista">
        `;

        pedido.detalles.forEach(detalle => {
            const estadoDetalle = detalle.estado.toLowerCase();
            const estadoDetalleDisplay = estadoDetalle.toUpperCase().replace('_', ' ');

            html += `
                <li class="detalle-item">
                    <div class="detalle-info">
                        <span class="detalle-nombre">${detalle.nombre}</span>
                        <span class="detalle-cantidad">x${detalle.cantidad}</span>
                        <!-- Precio no es esencial para Cocina, pero se mantiene si es necesario -->
                        <span class="detalle-precio">$${parseFloat(detalle.subtotal).toFixed(2)}</span>
                    </div>
                    <div class="detalle-controls">
                        <!-- El onclick llama a la función JS y le pasa el elemento -->
                        <span 
                            class="detalle-estado estado-${estadoDetalle}"
                            data-detalle-id="${detalle.id}"
                            data-current-state="${estadoDetalle}"
                            onclick="cambiarEstado(this)"
                        >
                            ${estadoDetalleDisplay}
                        </span>
                    </div>
                </li>
            `;
        });

        html += `
                </ul>
            </div>
        `;
    });

    pedidosContainer.innerHTML = html;
}


// Función para cambiar el estado del detalle del pedido (interactivo)
function cambiarEstado(element) {
    const detalleId = element.getAttribute('data-detalle-id');
    const currentState = element.getAttribute('data-current-state');
    
    if (!detalleId || !currentState) return;

    const nextState = ESTADOS_MAP[currentState];

    if (nextState === currentState) {
        console.log(`Detalle ${detalleId} ya está en estado final: ${currentState}`);
        return; 
    }

    const formData = new FormData();
    formData.append('detalle_id', detalleId);
    formData.append('estado', nextState);

    // Deshabilitar temporalmente el botón
    element.textContent = 'Actualizando...';
    element.style.pointerEvents = 'none';
    element.style.opacity = '0.7';

    fetch(ESTADO_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
             throw new Error('Error al actualizar el estado: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Éxito: forzar una actualización completa para reflejar el cambio en la vista
            actualizarPedidos();
        } else {
            console.error('Fallo al actualizar estado:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al enviar la actualización:', error);
        // Volver a habilitar en caso de error
        element.textContent = currentState.toUpperCase().replace('_', ' ');
        element.style.pointerEvents = 'auto';
        element.style.opacity = '1';
    });
}


// Actualizar cada 10 segundos
setInterval(actualizarPedidos, 10000);

// Actualizar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarPedidos();
});