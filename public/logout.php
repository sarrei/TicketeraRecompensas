<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new AuthController();
$auth->logout();
header('Location: login.php');
exit;
