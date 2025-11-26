<?php
// controllers/AuthController.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $userModel = new User();
            $usuario = $userModel->login($_POST['username'], $_POST['password']);
            
            if ($usuario) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                // Redirección simple y directa
                header("Location: /comanda1/index.php?seccion=dashboard");
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
                require __DIR__ . '/../views/auth/login.php';
            }
        } else {
            require __DIR__ . '/../views/auth/login.php';
        }
    }
    
    public function logout() {
        session_destroy();
        header("Location: /comanda1/index.php?action=login");
        exit();
    }
}
?>