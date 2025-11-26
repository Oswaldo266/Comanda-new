        </div> <!-- cierre de main-content -->
    </div> <!-- cierre de container -->

    <script>
    // JavaScript simplificado y confiable
    function showSection(sectionId) {
        console.log('Mostrando sección:', sectionId);
        
        // Ocultar todas las secciones
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Mostrar la sección seleccionada
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
        
        // Actualizar menú activo
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Activar el item del menú correspondiente
        const activeMenuItem = document.querySelector(`.sidebar-menu .menu-item[data-section="${sectionId}"]`);
        if (activeMenuItem) {
            activeMenuItem.classList.add('active');
        }
        
        // Actualizar URL
        updateURL(sectionId);
    }

    function updateURL(sectionId) {
        const newUrl = `${window.location.pathname}?seccion=${sectionId}`;
        window.history.pushState({section: sectionId}, '', newUrl);
    }

    function logout() {
        if (confirm('¿Está seguro que desea cerrar sesión?')) {
            window.location.href = '/comanda1/index.php?action=logout';
        }
    }

    function descargarPDF(tipo) {
        window.open('/comanda1/generar_pdf.php?tipo=' + tipo, '_blank');
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inicializando navegación...');
        
        // Agregar event listeners a los items del menú
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            item.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section');
                showSection(sectionId);
            });
        });
        
        // Mostrar sección inicial basada en URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialSection = urlParams.get('seccion') || 'dashboard';
        showSection(initialSection);
        
        // Manejar navegación con botones de atrás/adelante
        window.addEventListener('popstate', function(event) {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('seccion') || 'dashboard';
            showSection(section);
        });
    });

    // Funciones de reportes (pueden mantenerse vacías por ahora)
    function generarReporteDiario() {
        alert('Generando reporte diario...');
    }

    function verPedidosHoy() {
        alert('Mostrando pedidos de hoy...');
    }

    function cargarHistorialCompleto() {
        alert('Cargando historial completo...');
    }

    function verDetallesPedido(pedidoId) {
        alert('Mostrando detalles del pedido #' + pedidoId);
    }
    </script>
</body>
</html>