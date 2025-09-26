<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\PagosController;
use function App\Helpers\requireRoles;
use function App\Helpers\currentUserId;

requireRoles(['CLIENTE','ADMIN']);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$compraId = isset($_GET['compra_id']) ? (int)$_GET['compra_id'] : 0;
if (!$compraId) {
    http_response_code(400);
    echo 'Falta compra_id';
    exit;
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $numero = $_POST['numero'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $vencimiento = $_POST['vencimiento'] ?? '';

    $controller = new PagosController();
    $res = $controller->procesarPago((int)currentUserId(), $compraId, $numero, $cvv, $vencimiento);
    if ($res['ok']) {
        header('Location: confirmacion.php?compra_id=' . $compraId);
        exit;
    } else {
        $error = $res['error'] ?? 'Error en el pago';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pago</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 600px; margin: 0 auto; padding: 1rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    label { display:block; margin-top:.5rem; font-weight:600; }
    input, button { width: 100%; padding: .75rem; margin-top: .25rem; border-radius: 6px; border: 1px solid #999; }
    button { background: #0d6efd; color: white; border-color: #0d6efd; cursor: pointer; }
    .row { display:flex; gap:.5rem; }
    .error { color:#b00020; margin:.5rem 0; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="container">
    <h1>Pago</h1>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card">
      <form method="post">
        <label>Titular de la tarjeta</label>
        <input type="text" name="titular" required />
        <label>Número de tarjeta</label>
        <input type="text" name="numero" inputmode="numeric" pattern="^[0-9]{16}$" maxlength="19" placeholder="1234 5678 9012 3456" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,16)" required />
        <div class="row">
          <div style="flex:1;">
            <label>Vencimiento (MM/YY)</label>
            <input type="text" name="vencimiento" placeholder="MM/YY" pattern="^(0[1-9]|1[0-2])\/[0-9]{2}$" oninput="formatExpiry(this)" required />
          </div>
          <div style="flex:1;">
            <label>CVV</label>
            <input type="text" name="cvv" inputmode="numeric" pattern="^[0-9]{3}$" maxlength="3" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,3)" required />
          </div>
        </div>
        <button type="submit" style="margin-top:1rem;">Pagar</button>
      </form>
    </div>
  </div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
  <script>
    function formatExpiry(el){
      let v = el.value.replace(/[^0-9]/g,'').slice(0,4);
      if(v.length >= 3){ el.value = v.slice(0,2) + '/' + v.slice(2); }
      else { el.value = v; }
    }
  </script>
</body>
</html>
