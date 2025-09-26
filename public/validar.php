<?php
require_once __DIR__ . '/../vendor/autoload.php';

use function App\Helpers\requireRole;

// Restringido a AGENTE (o ADMIN vía allowAdmin=true)
requireRole('AGENTE', true);
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
  </style>
  </head>
<body>
  <div class="container">
    <h1>Panel de Validación</h1>
    <p>Acceso permitido: AGENTE/ADMIN. La funcionalidad de escaneo y validación se implementará en Fase 9.</p>
  </div>
</body>
</html>
