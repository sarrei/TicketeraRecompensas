<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../vendor/autoload.php';
use function App\Helpers\isLoggedIn;
use function App\Helpers\currentUserRole;
$script = $_SERVER['PHP_SELF'] ?? '';
$isAdminPath = strpos($script, '/public/admin/') !== false;
$prefix = $isAdminPath ? '..' : '.';
?>
<header style="border-bottom:1px solid #ccc;">
  <div style="max-width:1200px;margin:0 auto;padding: .75rem 1rem;display:flex;align-items:center;justify-content:space-between;">
    <div>
      <a href="<?= $prefix ?>/index.php" style="text-decoration:none;color:inherit;"><strong>Ticketera</strong></a>
    </div>
    <nav style="display:flex;gap:1rem;align-items:center;">
      <?php if (isLoggedIn()): ?>
        <?php if (currentUserRole()==='CLIENTE'): ?>
          <a href="<?= $prefix ?>/catalogo.php">Catálogo</a>
          <a href="<?= $prefix ?>/perfil.php">Mi Perfil</a>
        <?php elseif (currentUserRole()==='AGENTE'): ?>
          <a href="<?= $prefix ?>/validar.php">Validar Tickets</a>
        <?php elseif (currentUserRole()==='ADMIN'): ?>
          <a href="<?= $prefix ?>/admin/peliculas.php">Películas</a>
          <a href="<?= $prefix ?>/admin/cines.php">Cines</a>
          <a href="<?= $prefix ?>/admin/salas.php">Salas</a>
          <a href="<?= $prefix ?>/admin/funciones.php">Funciones</a>
        <?php endif; ?>
        <a href="<?= $prefix ?>/logout.php" style="color:#dc3545;">Salir</a>
      <?php else: ?>
        <a href="<?= $prefix ?>/login.php">Entrar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
