<?php
// Archivo: login.php
session_start();
require_once 'conexion.php';

// Procesar login si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Buscar usuario en la base de datos
    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        
        // Verificar contraseña (en producción usar password_verify)
        if ($password == '123456') { // Temporal - para prueba
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            // Redirigir al admin
            header("Location: admin.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Comanda Digital - Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Sistema de Comanda Digital</h1>
            <p>Panel de Administración</p>
        </div>
        
        <form id="loginForm" method="POST" action="login.php">
            <div class="input-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" placeholder="Ingrese su usuario" required>
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
            </div>
            
            <button type="submit" class="btn" id="loginBtn">
                <span id="loginText">Iniciar Sesión</span>
                <div id="loadingSpinner" class="spinner" style="display: none;"></div>
            </button>
            
            <?php if (isset($error)): ?>
            <div id="errorMessage" class="error-message" style="display: block;">
                <?php echo $error; ?>
            </div>
            <?php else: ?>
            <div id="errorMessage" class="error-message">
                Usuario o contraseña incorrectos. Intente nuevamente.
            </div>
            <?php endif; ?>
        </form>
        
        <div class="admin-info">
            <p>Usuarios de prueba: admin, mesero1, cocina1, caja1 | Contraseña: 123456</p>
        </div>
    </div>

    <script>
        // JavaScript para la interfaz (loading, etc.)
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const loginText = document.getElementById('loginText');
            const spinner = document.getElementById('loadingSpinner');
            
            // Mostrar loading
            loginText.style.display = 'none';
            spinner.style.display = 'inline-block';
            loginBtn.disabled = true;
        });
    </script>
</body>
</html>
