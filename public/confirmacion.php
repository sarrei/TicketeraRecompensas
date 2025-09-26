<?php
require_once __DIR__ . '/../vendor/autoload.php';

use function App\Helpers\requireLogin;
use function App\Helpers\currentUserId;
use App\Models\Ticket;
use App\Config\DB;

requireLogin();

$compraId = isset($_GET['compra_id']) ? (int)$_GET['compra_id'] : 0;
if (!$compraId) {
    http_response_code(400);
    echo 'Falta compra_id';
    exit;
}

$pdo = DB::getConnection();
$stmt = $pdo->prepare("SELECT * FROM compras WHERE id = ? AND usuario_id = ?");
$stmt->execute([$compraId, (int)currentUserId()]);
$compra = $stmt->fetch();
if (!$compra) {
    http_response_code(404);
    echo 'Compra no encontrada';
    exit;
}

$tickets = Ticket::obtenerPorCompra($compraId);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirmación</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 900px; margin: 0 auto; padding: 1rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .75rem; }
    img { max-width: 100%; height: auto; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Compra exitosa</h1>
    <p>Tu pago fue aprobado. Hemos enviado a tu correo los tickets con sus QR.</p>
    <div class="card">
      <h3>Tickets</h3>
      <?php if (empty($tickets)): ?>
        <p>No se encontraron tickets aún.</p>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($tickets as $t): ?>
            <?php $file = 'qr/' . htmlspecialchars($t['codigo_qr']) . '.png'; ?>
            <div>
              <img src="<?= $file ?>" alt="QR Ticket" />
              <p><small>Código: <?= htmlspecialchars($t['codigo_qr']) ?></small></p>
              <a href="<?= $file ?>" download>Descargar</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div style="margin-top:1rem;"><a href="catalogo.php">Volver al catálogo</a></div>
    </div>
  </div>
</body>
</html>
