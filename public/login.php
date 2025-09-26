<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;
use function App\Helpers\isLoggedIn;
use function App\Helpers\currentUserRole;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new AuthController();
$error = null;

if (($_POST['action'] ?? '') === 'login') {
    $res = $auth->login($_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
    if ($res['ok']) {
        header('Location: catalogo.php');
        exit;
    } else {
        $error = $res['error'];
    }
}

if (($_POST['action'] ?? '') === 'register') {
    $res = $auth->register($_POST['nombre'] ?? '', $_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
    if ($res['ok']) {
        header('Location: catalogo.php');
        exit;
    } else {
        $error = $res['error'];
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Ticketera</title>
  <style>
    :root { color-scheme: light dark; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .container { max-width: 960px; margin: 0 auto; padding: 1rem; }
    .grid { display: grid; gap: 1rem; grid-template-columns: 1fr; }
    @media (min-width: 720px) { .grid { grid-template-columns: 1fr 1fr; } }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; }
    input, button { width: 100%; padding: .75rem; margin-top: .5rem; border-radius: 6px; border: 1px solid #999; }
    button { background: #0d6efd; color: white; border-color: #0d6efd; cursor: pointer; }
    .error { color: #b00020; margin: .5rem 0; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    a { color:#0d6efd; text-decoration:none; }
  </style>
  <script>
    function switchTab(tab) {
      document.getElementById('login').style.display = tab==='login'?'block':'none';
      document.getElementById('register').style.display = tab==='register'?'block':'none';
    }
  </script>
  <?php if (isLoggedIn()): ?>
    <meta http-equiv="refresh" content="0; url=catalogo.php">
  <?php endif; ?>
  </head>
<body>
  <div class="container">
    <div class="header">
      <h1>Ticketera</h1>
      <nav>
        <?php if (isLoggedIn()): ?>
          <a href="catalogo.php">Ir al Catálogo</a>
          <?php if (currentUserRole()==='AGENTE' || currentUserRole()==='ADMIN'): ?> | <a href="validar.php">Validar Tickets</a><?php endif; ?>
        <?php endif; ?>
      </nav>
    </div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="margin-bottom:1rem;">
      <button onclick="switchTab('login')">Iniciar Sesión</button>
      <button onclick="switchTab('register')">Registrarse</button>
    </div>

    <div class="grid">
      <div id="login" class="card" style="display:block;">
        <h2>Iniciar Sesión</h2>
        <form method="post">
          <input type="hidden" name="action" value="login" />
          <label>Correo electrónico</label>
          <input type="email" name="correo" required />
          <label>Contraseña</label>
          <input type="password" name="contrasena" required />
          <button type="submit">Entrar</button>
        </form>
      </div>

      <div id="register" class="card" style="display:none;">
        <h2>Registro</h2>
        <form method="post">
          <input type="hidden" name="action" value="register" />
          <label>Nombre</label>
          <input type="text" name="nombre" required />
          <label>Correo electrónico</label>
          <input type="email" name="correo" required />
          <label>Contraseña</label>
          <input type="password" name="contrasena" minlength="6" required />
          <button type="submit">Crear cuenta</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
