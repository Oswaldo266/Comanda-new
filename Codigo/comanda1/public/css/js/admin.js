// public/js/admin.js

document.addEventListener('DOMContentLoaded', function () {
    // Detectar sección activa desde la URL
    const urlParams = new URLSearchParams(window.location.search);
    const seccion = urlParams.get('seccion') || 'dashboard';

    // Activar el ítem del menú correspondiente
    const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
    menuItems.forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === seccion) {
            item.classList.add('active');
        }
    });
});

function logout() {
    if (confirm('¿Está seguro que desea cerrar sesión?')) {
        window.location.href = '/comanda1/index.php?action=logout';
    }
}

function descargarPDF(tipo) {
    window.open('/comanda1/generar_pdf.php?tipo=' + tipo, '_blank');
}