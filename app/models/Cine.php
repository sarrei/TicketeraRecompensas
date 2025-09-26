<?php
namespace App\Models;

use App\Config\DB;

class Cine
{
    public static function listAll(): array
    {
        $pdo = DB::getConnection();
        return $pdo->query("SELECT * FROM cines ORDER BY ciudad, nombre")->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("SELECT * FROM cines WHERE id = ?");
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function create(string $nombre, string $ciudad, ?string $direccion): int
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("INSERT INTO cines (nombre, ciudad, direccion) VALUES (?,?,?)");
        $st->execute([$nombre, $ciudad, $direccion]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, string $nombre, string $ciudad, ?string $direccion): void
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("UPDATE cines SET nombre=?, ciudad=?, direccion=? WHERE id = ?");
        $st->execute([$nombre, $ciudad, $direccion, $id]);
    }

    public static function delete(int $id): void
    {
        $pdo = DB::getConnection();
        $st = $pdo->prepare("DELETE FROM cines WHERE id = ?");
        $st->execute([$id]);
    }
}

