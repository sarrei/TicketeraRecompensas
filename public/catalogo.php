<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\PeliculasController;
use App\Models\Pelicula;
use function App\Helpers\requireLogin;

use function App\Helpers\requireRoles;

requireRoles(['CLIENTE','ADMIN']);

$ciudad = isset($_GET['ciudad']) ? trim((string)$_GET['ciudad']) : null;
$cineId = isset($_GET['cine_id']) && $_GET['cine_id'] !== '' ? (int)$_GET['cine_id'] : null;

$controller = new PeliculasController();
$data = $controller->index($ciudad ?: null, $cineId ?: null);
$peliculas = $data['peliculas'];
$ciudades = $data['ciudades'];
$cines = $data['cines'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catálogo de Películas</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 1100px; margin: 0 auto; padding: 1rem; }
    .grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    @media (min-width: 800px) { .grid { grid-template-columns: 1fr 1fr; } }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    .row { display:flex; gap: .75rem; flex-wrap: wrap; }
    label { font-weight: 600; }
    select, button { padding: .5rem; border-radius: 6px; border:1px solid #999; }
    a.btn { display:inline-block; padding:.5rem .75rem; background:#0d6efd; color:#fff; border-radius:6px; text-decoration:none; }
    .muted { color:#666; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>
  <div class="container">
    <h1>Catálogo</h1>
    <form method="get" class="card" style="margin-bottom:1rem;">
      <div class="row">
        <div>
          <label>Ciudad</label><br>
          <select name="ciudad" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($ciudades as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>" <?= $ciudad===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>Cine</label><br>
          <select name="cine_id" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($cines as $cine): ?>
              <option value="<?= (int)$cine['id'] ?>" <?= ($cineId===(int)$cine['id'])?'selected':'' ?>><?= htmlspecialchars($cine['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="align-self:end;">
          <button type="submit">Filtrar</button>
        </div>
      </div>
    </form>

    <div class="grid">
      <?php foreach ($peliculas as $p): ?>
        <div class="card">
          <h2 style="margin:.25rem 0;"><?= htmlspecialchars($p['titulo']) ?></h2>
          <?php if (!empty($p['descripcion'])): ?>
            <p class="muted"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
          <?php endif; ?>
          <p class="muted">Clasificación: <?= htmlspecialchars($p['clasificacion'] ?? '-') ?> · Duración: <?= (int)($p['duracion'] ?? 0) ?> min</p>
          <h3 style="margin-top:1rem;">Funciones</h3>
          <?php if (!empty($p['funciones'])): ?>
            <ul>
              <?php foreach ($p['funciones'] as $f): ?>
                <li style="margin:.5rem 0;">
                  <strong><?= htmlspecialchars($f['cine_nombre']) ?></strong> (<?= htmlspecialchars($f['ciudad']) ?>) · 
                  Sala <?= htmlspecialchars($f['sala_nombre']) ?> · 
                  <?= date('d/m/Y H:i', strtotime($f['fecha_funcion'])) ?> · 
                  Q<?= number_format((float)$f['precio'], 2) ?>
                  &nbsp; <a class="btn" href="funcion.php?id=<?= (int)$f['id'] ?>">Seleccionar función</a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="muted">No hay funciones para los filtros seleccionados.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
