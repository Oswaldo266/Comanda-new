<?php
// generar_pdf.php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    die('No autorizado');
}

$tipo_reporte = $_GET['tipo'] ?? 'diario';

require_once __DIR__ . '/controllers/ReportController.php';
$controller = new ReportController();
$controller->generarPDF($tipo_reporte);
?>