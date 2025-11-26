<?php
// config/config.php

// Configuración de rutas base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$project_folder = 'comanda1'; // Cambia esto si tu carpeta tiene otro nombre

define('BASE_URL', $protocol . "://" . $host . "/" . $project_folder);
define('PROJECT_PATH', __DIR__ . '/..');

// Función helper para URLs
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_comanda_digital_v1');
?>