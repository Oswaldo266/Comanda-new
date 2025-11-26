<?php
// Front Controller - Punto de entrada único

// Configuración básica
session_start();

// IMPORTANTE: Configurar la ruta correcta
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/ComandaN/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Resto del código permanece igual...
// Cargar clases automáticamente
spl_autoload_register(function($class) {
    if (file_exists('controllers/' . $class . '.php')) {
        require_once 'controllers/' . $class . '.php';
    } elseif (file_exists('models/' . $class . '.php')) {
        require_once 'models/' . $class . '.php';
    }
});

// Obtener la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : 'mesa';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlSegments = explode('/', $url);

// Enrutamiento básico
$controllerName = ucfirst($urlSegments[0]) . 'Controller';
$action = isset($urlSegments[1]) ? $urlSegments[1] : 'index';
$params = array_slice($urlSegments, 2);

// Verificar si el controlador existe
if (file_exists('controllers/' . $controllerName . '.php')) {
    $controller = new $controllerName();
    
    if (method_exists($controller, $action)) {
        call_user_func_array([$controller, $action], $params);
    } else {
        http_response_code(404);
        echo "Página no encontrada";
    }
} else {
    header('Location: ' . BASE_URL . 'mesa');
    exit;
}
?>