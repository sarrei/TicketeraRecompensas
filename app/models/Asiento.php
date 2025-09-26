<?php
namespace App\Models;

use App\Config\DB;

class Asiento
{
    public static function getBySala(int $salaId): array
    {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT id, fila, numero FROM asientos WHERE sala_id = ? ORDER BY fila ASC, numero ASC");
        $stmt->execute([$salaId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function getOcupadosPorFuncion(int $funcionId): array
    {
        $pdo = DB::getConnection();
        $sql = "SELECT ra.asiento_id
                FROM reservas_asientos ra
                JOIN compra_detalle cd ON cd.id = ra.compra_detalle_id
                WHERE cd.tipo = 'FUNCION' AND cd.funcion_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$funcionId]);
        return array_map('intval', array_column($stmt->fetchAll() ?: [], 'asiento_id'));
    }

    public static function checkDisponibilidad(int $funcionId, array $asientoIds): array
    {
        if (empty($asientoIds)) return [];
        $pdo = DB::getConnection();
        $in = implode(',', array_fill(0, count($asientoIds), '?'));
        $params = $asientoIds;
        array_unshift($params, $funcionId);
        $sql = "SELECT ra.asiento_id
                FROM reservas_asientos ra
                JOIN compra_detalle cd ON cd.id = ra.compra_detalle_id
                WHERE cd.tipo = 'FUNCION' AND cd.funcion_id = ?
                AND ra.asiento_id IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array_map('intval', array_column($stmt->fetchAll() ?: [], 'asiento_id'));
    }
}
