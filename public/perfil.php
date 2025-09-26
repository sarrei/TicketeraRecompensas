<?php
require_once __DIR__ . '/../vendor/autoload.php';

use function App\Helpers\requireRoles;
use function App\Helpers\currentUserId;
use App\Models\Usuario;
use App\Config\DB;

requireRoles(['CLIENTE','ADMIN']);

$user = Usuario::findById((int)currentUserId());

$pdo = DB::getConnection();
$compras = $pdo->prepare("SELECT * FROM compras WHERE usuario_id = ? ORDER BY creado_en DESC");
$compras->execute([(int)currentUserId()]);
$compras = $compras->fetchAll() ?: [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi Perfil</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 1000px; margin: 0 auto; padding: 1rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ccc; padding:.5rem; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="container">
    <h1>Mi Perfil</h1>
    <div class="card">
      <p><strong>Nombre:</strong> <?= htmlspecialchars($user['nombre'] ?? '') ?></p>
      <p><strong>Correo:</strong> <?= htmlspecialchars($user['correo'] ?? '') ?></p>
      <p><strong>Nivel:</strong> <?= htmlspecialchars($user['nivel_nombre'] ?? '') ?></p>
      <p><strong>Puntos:</strong> <?= (int)($user['puntos'] ?? 0) ?></p>
    </div>
    <h2>Historial de compras</h2>
    <table>
      <tr><th>ID</th><th>Estado</th><th>Total</th><th>Puntos usados</th><th>Fecha</th></tr>
      <?php foreach ($compras as $c): ?>
        <tr>
          <td><?= (int)$c['id'] ?></td>
          <td><?= htmlspecialchars($c['estado']) ?></td>
          <td>Q<?= number_format((float)$c['precio_total'],2) ?></td>
          <td><?= (int)$c['puntos_usados'] ?></td>
          <td><?= htmlspecialchars($c['creado_en']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

