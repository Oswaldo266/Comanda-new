<?php
// controllers/AdminController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/observers/NotificationManager.php';
require_once __DIR__ . '/../models/observers/StockObserver.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Mesa.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Pedido.php';

class AdminController {
    private $notificationManager;
    
    public function __construct() {
        $this->notificationManager = new NotificationManager();
        $this->notificationManager->attach(new StockObserver());
    }
    
    public function index() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarFormulario();
        }
        
        $seccion = $_GET['seccion'] ?? 'dashboard';
        
        switch($seccion) {
            case 'dashboard':
                $this->dashboard();
                break;
            case 'mesas':
                $this->gestionMesas();
                break;
            case 'menu':
                $this->gestionMenu();
                break;
            case 'usuarios':
                $this->gestionUsuarios();
                break;
            case 'inventario':
                $this->controlInventario();
                break;
            case 'reportes':
                $this->reportes();
                break;
            default:
                $this->dashboard();
        }
    }
    
    private function procesarFormulario() {
        if (!isset($_POST['accion'])) return;
        
        switch($_POST['accion']) {
            case 'agregar_mesa':
                $this->agregarMesa();
                break;
            case 'eliminar_mesa':
                $this->eliminarMesa();
                break;
            case 'agregar_producto':
                $this->agregarProducto();
                break;
            case 'eliminar_producto':
                $this->eliminarProducto();
                break;
            case 'agregar_usuario':
                $this->agregarUsuario();
                break;
            case 'eliminar_usuario':
                $this->eliminarUsuario();
                break;
            case 'actualizar_inventario':
                $this->actualizarInventario();
                break;
        }
    }
    
    private function agregarMesa() {
        $mesaModel = new Mesa();
        $mesaModel->crear($_POST);
        
        $this->notificationManager->notify('mesa_agregada', [
            'numero_mesa' => $_POST['numero_mesa'],
            'ubicacion' => $_POST['ubicacion'],
            'usuario' => $_SESSION['usuario_nombre']
        ]);
        
        $this->redirect('mesas');
    }
    
    private function agregarProducto() {
        $productoModel = new Producto();
        $productoModel->crear($_POST);
        
        $this->notificationManager->notify('producto_agregado', [
            'producto' => $_POST['nombre'],
            'precio' => $_POST['precio'],
            'usuario' => $_SESSION['usuario_nombre']
        ]);
        
        $this->redirect('menu');
    }
    
    private function actualizarInventario() {
        $inventarioModel = new Inventario();
        $ingrediente_id = $_POST['ingrediente_id'];
        $cantidad_actual = $_POST['cantidad_actual'];
        
        $ingrediente_actual = $inventarioModel->getIngrediente($ingrediente_id);
        $inventarioModel->actualizarCantidad($ingrediente_id, $cantidad_actual);
        
        $this->notificationManager->notify('inventario_actualizado', [
            'ingrediente' => $ingrediente_actual['nombre'],
            'cantidad' => $cantidad_actual,
            'usuario' => $_SESSION['usuario_nombre'],
            'anterior' => $ingrediente_actual['cantidad_actual']
        ]);
        
        if ($cantidad_actual <= $ingrediente_actual['cantidad_minima']) {
            $ingrediente_actualizado = $inventarioModel->getIngrediente($ingrediente_id);
            $this->notificationManager->notify('stock_bajo', $ingrediente_actualizado);
        }
        
        $this->redirect('inventario');
    }
    
    private function eliminarMesa() {
        $mesaModel = new Mesa();
        $mesaModel->eliminar($_POST['mesa_id']);
        $this->redirect('mesas');
    }
    
    private function eliminarProducto() {
        $productoModel = new Producto();
        $productoModel->eliminar($_POST['producto_id']);
        $this->redirect('menu');
    }
    
    private function agregarUsuario() {
        $userModel = new User();
        $userModel->crear($_POST);
        $this->redirect('usuarios');
    }
    
    private function eliminarUsuario() {
        $userModel = new User();
        $userModel->eliminar($_POST['usuario_id']);
        $this->redirect('usuarios');
    }
    
    private function dashboard() {
        $ventasModel = new Venta();
        $pedidosModel = new Pedido();
        $mesasModel = new Mesa();
        $productoModel = new Producto();
        
        $data = [
            'ventas_hoy' => $ventasModel->getVentasHoy(),
            'pedidos_activos' => $pedidosModel->getPedidosActivos(),
            'total_mesas' => $mesasModel->getTotalMesas(),
            'mesas_ocupadas' => $mesasModel->getMesasOcupadas(),
            'productos' => $productoModel->getAllActive(),
            'alertas_recientes' => $this->getAlertasRecientes()
        ];
        
        require __DIR__ . '/../views/admin/dashboard.php';
    }
    
    private function gestionMesas() {
        $mesaModel = new Mesa();
        $data = ['mesas' => $mesaModel->getAll()];
        require __DIR__ . '/../views/admin/mesas.php';
    }
    
    private function gestionMenu() {
        $productoModel = new Producto();
        $db = Database::getConnection();
        
        $sql_categorias = "SELECT * FROM categorias_menu WHERE activa = 1";
        $categorias = $db->query($sql_categorias)->fetch_all(MYSQLI_ASSOC);
        
        $data = [
            'productos' => $productoModel->getAllActive(),
            'categorias' => $categorias
        ];
        
       require __DIR__ . '/../views/admin/menu.php';
    }
    
    private function gestionUsuarios() {
        $userModel = new User();
        $data = ['usuarios' => $userModel->getAll()];
         require __DIR__ . '/../views/admin/usuarios.php';
    }
    
    private function controlInventario() {
        $inventarioModel = new Inventario();
        
        $data = [
            'ingredientes' => $inventarioModel->getAll(),
            'alertas_stock' => $inventarioModel->getStockBajo(),
            'alertas_sistema' => $this->getAlertasSistema()
        ];
        
         require __DIR__ . '/../views/admin/inventario.php';
    }
    
    private function reportes() {
        require __DIR__ . '/../views/admin/reportes.php';
    }
    
    private function getAlertasSistema() {
        $db = Database::getConnection();
        $sql = "SELECT * FROM alertas_sistema WHERE leida = 0 ORDER BY fecha_creacion DESC LIMIT 10";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    private function getAlertasRecientes() {
        $db = Database::getConnection();
        $sql = "SELECT * FROM alertas_sistema ORDER BY fecha_creacion DESC LIMIT 5";
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    private function redirect($seccion) {
        header("Location: " . BASE_URL . "/index.php?seccion=" . $seccion);

        exit();
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: ../index.php?action=login");
            exit();
        }
    }
}
?>