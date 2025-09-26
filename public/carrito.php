<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ComprasController;
use function App\Helpers\requireLogin;
use function App\Helpers\currentUserId;

requireLogin();

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$carrito = $_SESSION['carrito'] ?? null;
if (!$carrito || ($carrito['tipo'] ?? '') !== 'FUNCION') {
    header('Location: catalogo.php');
    exit;
}

$controller = new ComprasController();
$resumen = $controller->resumen($carrito, (int)currentUserId());

$usarPuntos = isset($_POST['usar_puntos']);
$confirma = isset($_POST['confirmar']);
$error = null; $ok = null; $compraId = null;

if ($confirma) {
    $confirm = $controller->confirmar((int)currentUserId(), $carrito, (bool)$usarPuntos);
    if ($confirm['ok']) {
        $ok = true;
        $compraId = (int)$confirm['compra_id'];
        unset($_SESSION['carrito']);
        header('Location: pago.php?compra_id=' . $compraId);
        exit;
    } else {
        $error = $confirm['error'] ?? 'Error al confirmar compra';
    }
}

// Calcular descuento previo (solo para mostrar)
[$descuentoPreview, $puntosUsadosPreview] = ($resumen['calcula_descuento'])($usarPuntos);
$totalPreview = max(0.0, $resumen['subtotal'] - $descuentoPreview);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Carrito</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 900px; margin: 0 auto; padding: 1rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    .row { display:flex; justify-content:space-between; margin:.25rem 0; }
    .muted { color:#666; }
    button { padding:.6rem .9rem; border-radius:6px; border:1px solid #198754; background:#198754; color:#fff; cursor:pointer; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Resumen de compra</h1>
    <?php if ($error): ?><div style="color:#b00020;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card">
      <p><strong>Pelicula:</strong> <?= htmlspecialchars($resumen['funcion']['titulo']) ?></p>
      <p><strong>Cine/Sala:</strong> <?= htmlspecialchars($resumen['funcion']['cine_nombre']) ?> / <?= htmlspecialchars($resumen['funcion']['sala_nombre']) ?></p>
      <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($resumen['funcion']['fecha_funcion'])) ?></p>
      <div class="row"><span>Cantidad</span><span><?= (int)$resumen['cantidad'] ?></span></div>
      <div class="row"><span>Precio unitario</span><span>Q<?= number_format($resumen['precio_unit'],2) ?></span></div>
      <div class="row"><span>Subtotal</span><span>Q<?= number_format($resumen['subtotal'],2) ?></span></div>
      <hr>
      <form method="post">
        <p><strong>Puntos disponibles:</strong> <?= (int)$resumen['puntos'] ?></p>
        <?php if ($resumen['puntos'] >= 100): ?>
          <label><input type="checkbox" name="usar_puntos" <?= $usarPuntos?'checked':'' ?>> Canjear puntos</label>
          <p class="muted">Usará hasta Q<?= number_format($resumen['puntos'] * $resumen['valor_punto_q'], 2) ?> o el subtotal, mínimo requerido: Q100 equivalentes.</p>
        <?php else: ?>
          <p class="muted">Necesitas al menos 100 puntos para canjear (equivalente a Q100).</p>
        <?php endif; ?>
        <?php if ($usarPuntos): ?>
          <div class="row"><span>Descuento por puntos</span><span>- Q<?= number_format($descuentoPreview,2) ?> (<?= (int)$puntosUsadosPreview ?> pts)</span></div>
        <?php endif; ?>
        <div class="row" style="font-weight:700"><span>Total</span><span>Q<?= number_format($totalPreview,2) ?></span></div>
        <div style="margin-top:1rem;">
          <button type="submit" name="confirmar" value="1">Confirmar compra</button>
          &nbsp; <a href="asientos.php?funcion_id=<?= (int)$resumen['funcion']['id'] ?>">Cambiar asientos</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>

