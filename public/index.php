<?php
require_once __DIR__ . '/../vendor/autoload.php';

use function App\Helpers\isLoggedIn;
use function App\Helpers\currentUserRole;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    $role = currentUserRole();
    if ($role === 'AGENTE') {
        header('Location: validar.php');
    } else {
        header('Location: catalogo.php');
    }
} else {
    header('Location: login.php');
}
exit;
