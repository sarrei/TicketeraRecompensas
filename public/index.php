<?php
require_once __DIR__ . '/../vendor/autoload.php';

use function App\Helpers\isLoggedIn;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header('Location: catalogo.php');
} else {
    header('Location: login.php');
}
exit;
