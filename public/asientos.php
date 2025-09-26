<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AsientosController;
use App\Models\Asiento;
use function App\Helpers\requireRoles;

requireRoles(['CLIENTE','ADMIN']);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$funcionId = isset($_GET['funcion_id']) ? (int)$_GET['funcion_id'] : 0;
if (!$funcionId) {
    http_response_code(400);
    echo 'Falta parametro funcion_id';
    exit;
}

$controller = new AsientosController();
$data = $controller->grid($funcionId);
$detalle = $data['detalle'];
$grid = $data['grid'];
$hayAsientos = $data['hay_asientos'];

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $seleccion = array_map('intval', $_POST['asientos'] ?? []);
    if (empty($seleccion)) {
        $error = 'Selecciona al menos un asiento';
    } else {
        // Verificar que no estén ya ocupados
        $conflictos = Asiento::checkDisponibilidad($funcionId, $seleccion);
        if (!empty($conflictos)) {
            $error = 'Algunos asientos ya no están disponibles. Actualiza y vuelve a intentar.';
        } else {
            // Guardar en carrito de sesión
            $_SESSION['carrito'] = [
                'tipo' => 'FUNCION',
                'funcion_id' => $funcionId,
                'asientos' => $seleccion,
                'precio_unitario' => (float)$detalle['precio'],
                'pelicula' => $detalle['titulo'],
                'cine' => $detalle['cine_nombre'],
                'sala' => $detalle['sala_nombre'],
                'fecha' => $detalle['fecha_funcion'],
            ];
            header('Location: carrito.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seleccionar asientos - <?= htmlspecialchars($detalle['titulo']) ?></title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 1000px; margin: 0 auto; padding: 1rem; }
    .screen { text-align:center; margin: .5rem 0 1rem; font-weight:700; }
    .grid { display:grid; gap:.5rem; justify-content:center; }
    .fila { display:flex; gap:.5rem; align-items:center; }
    .seat { width:34px; height:34px; display:flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #999; }
    .ocupado { background:#dc3545; color:#fff; }
    .libre { background:#19875422; }
    .legend { display:flex; gap:1rem; margin-top:1rem; }
    .error { color:#b00020; margin:.5rem 0; }
    button { padding:.6rem .9rem; border-radius:6px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; cursor:pointer; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="container">
    <h1>Selecciona tus asientos</h1>
    <p><?= htmlspecialchars($detalle['titulo']) ?> · <?= htmlspecialchars($detalle['cine_nombre']) ?> · Sala <?= htmlspecialchars($detalle['sala_nombre']) ?> · <?= date('d/m/Y H:i', strtotime($detalle['fecha_funcion'])) ?> · Q<?= number_format((float)$detalle['precio'], 2) ?></p>

    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (!$hayAsientos): ?>
      <p>No hay asientos configurados para esta sala. Contacta a un administrador.</p>
      <p><a href="funcion.php?id=<?= (int)$detalle['id'] ?>">Volver</a></p>
    <?php else: ?>
    <form method="post">
      <div class="screen">Pantalla</div>
      <div class="grid">
        <?php foreach ($grid as $fila => $asientosFila): ?>
          <div class="fila">
            <div style="width:24px; text-align:center; font-weight:600;"><?= htmlspecialchars($fila) ?></div>
            <?php foreach ($asientosFila as $a): ?>
              <?php if ($a['ocupado']): ?>
                <div class="seat ocupado" title="Ocupado">X</div>
              <?php else: ?>
                <label class="seat libre">
                  <input type="checkbox" name="asientos[]" value="<?= (int)$a['id'] ?>" style="display:none;" />
                  <?= (int)$a['numero'] ?>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="legend">
        <span><span class="seat libre"></span> Libre</span>
        <span><span class="seat ocupado"></span> Ocupado</span>
      </div>
      <div style="margin-top:1rem;">
        <button type="submit">Continuar</button>
        &nbsp; <a href="funcion.php?id=<?= (int)$detalle['id'] ?>">Volver</a>
      </div>
    </form>
    <?php endif; ?>
  </div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
