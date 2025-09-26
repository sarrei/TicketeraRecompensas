<?php
namespace App\Models;

use App\Config\DB;
use PDO;

class Pelicula
{
    public static function listAll(): array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->query("SELECT * FROM peliculas ORDER BY creado_en DESC, id DESC");
        return $stmt->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("SELECT * FROM peliculas WHERE id = ?");
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(string $titulo, ?string $descripcion, ?int $duracion, ?string $clasificacion): int
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("INSERT INTO peliculas (titulo, descripcion, duracion, clasificacion) VALUES (?,?,?,?)");
        $st->execute([$titulo, $descripcion, $duracion, $clasificacion]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, string $titulo, ?string $descripcion, ?int $duracion, ?string $clasificacion): void
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("UPDATE peliculas SET titulo=?, descripcion=?, duracion=?, clasificacion=? WHERE id = ?");
        $st->execute([$titulo, $descripcion, $duracion, $clasificacion, $id]);
    }

    public static function delete(int $id): void
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("DELETE FROM peliculas WHERE id = ?");
        $st->execute([$id]);
    }

    public static function getCiudades(): array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->query("SELECT DISTINCT ciudad FROM cines ORDER BY ciudad ASC");
        return array_column($stmt->fetchAll() ?: [], 'ciudad');
    }

    public static function getCinesPorCiudad(string $ciudad): array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT id, nombre, ciudad FROM cines WHERE ciudad = ? ORDER BY nombre ASC");
        $stmt->execute([$ciudad]);
        return $stmt->fetchAll() ?: [];
    }

    public static function getFuncionesPorPelicula(int $peliculaId, ?string $ciudad = null, ?int $cineId = null): array
    {
        $pdo = DB::getConnection();
        $sql = "SELECT f.id, f.fecha_funcion, f.precio,
                       p.titulo,
                       s.id AS sala_id, s.nombre AS sala_nombre,
                       c.id AS cine_id, c.nombre AS cine_nombre, c.ciudad
                FROM funciones f
                JOIN peliculas p ON p.id = f.pelicula_id
                JOIN salas s ON s.id = f.sala_id
                JOIN cines c ON c.id = s.cine_id
                WHERE f.pelicula_id = :peliculaId";
        $params = ['peliculaId' => $peliculaId];
        if ($ciudad) {
            $sql .= " AND c.ciudad = :ciudad";
            $params['ciudad'] = $ciudad;
        }
        if ($cineId) {
            $sql .= " AND c.id = :cineId";
            $params['cineId'] = $cineId;
        }
        $sql .= " ORDER BY f.fecha_funcion ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function listWithFunciones(?string $ciudad = null, ?int $cineId = null): array
    {
        $peliculas = self::listAll();
        foreach ($peliculas as &$p) {
            $p['funciones'] = self::getFuncionesPorPelicula((int)$p['id'], $ciudad, $cineId);
        }
        return $peliculas;
    }
}
