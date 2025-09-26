<?php
namespace App\Models;

use App\Config\DB;
use PDO;

class Funcion
{
    public static function findDetail(int $id): ?array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT f.*, p.titulo, p.descripcion, p.duracion, p.clasificacion,
                                      s.nombre AS sala_nombre,
                                      s.id AS sala_id, c.id AS cine_id, c.nombre AS cine_nombre, c.ciudad
                               FROM funciones f
                               JOIN peliculas p ON p.id = f.pelicula_id
                               JOIN salas s ON s.id = f.sala_id
                               JOIN cines c ON c.id = s.cine_id
                               WHERE f.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function countAsientosTotalesPorSala(int $salaId): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM asientos WHERE sala_id = ?");
        $stmt->execute([$salaId]);
        $row = $stmt->fetch();
        $total = (int)($row['total'] ?? 0);
        if ($total === 0) {
            // Fallback a capacidad declarada si no hay asientos cargados
            $stmt2 = $pdo->prepare("SELECT capacidad FROM salas WHERE id = ?");
            $stmt2->execute([$salaId]);
            $row2 = $stmt2->fetch();
            $total = (int)($row2['capacidad'] ?? 0);
        }
        return $total;
    }

    public static function countAsientosReservados(int $funcionId): int
    {
        $pdo = DB::getConnection();
        $sql = "SELECT COUNT(*) AS usados
                FROM reservas_asientos ra
                JOIN compra_detalle cd ON cd.id = ra.compra_detalle_id
                WHERE cd.tipo = 'FUNCION' AND cd.funcion_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$funcionId]);
        $row = $stmt->fetch();
        return (int)($row['usados'] ?? 0);
    }

    public static function getDisponibles(int $funcionId): int
    {
        $detalle = self::findDetail($funcionId);
        if (!$detalle) return 0;
        $total = self::countAsientosTotalesPorSala((int)$detalle['sala_id']);
        $usados = self::countAsientosReservados($funcionId);
        $disponibles = max(0, $total - $usados);
        return $disponibles;
    }
}
