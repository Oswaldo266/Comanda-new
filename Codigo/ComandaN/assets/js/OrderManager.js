class OrderManager {
    constructor(cartManager) {
        this.cartManager = cartManager;
    }

    saveOrder() {
        const pedidos = JSON.parse(localStorage.getItem("pedidos")) || {};
        const mesaKey = "mesa_" + this.cartManager.getMesaNumero();
        const pedido = this.cartManager.getCart().map(i => ({
            nombre: i.name,
            cantidad: i.quantity,
            precio: i.price,
            estado: "pendiente"
        }));

        pedidos[mesaKey] = pedido;
        localStorage.setItem("pedidos", JSON.stringify(pedidos));
        
        return pedido;
    }

    generateTicket() {
        const cart = this.cartManager.getCart();
        const total = this.cartManager.getTotal();
        const mesaNumero = this.cartManager.getMesaNumero();
        const now = new Date();
        const fecha = now.toLocaleDateString();
        const hora = now.toLocaleTimeString();

        return `
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
`;
    }

    downloadTicket() {
        const ticketContent = this.generateTicket();
        const blob = new Blob([ticketContent], { type: "text/plain" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `Ticket_Mesa_${this.cartManager.getMesaNumero()}.txt`;
        link.click();
    }
}