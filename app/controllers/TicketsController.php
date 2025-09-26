<?php
namespace App\Controllers;

use App\Models\Ticket;
use App\Config\DB;

class TicketsController
{
    public function validar(int $agenteId, ?string $code = null, ?int $ticketId = null): array
    {
        $ticket = null;
        if ($ticketId) {
            $ticket = Ticket::findById($ticketId);
        } elseif ($code) {
            $ticket = Ticket::findByCode($code);
        }
        if (!$ticket) {
            return ['ok' => false, 'error' => 'Ticket no encontrado'];
        }
        if ((int)$ticket['validado'] === 1) {
            return ['ok' => false, 'error' => 'Ticket ya validado anteriormente'];
        }

        // Verificar compra PAGADA
        $pdo = DB::getConnection();
        $sql = "SELECT c.estado
                FROM tickets t
                JOIN compra_detalle cd ON cd.id = t.compra_detalle_id
                JOIN compras c ON c.id = cd.compra_id
                WHERE t.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([(int)$ticket['id']]);
        $row = $stmt->fetch();
        if (!$row || ($row['estado'] ?? '') !== 'PAGADO') {
            return ['ok' => false, 'error' => 'La compra no está pagada'];
        }

        // Detalles adicionales
        $info = null;
        $code = (string)$ticket['codigo_qr'];
        $parts = explode('-', $code);
        $asientoId = null;
        if (count($parts) >= 4) {
            $asientoId = (int)end($parts);
        }
        $q = "SELECT p.titulo, f.fecha_funcion, f.precio, s.nombre AS sala_nombre, c.nombre AS cine_nombre, c.ciudad,
                     co.usuario_id
              FROM tickets t
              JOIN compra_detalle cd ON cd.id = t.compra_detalle_id
              JOIN compras co ON co.id = cd.compra_id
              JOIN funciones f ON f.id = cd.funcion_id
              JOIN salas s ON s.id = f.sala_id
              JOIN cines c ON c.id = s.cine_id
              JOIN peliculas p ON p.id = f.pelicula_id
              WHERE t.id = ?";
        $st = $pdo->prepare($q);
        $st->execute([(int)$ticket['id']]);
        $info = $st->fetch() ?: null;
        $asiento = null;
        if ($asientoId) {
            $st2 = $pdo->prepare("SELECT fila, numero FROM asientos WHERE id = ?");
            $st2->execute([$asientoId]);
            $asiento = $st2->fetch() ?: null;
        }

        Ticket::marcarValidado((int)$ticket['id'], $agenteId);
        return ['ok' => true, 'ticket' => $ticket, 'info' => $info, 'asiento' => $asiento];
    }
}
