<?php
namespace App\Models;

use App\Config\DB;

class Sala
{
    public static function listAll(): array
    {
        $pdo = DB::getConnection();
        $sql = "SELECT s.*, c.nombre AS cine_nombre, c.ciudad FROM salas s JOIN cines c ON c.id = s.cine_id ORDER BY c.ciudad, c.nombre, s.nombre";
        return $pdo->query($sql)->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("SELECT * FROM salas WHERE id = ?");
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(int $cineId, string $nombre, int $capacidad): int
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("INSERT INTO salas (cine_id, nombre, capacidad) VALUES (?,?,?)");
        $st->execute([$cineId, $nombre, $capacidad]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, int $cineId, string $nombre, int $capacidad): void
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("UPDATE salas SET cine_id=?, nombre=?, capacidad=? WHERE id = ?");
        $st->execute([$cineId, $nombre, $capacidad, $id]);
    }

    public static function delete(int $id): void
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("DELETE FROM salas WHERE id = ?");
        $st->execute([$id]);
    }
}

