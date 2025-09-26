<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use function App\Helpers\requireRole;
use App\Models\Pelicula;

requireRole('ADMIN');

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            Pelicula::create(trim($_POST['titulo'] ?? ''), $_POST['descripcion'] ?? null, $_POST['duracion']!==''?(int)$_POST['duracion']:null, $_POST['clasificacion'] ?? null);
        } elseif ($action === 'update') {
            Pelicula::update((int)$_POST['id'], trim($_POST['titulo'] ?? ''), $_POST['descripcion'] ?? null, $_POST['duracion']!==''?(int)$_POST['duracion']:null, $_POST['clasificacion'] ?? null);
        } elseif ($action === 'delete') {
            Pelicula::delete((int)$_POST['id']);
        }
        header('Location: peliculas.php');
        exit;
    } catch (Throwable $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$peliculas = Pelicula::listAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Películas</title>
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
    <h1>Películas</h1>
    <p><a href="../catalogo.php">Volver al sitio</a> | <a href="cines.php">Cines</a> | <a href="salas.php">Salas</a> | <a href="funciones.php">Funciones</a></p>
    <?php if ($error): ?><div style="color:#b00020;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <h2>Nueva</h2>
    <form method="post">
      <input type="hidden" name="action" value="create">
      <input name="titulo" placeholder="Título" required>
      <input name="clasificacion" placeholder="Clasificación">
      <input name="duracion" type="number" placeholder="Duración (min)">
      <br>
      <textarea name="descripcion" placeholder="Descripción" rows="2" style="width:100%;"></textarea>
      <br>
      <button type="submit">Guardar</button>
    </form>
    <h2>Listado</h2>
    <table>
      <tr><th>ID</th><th>Título</th><th>Clasificación</th><th>Duración</th><th>Acciones</th></tr>
      <?php foreach ($peliculas as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= htmlspecialchars($p['titulo']) ?></td>
          <td><?= htmlspecialchars($p['clasificacion'] ?? '') ?></td>
          <td><?= (int)($p['duracion'] ?? 0) ?></td>
          <td>
            <form method="post" style="display:inline-block;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" onclick="return confirm('¿Eliminar?')">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>

