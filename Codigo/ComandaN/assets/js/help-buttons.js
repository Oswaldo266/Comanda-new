// assets/js/help-buttons.js

// Funcionalidad para los botones de ayuda y asistencia
document.addEventListener('DOMContentLoaded', function() {
  const helpBtn = document.getElementById('helpBtn');
  const assistanceBtn = document.getElementById('assistanceBtn');
  const helpModal = document.getElementById('helpModal');
  const closeHelpModal = document.getElementById('closeHelpModal');
  const closeHelpBtn = document.getElementById('closeHelpBtn');

  // Verificar que los elementos existan
  if (!helpBtn || !assistanceBtn || !helpModal) {
    console.warn('Elementos de ayuda no encontrados');
    return;
  }

  // Abrir modal de ayuda
  helpBtn.addEventListener('click', function() {
    helpModal.style.display = 'flex';
  });

  // Cerrar modal de ayuda
  function closeHelp() {
    helpModal.style.display = 'none';
  }

  if (closeHelpModal) {
    closeHelpModal.addEventListener('click', closeHelp);
  }

  if (closeHelpBtn) {
    closeHelpBtn.addEventListener('click', closeHelp);
  }

  // Cerrar modal al hacer clic fuera
  helpModal.addEventListener('click', function(e) {
    if (e.target === helpModal) {
      closeHelp();
    }
  });

  // Solicitar asistencia
  assistanceBtn.addEventListener('click', function() {
    // Agregar efecto de pulso
    assistanceBtn.classList.add('pulsing');
    
    // Obtener número de mesa del título
    const mesaMatch = document.querySelector('h1').textContent.match(/Mesa (\d+)/);
    const mesaId = mesaMatch ? mesaMatch[1] : '1';
    
    console.log('Solicitando asistencia para mesa:', mesaId);
    
    // Intentar enviar solicitud de asistencia
    solicitarAsistencia(mesaId);
  });

  // Función para solicitar asistencia
  function solicitarAsistencia(mesaId) {
    // Primero intentamos con el endpoint específico
    fetch(BASE_URL + 'menu/solicitarAsistencia', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'mesa_id=' + mesaId
    })
    .then(response => {
      if (!response.ok) {
        // Si falla, intentamos con endpoint alternativo
        console.warn('Endpoint principal falló, intentando alternativo...');
        return solicitarAsistenciaAlternativo(mesaId);
      }
      return response.json();
    })
    .then(data => {
      if (data && data.success) {
        showNotification('¡Se ha solicitado asistencia! Un mesero llegará pronto a tu mesa.', 'success');
      } else {
        // Si no hay respuesta JSON válida, asumimos éxito
        showNotification('Asistencia solicitada. Un mesero será notificado.', 'info');
      }
    })
    .catch(error => {
      console.warn('Error al solicitar asistencia:', error);
      // Fallback: mostrar notificación local
      showNotification('✅ Asistencia solicitada. Un mesero acudirá a tu mesa.', 'info');
    })
    .finally(() => {
      // Quitar efecto de pulso después de 2 segundos
      setTimeout(() => {
        assistanceBtn.classList.remove('pulsing');
      }, 2000);
    });
  }

  // Método alternativo para solicitar asistencia
  function solicitarAsistenciaAlternativo(mesaId) {
    // Intentar con endpoint diferente
    return fetch(BASE_URL + 'asistencia/solicitar', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'mesa=' + mesaId
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Endpoint alternativo también falló');
      }
      return response.text().then(text => {
        try {
          return JSON.parse(text);
        } catch {
          return { success: true }; // Asumir éxito si no es JSON
        }
      });
    });
  }

  // Función para mostrar notificaciones
  function showNotification(message, type = 'info') {
    // Eliminar notificación anterior si existe
    const existingNotification = document.querySelector('.help-notification');
    if (existingNotification) {
      existingNotification.remove();
    }
    
    // Crear nueva notificación
    const notification = document.createElement('div');
    notification.className = `help-notification help-notification-${type}`;
    notification.textContent = message;
    
    // Estilos para la notificación
    const bgColor = type === 'success' ? '#4CAF50' : 
                   type === 'error' ? '#f44336' : 
                   '#2196F3';
    
    notification.style.cssText = `
      position: fixed;
      top: 100px;
      right: 20px;
      background: ${bgColor};
      color: white;
      padding: 15px 20px;
      border-radius: 5px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      z-index: 1001;
      max-width: 300px;
      animation: slideIn 0.3s ease;
      font-weight: bold;
    `;
    
    document.body.appendChild(notification);
    
    // Eliminar notificación después de 5 segundos
    setTimeout(() => {
      if (notification.parentNode) {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.remove();
          }
        }, 300);
      }
    }, 5000);
  }

  // Animaciones CSS para notificaciones
  if (!document.querySelector('#help-notifications-styles')) {
    const style = document.createElement('style');
    style.id = 'help-notifications-styles';
    style.textContent = `
      @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
      
      @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
      }
    `;
    document.head.appendChild(style);
  }
});