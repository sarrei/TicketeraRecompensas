<?php
namespace App\Models;

use App\Config\DB;
use PDO;

class Usuario
{
    public static function findById(int $id): ?array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre, n.nombre AS nivel_nombre
                                FROM usuarios u
                                JOIN roles r ON r.id = u.rol_id
                                JOIN niveles_fidelizacion n ON n.id = u.nivel_id
                                WHERE u.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $correo): ?array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre, n.nombre AS nivel_nombre
                                FROM usuarios u
                                JOIN roles r ON r.id = u.rol_id
                                JOIN niveles_fidelizacion n ON n.id = u.nivel_id
                                WHERE u.correo = ?");
        $stmt->execute([$correo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nombre, string $correo, string $contrasena): int
    {
        $pdo = DB::getConnection();
        $pdo->beginTransaction();
        try {
            $rolId = self::getRolId('CLIENTE');
            $nivelId = self::getNivelId('BRONCE');
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contrasena_hash, rol_id, nivel_id, puntos)
                                    VALUES (?,?,?,?,?,0)");
            $stmt->execute([$nombre, $correo, $hash, $rolId, $nivelId]);
            $id = (int)$pdo->lastInsertId();
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function verifyPassword(string $correo, string $contrasena): ?array
    {
        $user = self::findByEmail($correo);
        if (!$user) return null;
        if (!password_verify($contrasena, $user['contrasena_hash'])) return null;
        return $user;
    }

    public static function getRolId(string $nombreRol): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE nombre = ?");
        $stmt->execute([$nombreRol]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new \RuntimeException('Rol no encontrado: ' . $nombreRol);
        }
        return (int)$row['id'];
    }

    public static function getNivelId(string $nombreNivel): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM niveles_fidelizacion WHERE nombre = ?");
        $stmt->execute([$nombreNivel]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new \RuntimeException('Nivel no encontrado: ' . $nombreNivel);
        }
        return (int)$row['id'];
    }
}

