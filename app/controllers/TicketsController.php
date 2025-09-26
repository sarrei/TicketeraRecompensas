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

        Ticket::marcarValidado((int)$ticket['id'], $agenteId);
        return ['ok' => true, 'ticket' => $ticket];
    }
}
