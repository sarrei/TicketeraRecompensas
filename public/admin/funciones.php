<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use function App\Helpers\requireRole;
use App\Models\Funcion;
use App\Models\Pelicula;
use App\Models\Sala;

requireRole('ADMIN');

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            Funcion::create((int)$_POST['pelicula_id'], (int)$_POST['sala_id'], $_POST['fecha_funcion'], (float)$_POST['precio']);
        } elseif ($action === 'delete') {
            Funcion::delete((int)$_POST['id']);
        }
        header('Location: funciones.php');
        exit;
    } catch (Throwable $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$funciones = Funcion::listAll();
$peliculas = Pelicula::listAll();
$salas = Sala::listAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Funciones</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 1100px; margin: 0 auto; padding: 1rem; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ccc; padding:.5rem; }
    input, textarea, select, button { padding:.4rem; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../partials/header.php'; ?>
  <div class="container">
    <h1>Funciones</h1>
    <p><a href="../catalogo.php">Volver al sitio</a> | <a href="peliculas.php">Películas</a> | <a href="cines.php">Cines</a> | <a href="salas.php">Salas</a></p>
    <?php if ($error): ?><div style="color:#b00020;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <h2>Nueva</h2>
    <form method="post">
      <input type="hidden" name="action" value="create">
      <select name="pelicula_id" required>
        <option value="">Seleccione película</option>
        <?php foreach ($peliculas as $p): ?>
          <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="sala_id" required>
        <option value="">Seleccione sala</option>
        <?php foreach ($salas as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['cine_nombre'].' - '.$s['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="datetime-local" name="fecha_funcion" required>
      <input type="number" step="0.01" name="precio" placeholder="Precio" required>
      <button type="submit">Guardar</button>
    </form>
    <h2>Listado</h2>
    <table>
      <tr><th>ID</th><th>Película</th><th>Cine</th><th>Sala</th><th>Fecha/Hora</th><th>Precio</th><th>Acciones</th></tr>
      <?php foreach ($funciones as $f): ?>
        <tr>
          <td><?= (int)$f['id'] ?></td>
          <td><?= htmlspecialchars($f['titulo']) ?></td>
          <td><?= htmlspecialchars($f['cine_nombre'].' ('.$f['ciudad'].')') ?></td>
          <td><?= htmlspecialchars($f['sala_nombre']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($f['fecha_funcion'])) ?></td>
          <td>Q<?= number_format((float)$f['precio'],2) ?></td>
          <td>
            <form method="post" style="display:inline-block;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
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
