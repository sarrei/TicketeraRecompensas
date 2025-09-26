<?php
namespace App\Models;

use App\Config\DB;

class Compra
{
    public static function crearPendiente(int $usuarioId, float $precioTotal, int $puntosUsados): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO compras (usuario_id, precio_total, puntos_usados, estado) VALUES (?,?,?, 'PENDIENTE')");
        $stmt->execute([$usuarioId, $precioTotal, $puntosUsados]);
        return (int)$pdo->lastInsertId();
    }

    public static function agregarDetalleFuncion(int $compraId, int $funcionId, int $cantidad, float $subtotal): int
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO compra_detalle (compra_id, tipo, funcion_id, cantidad, subtotal) VALUES (?,?,?, ?, ?)");
        $stmt->execute([$compraId, 'FUNCION', $funcionId, $cantidad, $subtotal]);
        return (int)$pdo->lastInsertId();
    }

    public static function reservarAsientos(int $compraDetalleId, array $asientoIds): void
    {
        if (empty($asientoIds)) return;
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("INSERT INTO reservas_asientos (compra_detalle_id, asiento_id) VALUES (?, ?)");
        foreach ($asientoIds as $aid) {
            $stmt->execute([$compraDetalleId, (int)$aid]);
        }
    }
}
