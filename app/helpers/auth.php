<?php
namespace App\Helpers;

use App\Models\Usuario;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_id']);
}

function currentUserId(): ?int
{
    return $_SESSION['usuario_id'] ?? null;
}

function currentUserRole(): ?string
{
    return $_SESSION['rol'] ?? null;
}

function loginUser(array $user): void
{
    $_SESSION['usuario_id'] = (int)$user['id'];
    $_SESSION['rol'] = $user['rol'] ?? $user['rol_nombre'] ?? null;
    $_SESSION['nombre'] = $user['nombre'] ?? null;
    $_SESSION['correo'] = $user['correo'] ?? null;
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(string $role, bool $allowAdmin = true): void
{
    requireLogin();
    $current = currentUserRole();
    if ($current === $role) {
        return;
    }
    if ($allowAdmin && $current === 'ADMIN') {
        return;
    }
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
}

function requireRoles(array $roles): void
{
    requireLogin();
    $current = currentUserRole();
    if (in_array($current, $roles, true)) {
        return;
    }
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
}

function isRole(string $role): bool
{
    return currentUserRole() === $role;
}
