<?php
namespace App\Controllers;

use App\Models\Usuario;
use function App\Helpers\loginUser;
use function App\Helpers\logoutUser;

class AuthController
{
    public function login(string $correo, string $contrasena): array
    {
        $correo = trim($correo);
        $contrasena = trim($contrasena);
        if ($correo === '' || $contrasena === '') {
            return ['ok' => false, 'error' => 'Correo y contraseña son requeridos'];
        }

        $user = Usuario::verifyPassword($correo, $contrasena);
        if (!$user) {
            return ['ok' => false, 'error' => 'Credenciales inválidas'];
        }
        loginUser([
            'id' => $user['id'],
            'rol' => $user['rol_nombre'],
            'nombre' => $user['nombre'],
            'correo' => $user['correo'],
        ]);
        return ['ok' => true, 'user' => $user];
    }

    public function register(string $nombre, string $correo, string $contrasena): array
    {
        $nombre = trim($nombre);
        $correo = trim($correo);
        $contrasena = trim($contrasena);

        if ($nombre === '' || $correo === '' || $contrasena === '') {
            return ['ok' => false, 'error' => 'Todos los campos son requeridos'];
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Correo inválido'];
        }
        if (strlen($contrasena) < 6) {
            return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        $existing = Usuario::findByEmail($correo);
        if ($existing) {
            return ['ok' => false, 'error' => 'El correo ya está registrado'];
        }

        $id = Usuario::create($nombre, $correo, $contrasena);
        $user = Usuario::findById($id);
        loginUser([
            'id' => $user['id'],
            'rol' => $user['rol_nombre'],
            'nombre' => $user['nombre'],
            'correo' => $user['correo'],
        ]);
        return ['ok' => true, 'user' => $user];
    }

    public function logout(): void
    {
        logoutUser();
    }
}

