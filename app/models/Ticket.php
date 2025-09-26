<?php
namespace App\Models;

use App\Config\DB;

class Ticket
{
    public static function crear(int $compraDetalleId, string $codigo): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO tickets (compra_detalle_id, codigo_qr) VALUES (?,?)");
        $stmt->execute([$compraDetalleId, $codigo]);
        return (int)$pdo->lastInsertId();
    }

    public static function obtenerPorCompra(int $compraId): array
    {
        $pdo = DB::getConnection();
        $sql = "SELECT t.* FROM tickets t JOIN compra_detalle cd ON cd.id = t.compra_detalle_id WHERE cd.compra_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$compraId]);
        return $stmt->fetchAll() ?: [];
    }
}
