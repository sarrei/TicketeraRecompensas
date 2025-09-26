<?php
namespace App\Controllers;

use App\Models\Funcion;

class FuncionesController
{
    public function show(int $id): ?array
    {
        $detalle = Funcion::findDetail($id);
        if (!$detalle) return null;
        $detalle['asientos_disponibles'] = Funcion::getDisponibles($id);
        return $detalle;
    }
}
