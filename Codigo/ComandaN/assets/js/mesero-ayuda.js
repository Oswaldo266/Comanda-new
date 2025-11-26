// assets/js/mesero-ayuda.js

document.addEventListener('DOMContentLoaded', function() {
    const botonAyuda = document.getElementById('botonAyuda');
    const ayudaPopup = document.getElementById('ayudaPopup');
    const cerrarAyuda = document.getElementById('cerrarAyuda');
    const listaMesas = document.getElementById('listaMesas');

    // Array para mantener notificaciones no leídas
    let notificacionesPendientes = [];

    // Inicializar sistema de notificaciones
    inicializarNotificaciones();

    // Mostrar popup de ayuda
    botonAyuda.addEventListener('click', function() {
        ayudaPopup.style.display = 'block';
        cargarNotificaciones();
        marcarNotificacionesLeidas();
    });

    // Cerrar popup de ayuda
    if (cerrarAyuda) {
        cerrarAyuda.addEventListener('click', function() {
            ayudaPopup.style.display = 'none';
        });
    }

    // Cerrar popup al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (ayudaPopup.style.display === 'block' && 
            !ayudaPopup.contains(e.target) && 
            e.target !== botonAyuda) {
            ayudaPopup.style.display = 'none';
        }
    });

    // Inicializar sistema de notificaciones
    function inicializarNotificaciones() {
        // Cargar notificaciones existentes
        cargarNotificacionesDelStorage();
        
        // Escuchar nuevas notificaciones
        window.addEventListener('nuevaNotificacion', function(e) {
            const notificacion = e.detail;
            mostrarNotificacionEmergente(notificacion);
            agregarNotificacionPendiente(notificacion);
        });

        // Verificar nuevas notificaciones cada 5 segundos
        setInterval(verificarNuevasNotificaciones, 5000);

        // Actualizar badge del botón
        actualizarBadgeNotificaciones();
    }

    // Mostrar notificación emergente
    function mostrarNotificacionEmergente(notificacion) {
        // Crear elemento de notificación emergente
        const notificacionElem = document.createElement('div');
        notificacionElem.className = 'notificacion-emergente';
        notificacionElem.innerHTML = `
            <div class="notificacion-contenido">
                <div class="notificacion-icono">🔔</div>
                <div class="notificacion-texto">
                    <strong>${notificacion.mensaje}</strong>
                    <span class="notificacion-tiempo">Ahora</span>
                </div>
                <button class="cerrar-notificacion">×</button>
            </div>
        `;

        // Estilos para la notificación emergente
        notificacionElem.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #8B0000;
            color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 1001;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid #FFD700;
        `;

        // Agregar al documento
        document.body.appendChild(notificacionElem);

        // Cerrar notificación después de 5 segundos
        setTimeout(() => {
            if (notificacionElem.parentNode) {
                notificacionElem.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notificacionElem.parentNode) {
                        notificacionElem.remove();
                    }
                }, 300);
            }
        }, 5000);

        // Cerrar manualmente
        const cerrarBtn = notificacionElem.querySelector('.cerrar-notificacion');
        cerrarBtn.addEventListener('click', function() {
            notificacionElem.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notificacionElem.parentNode) {
                    notificacionElem.remove();
                }
            }, 300);
        });
    }

    // Agregar notificación a la lista de pendientes
    function agregarNotificacionPendiente(notificacion) {
        notificacionesPendientes.push(notificacion);
        actualizarBadgeNotificaciones();
        
        // Si el popup está abierto, actualizar la lista
        if (ayudaPopup.style.display === 'block') {
            cargarNotificaciones();
        }
    }

    // Cargar notificaciones del localStorage
    function cargarNotificacionesDelStorage() {
        const notificaciones = JSON.parse(localStorage.getItem('notificaciones_mesero') || '[]');
        notificacionesPendientes = notificaciones.filter(n => !n.leida);
        actualizarBadgeNotificaciones();
    }

    // Verificar nuevas notificaciones
    function verificarNuevasNotificaciones() {
        const notificaciones = JSON.parse(localStorage.getItem('notificaciones_mesero') || '[]');
        const nuevasNotificaciones = notificaciones.filter(n => !n.leida);
        
        // Encontrar notificaciones nuevas
        nuevasNotificaciones.forEach(notificacion => {
            const existe = notificacionesPendientes.some(n => n.timestamp === notificacion.timestamp);
            if (!existe) {
                mostrarNotificacionEmergente(notificacion);
                notificacionesPendientes.push(notificacion);
            }
        });
        
        actualizarBadgeNotificaciones();
    }

    // Cargar notificaciones en el popup
    function cargarNotificaciones() {
        const notificaciones = JSON.parse(localStorage.getItem('notificaciones_mesero') || '[]');
        
        // Filtrar notificaciones de las últimas 24 horas
        const ahora = new Date();
        const notificacionesRecientes = notificaciones.filter(notif => {
            const tiempoNotificacion = new Date(notif.timestamp);
            const diferenciaHoras = (ahora - tiempoNotificacion) / (1000 * 60 * 60);
            return diferenciaHoras <= 24; // Solo últimas 24 horas
        });

        if (notificacionesRecientes.length > 0) {
            mostrarNotificacionesEnPopup(notificacionesRecientes);
        } else {
            mostrarSinNotificaciones();
        }
    }

    // Mostrar notificaciones en el popup
    function mostrarNotificacionesEnPopup(notificaciones) {
        const ahora = new Date();
        let html = '';
        
        notificaciones.forEach(notif => {
            const tiempo = new Date(notif.timestamp);
            const diferenciaMinutos = Math.floor((ahora - tiempo) / (1000 * 60));
            const esNueva = !notif.leida;
            
            html += `
                <div class="mesa-item ${esNueva ? 'nueva' : ''}">
                    <strong>${notif.mensaje}</strong>
                    <span style="font-size: 12px; opacity: 0.8; display: block; margin-top: 5px;">
                        Hace ${diferenciaMinutos} minutos
                        ${esNueva ? ' • <span style="color:#FFD700">NUEVO</span>' : ''}
                    </span>
                </div>
            `;
        });
        
        listaMesas.innerHTML = html;
    }

    // Mostrar mensaje cuando no hay notificaciones
    function mostrarSinNotificaciones() {
        listaMesas.innerHTML = '<div class="mesa-item">No hay solicitudes de asistencia</div>';
    }

    // Marcar notificaciones como leídas
    function marcarNotificacionesLeidas() {
        const notificaciones = JSON.parse(localStorage.getItem('notificaciones_mesero') || '[]');
        const notificacionesActualizadas = notificaciones.map(notif => ({
            ...notif,
            leida: true
        }));
        
        localStorage.setItem('notificaciones_mesero', JSON.stringify(notificacionesActualizadas));
        notificacionesPendientes = [];
        actualizarBadgeNotificaciones();
    }

    // Actualizar badge de notificaciones
    function actualizarBadgeNotificaciones() {
        const count = notificacionesPendientes.length;
        let badge = document.querySelector('.notificacion-badge');
        
        if (!badge && count > 0) {
            badge = document.createElement('span');
            badge.className = 'notificacion-badge';
            botonAyuda.appendChild(badge);
        }
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Actualizar cada 30 segundos si el popup está abierto
    setInterval(() => {
        if (ayudaPopup.style.display === 'block') {
            cargarNotificaciones();
        }
    }, 30000);
});

// Animaciones CSS para notificaciones
const notificacionStyles = document.createElement('style');
notificacionStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .notificacion-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #FFD700;
        color: #000;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        font-weight: bold;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .notificacion-contenido {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notificacion-icono {
        font-size: 20px;
    }
    
    .notificacion-texto {
        flex: 1;
    }
    
    .notificacion-tiempo {
        font-size: 12px;
        opacity: 0.8;
        display: block;
        margin-top: 5px;
    }
    
    .cerrar-notificacion {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .cerrar-notificacion:hover {
        background: rgba(255,255,255,0.2);
    }
    
    .mesa-item.nueva {
        background: rgba(255,215,0,0.1);
        border-left: 3px solid #FFD700;
        padding-left: 10px;
        margin-left: -10px;
    }
`;
document.head.appendChild(notificacionStyles);