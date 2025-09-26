<?php
namespace App\Models;

use App\Config\DB;

class Pago
{
    public static function crear(int $compraId, string $metodo, string $referencia, float $monto, string $estado = 'PENDIENTE'): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO pagos (compra_id, metodo, referencia, monto, estado) VALUES (?,?,?,?,?)");
        $stmt->execute([$compraId, $metodo, $referencia, $monto, $estado]);
        return (int)$pdo->lastInsertId();
    }

    public static function actualizarEstado(int $pagoId, string $estado): void
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE pagos SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $pagoId]);
    }
}
