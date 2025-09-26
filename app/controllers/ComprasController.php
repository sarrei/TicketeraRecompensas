<?php
namespace App\Controllers;

use App\Models\Compra;
use App\Models\Usuario;
use App\Models\Funcion;
use App\Models\Asiento;

class ComprasController
{
    public function resumen(array $carrito, int $usuarioId): array
    {
        if (($carrito['tipo'] ?? '') !== 'FUNCION') {
            throw new \RuntimeException('Carrito inválido');
        }
        $funcion = Funcion::findDetail((int)$carrito['funcion_id']);
        if (!$funcion) throw new \RuntimeException('Función no encontrada');
        $cantidad = count($carrito['asientos'] ?? []);
        $precioUnit = (float)$funcion['precio'];
        $subtotal = $cantidad * $precioUnit;
        $usuario = Usuario::findById($usuarioId);
        $puntos = (int)($usuario['puntos'] ?? 0);

        // Política simple: 1 punto = Q1; canje habilitado si puntos >= 100
        $valorPuntoQ = 1.0;
        $calculaDescuento = function (bool $usar) use ($puntos, $subtotal, $valorPuntoQ): array {
            $maxDescuento = min($subtotal, $puntos * $valorPuntoQ);
            $puntosUsados = 0;
            $descuento = 0.0;
            if ($usar && $puntos >= 100) {
                $descuento = $maxDescuento;
                $puntosUsados = (int)min($puntos, (int)floor($descuento / $valorPuntoQ));
            }
            return [$descuento, $puntosUsados];
        };

        return [
            'funcion' => $funcion,
            'cantidad' => $cantidad,
            'precio_unit' => $precioUnit,
            'subtotal' => $subtotal,
            'usuario' => $usuario,
            'puntos' => $puntos,
            'valor_punto_q' => $valorPuntoQ,
            'calcula_descuento' => $calculaDescuento,
        ];
    }

    public function confirmar(int $usuarioId, array $carrito, bool $usarPuntos): array
    {
        if (($carrito['tipo'] ?? '') !== 'FUNCION') {
            return ['ok' => false, 'error' => 'Carrito inválido'];
        }
        $funcion = Funcion::findDetail((int)$carrito['funcion_id']);
        if (!$funcion) return ['ok' => false, 'error' => 'Función no encontrada'];
        $asientos = array_map('intval', $carrito['asientos'] ?? []);
        if (empty($asientos)) return ['ok' => false, 'error' => 'Sin asientos seleccionados'];

        // Verificar disponibilidad antes de reservar
        $conflictos = Asiento::checkDisponibilidad((int)$carrito['funcion_id'], $asientos);
        if (!empty($conflictos)) {
            return ['ok' => false, 'error' => 'Algunos asientos ya no están disponibles'];
        }

        $cantidad = count($asientos);
        $precioUnit = (float)$funcion['precio'];
        $subtotal = $cantidad * $precioUnit;

        $usuario = Usuario::findById($usuarioId);
        $puntos = (int)($usuario['puntos'] ?? 0);
        $valorPuntoQ = 1.0;
        $descuento = 0.0; $puntosUsados = 0;
        if ($usarPuntos && $puntos >= 100) {
            $maxDescuento = min($subtotal, $puntos * $valorPuntoQ);
            $descuento = $maxDescuento;
            $puntosUsados = (int)min($puntos, (int)floor($descuento / $valorPuntoQ));
        }
        $total = max(0.0, $subtotal - $descuento);

        $pdo = \App\Config\DB::getConnection();
        $pdo->beginTransaction();
        try {
            $compraId = Compra::crearPendiente($usuarioId, $total, $puntosUsados);
            $detalleId = Compra::agregarDetalleFuncion($compraId, (int)$carrito['funcion_id'], $cantidad, $subtotal);
            Compra::reservarAsientos($detalleId, $asientos);
            $pdo->commit();
            return ['ok' => true, 'compra_id' => $compraId];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'No fue posible crear la compra'];
        }
    }
}
