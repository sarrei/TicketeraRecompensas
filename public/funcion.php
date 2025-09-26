<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\FuncionesController;
use function App\Helpers\requireLogin;

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controller = new FuncionesController();
$detalle = $id ? $controller->show($id) : null;
if (!$detalle) {
    http_response_code(404);
    echo 'Función no encontrada';
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Función: <?= htmlspecialchars($detalle['titulo']) ?></title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 900px; margin: 0 auto; padding: 1rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    a.btn { display:inline-block; padding:.6rem .9rem; background:#198754; color:#fff; border-radius:6px; text-decoration:none; }
  </style>
</head>
<body>
  <div class="container">
    <h1><?= htmlspecialchars($detalle['titulo']) ?></h1>
    <div class="card">
      <p><strong>Cine:</strong> <?= htmlspecialchars($detalle['cine_nombre']) ?> (<?= htmlspecialchars($detalle['ciudad']) ?>)</p>
      <p><strong>Sala:</strong> <?= htmlspecialchars($detalle['sala_nombre']) ?></p>
      <p><strong>Fecha y hora:</strong> <?= date('d/m/Y H:i', strtotime($detalle['fecha_funcion'])) ?></p>
      <p><strong>Precio:</strong> Q<?= number_format((float)$detalle['precio'], 2) ?></p>
      <p><strong>Asientos disponibles:</strong> <?= (int)$detalle['asientos_disponibles'] ?></p>
      <div style="margin-top:1rem;">
        <a class="btn" href="asientos.php?funcion_id=<?= (int)$detalle['id'] ?>">Seleccionar asientos</a>
        &nbsp; &nbsp; <a href="catalogo.php">Volver al catálogo</a>
      </div>
    </div>

    <?php if (!empty($detalle['descripcion'])): ?>
      <div class="card" style="margin-top:1rem;">
        <h3>Descripción</h3>
        <p><?= nl2br(htmlspecialchars($detalle['descripcion'])) ?></p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
