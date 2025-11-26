<?php
require_once 'models/MesaModel.php';

class MesaController {
    public function index() {
        $mesaModel = new MesaModel();
        $mesas = $mesaModel->obtenerMesasActivas();

        // Pasar datos a la vista
        $data = [
            'mesas' => $mesas,
            'assets_url' => ASSETS_URL
        ];

        require_once 'views/mesas/mesas.php';
    }
}
?>