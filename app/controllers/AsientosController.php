<?php
namespace App\Controllers;

use App\Models\Asiento;
use App\Models\Funcion;

class AsientosController
{
    public function grid(int $funcionId): array
    {
        $detalle = Funcion::findDetail($funcionId);
        if (!$detalle) {
            throw new \RuntimeException('Función no encontrada');
        }
        $salaId = (int)$detalle['sala_id'];
        $asientos = Asiento::getBySala($salaId);
        $ocupados = Asiento::getOcupadosPorFuncion($funcionId);

        // Estructura por filas
        $grid = [];
        foreach ($asientos as $a) {
            $fila = (string)$a['fila'];
            $grid[$fila] = $grid[$fila] ?? [];
            $grid[$fila][] = [
                'id' => (int)$a['id'],
                'numero' => (int)$a['numero'],
                'ocupado' => in_array((int)$a['id'], $ocupados, true),
            ];
        }
        // Ordenar cada fila por numero
        foreach ($grid as &$arr) {
            usort($arr, fn($x,$y)=>$x['numero']<=>$y['numero']);
        }
        ksort($grid, SORT_NATURAL);

        return [
            'detalle' => $detalle,
            'grid' => $grid,
            'hay_asientos' => !empty($asientos),
        ];
    }
}
