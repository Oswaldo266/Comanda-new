<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Seleccionar Mesa</title>
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/estilos.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/mesas.css">
</head>
<body>
  <header>
    <h1>Selecciona tu Mesa</h1>
  </header>

  <main>
    <div class="mesas">
      <?php if (!empty($data['mesas'])): ?>
        <?php foreach ($data['mesas'] as $mesa): ?>
          <div class="mesa" onclick="seleccionarMesa(<?php echo $mesa['id']; ?>)">
            <?php echo htmlspecialchars($mesa['numero_mesa']); ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No hay mesas activas disponibles</p>
      <?php endif; ?>
    </div>
  </main>

  <script>
    function seleccionarMesa(idMesa) {
      window.location.href = "<?php echo BASE_URL; ?>menu?mesa=" + idMesa;
    }
  </script>
</body>
</html>