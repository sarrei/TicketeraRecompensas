<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use function App\Helpers\requireRole;
use App\Models\Sala;
use App\Models\Cine;

requireRole('ADMIN');

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            Sala::create((int)$_POST['cine_id'], trim($_POST['nombre'] ?? ''), (int)$_POST['capacidad']);
        } elseif ($action === 'delete') {
            Sala::delete((int)$_POST['id']);
        }
        header('Location: salas.php');
        exit;
    } catch (Throwable $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$salas = Sala::listAll();
$cines = Cine::listAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Salas</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 1000px; margin: 0 auto; padding: 1rem; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ccc; padding:.5rem; }
    input, textarea, select, button { padding:.4rem; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Salas</h1>
    <p><a href="../catalogo.php">Volver al sitio</a> | <a href="peliculas.php">Películas</a> | <a href="cines.php">Cines</a> | <a href="funciones.php">Funciones</a></p>
    <?php if ($error): ?><div style="color:#b00020;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <h2>Nueva</h2>
    <form method="post">
      <input type="hidden" name="action" value="create">
      <select name="cine_id" required>
        <option value="">Seleccione cine</option>
        <?php foreach ($cines as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre'].' ('.$c['ciudad'].')') ?></option>
        <?php endforeach; ?>
      </select>
      <input name="nombre" placeholder="Nombre" required>
      <input name="capacidad" type="number" placeholder="Capacidad" required>
      <button type="submit">Guardar</button>
    </form>
    <h2>Listado</h2>
    <table>
      <tr><th>ID</th><th>Cine</th><th>Nombre</th><th>Capacidad</th><th>Acciones</th></tr>
      <?php foreach ($salas as $s): ?>
        <tr>
          <td><?= (int)$s['id'] ?></td>
          <td><?= htmlspecialchars($s['cine_nombre'].' ('.$s['ciudad'].')') ?></td>
          <td><?= htmlspecialchars($s['nombre']) ?></td>
          <td><?= (int)$s['capacidad'] ?></td>
          <td>
            <form method="post" style="display:inline-block;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
              <button type="submit" onclick="return confirm('¿Eliminar?')">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>

