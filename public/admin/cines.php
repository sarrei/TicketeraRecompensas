<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use function App\Helpers\requireRole;
use App\Models\Cine;

requireRole('ADMIN');

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            Cine::create(trim($_POST['nombre'] ?? ''), trim($_POST['ciudad'] ?? ''), $_POST['direccion'] ?? null);
        } elseif ($action === 'update') {
            Cine::update((int)$_POST['id'], trim($_POST['nombre'] ?? ''), trim($_POST['ciudad'] ?? ''), $_POST['direccion'] ?? null);
        } elseif ($action === 'delete') {
            Cine::delete((int)$_POST['id']);
        }
        header('Location: cines.php');
        exit;
    } catch (Throwable $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$cines = Cine::listAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Cines</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 1000px; margin: 0 auto; padding: 1rem; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ccc; padding:.5rem; }
    input, textarea, select, button { padding:.4rem; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../partials/header.php'; ?>
  <div class="container">
    <h1>Cines</h1>
    <p><a href="../catalogo.php">Volver al sitio</a> | <a href="peliculas.php">Películas</a> | <a href="salas.php">Salas</a> | <a href="funciones.php">Funciones</a></p>
    <?php if ($error): ?><div style="color:#b00020;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <h2>Nuevo</h2>
    <form method="post">
      <input type="hidden" name="action" value="create">
      <input name="nombre" placeholder="Nombre" required>
      <input name="ciudad" placeholder="Ciudad" required>
      <input name="direccion" placeholder="Dirección">
      <button type="submit">Guardar</button>
    </form>
    <h2>Listado</h2>
    <table>
      <tr><th>ID</th><th>Nombre</th><th>Ciudad</th><th>Dirección</th><th>Acciones</th></tr>
      <?php foreach ($cines as $c): ?>
        <tr>
          <td><?= (int)$c['id'] ?></td>
          <td><?= htmlspecialchars($c['nombre']) ?></td>
          <td><?= htmlspecialchars($c['ciudad']) ?></td>
          <td><?= htmlspecialchars($c['direccion'] ?? '') ?></td>
          <td>
            <form method="post" style="display:inline-block;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button type="submit" onclick="return confirm('¿Eliminar?')">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
