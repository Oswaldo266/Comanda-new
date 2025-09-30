<?php
// archivo: conexion.php
$host = "localhost";
$usuario = "root";
$password = "";  // Por defecto en XAMPP está vacío
$basedatos = "sistema_comanda_digital_v1";

// Crear conexión
$conexion = new mysqli($host, $usuario, $password, $basedatos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset
$conexion->set_charset("utf8mb4");

// echo "Conexión exitosa"; // Puedes comentar esto después de probar
?>