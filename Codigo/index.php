<?php
// index.php
session_start();

// Configurar rutas
$action = $_GET['action'] ?? 'admin';

// Routing simple
switch($action) {
    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;
    case 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
    default:
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        $controller->index();
        break;
}
?>