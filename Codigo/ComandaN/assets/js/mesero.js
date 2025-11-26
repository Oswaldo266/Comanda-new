// Función para actualizar los pedidos automáticamente
function actualizarPedidos() {
    fetch(MESERO_URL + 'obtenerPedidosActualizados')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarVistaPedidos(data.pedidos);
            }
        })
        .catch(error => {
            console.error('Error al actualizar pedidos:', error);
        });
}

// Función para actualizar la vista con los nuevos pedidos
function actualizarVistaPedidos(pedidos) {
    const pedidosContainer = document.getElementById('pedidosContainer');
    
    if (pedidos.length === 0) {
        pedidosContainer.innerHTML = '<p>No hay pedidos activos</p>';
        return;
    }

    let html = '';
    
    pedidos.forEach(pedido => {
        html += `
            <div class="pedido" data-pedido-id="${pedido.id}">
                <h3>Mesa ${pedido.mesa_id} - Pedido #${pedido.id}</h3>
                <p>Estado general: ${pedido.estado.toUpperCase()}</p>
                <p>Total: $${parseFloat(pedido.total).toFixed(2)}</p>
                <ul>
        `;

        pedido.detalles.forEach(detalle => {
            const estados = {
                'pendiente': 'Pendiente',
                'en_preparacion': 'Preparando', 
                'listo': 'Listo',
                'entregado': 'Entregado'
            };
            
            let options = '';
            for (const [valor, texto] of Object.entries(estados)) {
                const selected = detalle.estado === valor ? 'selected' : '';
                options += `<option value="${valor}" ${selected}>${texto}</option>`;
            }

            html += `
                <li>
                    ${detalle.nombre} 
                    x${detalle.cantidad} 
                    - $${parseFloat(detalle.subtotal).toFixed(2)}
                    <select data-pedido="${pedido.id}" data-detalle="${detalle.id}">
                        ${options}
                    </select>
                </li>
            `;
        });

        html += `
                </ul>
                <button onclick="cerrarPedido(${pedido.id})">Cerrar Pedido</button>
            </div>
        `;
    });

    pedidosContainer.innerHTML = html;
    
    // Re-asignar event listeners a los selects
    asignarEventListeners();
}

// Asignar event listeners a los selects
function asignarEventListeners() {
    document.querySelectorAll('select').forEach(sel => {
        sel.addEventListener('change', function() {
            const detalleId = this.dataset.detalle;
            const nuevoEstado = this.value;

            fetch(MESERO_URL + "actualizarDetalle", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "detalle_id=" + detalleId + "&estado=" + nuevoEstado
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log("Estado actualizado correctamente");
                }
            })
            .catch(error => {
                console.error('Error al actualizar estado:', error);
            });
        });
    });
}

// Cerrar pedido completo
function cerrarPedido(idPedido) {
    if (!confirm('¿Estás seguro de que quieres cerrar este pedido?')) {
        return;
    }
    
    fetch(MESERO_URL + "cerrarPedido", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "pedido_id=" + idPedido
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log("Pedido cerrado correctamente");
            // Actualizar la vista después de cerrar
            actualizarPedidos();
        }
    })
    .catch(error => {
        console.error('Error al cerrar pedido:', error);
    });
}

// Actualizar cada 5 segundos
setInterval(actualizarPedidos, 5000);

// También actualizar cuando la ventana gana el foco
window.addEventListener('focus', actualizarPedidos);

// Inicializar event listeners al cargar
document.addEventListener('DOMContentLoaded', function() {
    asignarEventListeners();
});