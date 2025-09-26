<?php
require_once __DIR__ . '/../vendor/autoload.php';

use function App\Helpers\requireRole;
use function App\Helpers\currentUserId;
use App\Controllers\TicketsController;

// Restringido a AGENTE (o ADMIN vía allowAdmin=true)
requireRole('AGENTE', true);

$resultado = null; $error = null;
$code = isset($_GET['code']) ? trim((string)$_GET['code']) : '';
$ticketId = isset($_GET['ticket_id']) && $_GET['ticket_id']!=='' ? (int)$_GET['ticket_id'] : null;
if ($code || $ticketId) {
    $controller = new TicketsController();
    $res = $controller->validar((int)currentUserId(), $code ?: null, $ticketId ?: null);
    if ($res['ok']) {
        $resultado = 'VALIDO';
    } else {
        $resultado = 'INVALIDO';
        $error = $res['error'] ?? 'No válido';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Validación de Tickets</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 960px; margin: 0 auto; padding: 1rem; }
    .ok { color:#198754; font-weight:700; }
    .bad { color:#dc3545; font-weight:700; }
    input, button { padding:.6rem .9rem; border-radius:6px; border:1px solid #999; }
  </style>
  </head>
<body>
  <div class="container">
    <h1>Validar Ticket</h1>
    <form method="get" class="card">
      <div style="margin:.5rem 0;">
        <label>Ingresar código de ticket (del QR) o ID</label><br>
        <input type="text" name="code" placeholder="Código QR" value="<?= htmlspecialchars($code) ?>" style="width:60%;" />
        <span> ó </span>
        <input type="number" name="ticket_id" placeholder="ID" value="<?= htmlspecialchars((string)($ticketId ?? '')) ?>" style="width:20%;" />
        <button type="submit">Validar</button>
      </div>
    </form>
    <?php if ($resultado): ?>
      <p class="<?= $resultado==='VALIDO'?'ok':'bad' ?>">
        <?= $resultado==='VALIDO'?'✅ Ticket válido y marcado como usado':'❌ Ticket inválido' ?>
        <?php if ($error && $resultado==='INVALIDO'): ?><br><small><?= htmlspecialchars($error) ?></small><?php endif; ?>
      </p>
    <?php endif; ?>
  </div>
</body>
</html>
