<?php
namespace App\Controllers;

use App\Models\Pelicula;

class PeliculasController
{
    public function index(?string $ciudad = null, ?int $cineId = null): array
    {
        $ciudad = $ciudad ? trim($ciudad) : null;
        $cineId = $cineId ? (int)$cineId : null;
        $peliculas = Pelicula::listWithFunciones($ciudad, $cineId);
        $ciudades = Pelicula::getCiudades();
        $cines = $ciudad ? Pelicula::getCinesPorCiudad($ciudad) : [];
        return [
            'peliculas' => $peliculas,
            'ciudades' => $ciudades,
            'cines' => $cines,
        ];
    }
}
