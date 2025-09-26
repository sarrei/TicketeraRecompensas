<?php
namespace App\Controllers;

use App\Models\Pago;
use App\Models\Usuario;
use App\Models\Ticket;
use App\Config\DB;
use function App\Helpers\generateQR;
use function App\Helpers\sendMail;

class PagosController
{
    public function validarInputs(string $numero, string $cvv, string $vencimiento): array
    {
        $numero = preg_replace('/\s+/', '', $numero);
        if (!preg_match('/^\d{16}$/', $numero)) {
            return [false, 'El número de tarjeta debe tener 16 dígitos'];
        }
        if (!preg_match('/^\d{3}$/', $cvv)) {
            return [false, 'El CVV debe tener 3 dígitos'];
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $vencimiento)) {
            return [false, 'El vencimiento debe ser MM/YY'];
        }
        // Validar no expirado
        [$mm, $yy] = explode('/', $vencimiento);
        $expMonth = (int)$mm;
        $expYear = 2000 + (int)$yy;
        $lastDay = (int)date('t', strtotime("$expYear-$expMonth-01"));
        $expTs = strtotime("$expYear-$expMonth-$lastDay 23:59:59");
        if ($expTs < time()) {
            return [false, 'La tarjeta está vencida'];
        }
        return [true, null];
    }

    public function procesarPago(int $usuarioId, int $compraId, string $numero, string $cvv, string $vencimiento): array
    {
        // Validación
        [$ok, $msg] = $this->validarInputs($numero, $cvv, $vencimiento);
        if (!$ok) return ['ok' => false, 'error' => $msg];

        $pdo = DB::getConnection();
        // Verificar compra pertenece al usuario y está pendiente
        $stmt = $pdo->prepare("SELECT * FROM compras WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$compraId, $usuarioId]);
        $compra = $stmt->fetch();
        if (!$compra) return ['ok' => false, 'error' => 'Compra no encontrada'];
        if (($compra['estado'] ?? '') !== 'PENDIENTE') return ['ok' => false, 'error' => 'La compra no está pendiente'];

        $monto = (float)$compra['precio_total'];
        $pdo->beginTransaction();
        try {
            // Crear pago PENDIENTE (simulación STRIPE)
            $ref = substr(hash('sha256', $numero . $vencimiento . microtime(true)), 0, 18);
            $pagoId = Pago::crear($compraId, 'STRIPE', $ref, $monto, 'PENDIENTE');

            // Simulación: aprobar automáticamente
            Pago::actualizarEstado($pagoId, 'APROBADO');

            // Marcar compra como PAGADO
            $upd = $pdo->prepare("UPDATE compras SET estado = 'PAGADO' WHERE id = ?");
            $upd->execute([$compraId]);

            // Ajuste de puntos: canje y acumulación
            $puntosUsados = (int)$compra['puntos_usados'];
            if ($puntosUsados > 0) {
                Usuario::ajustarPuntos($usuarioId, -$puntosUsados);
                $pm = $pdo->prepare("INSERT INTO puntos_movimientos (usuario_id, compra_id, tipo, puntos) VALUES (?,?, 'CANJE', ?)");
                $pm->execute([$usuarioId, $compraId, $puntosUsados]);
            }
            $puntosAcum = (int)floor($monto / 10); // Q100 => 10 pts
            if ($puntosAcum > 0) {
                Usuario::ajustarPuntos($usuarioId, $puntosAcum);
                $pm2 = $pdo->prepare("INSERT INTO puntos_movimientos (usuario_id, compra_id, tipo, puntos) VALUES (?,?, 'ACUMULACION', ?)");
                $pm2->execute([$usuarioId, $compraId, $puntosAcum]);
            }

            // Generar tickets con QR por asiento reservado
            $detalles = $pdo->prepare("SELECT id FROM compra_detalle WHERE compra_id = ?");
            $detalles->execute([$compraId]);
            $ticketFiles = [];
            while ($d = $detalles->fetch()) {
                $detalleId = (int)$d['id'];
                // por cada reserva de asiento, crear ticket
                $res = $pdo->prepare("SELECT asiento_id FROM reservas_asientos WHERE compra_detalle_id = ?");
                $res->execute([$detalleId]);
                $asientos = $res->fetchAll();
                foreach ($asientos as $a) {
                    $code = bin2hex(random_bytes(8)) . '-' . $compraId . '-' . $detalleId;
                    $ticketId = Ticket::crear($detalleId, $code);
                    $payload = json_encode(['ticket_id' => $ticketId, 'code' => $code], JSON_UNESCAPED_SLASHES);
                    $file = generateQR($code, $payload);
                    if ($file) { $ticketFiles[] = $file; }
                }
            }

            // Enviar email con tickets
            $usuario = Usuario::findById($usuarioId);
            $correo = $usuario['correo'] ?? null;
            if ($correo) {
                $subject = 'Compra exitosa - Tickets';
                $body = '<p>Gracias por tu compra.</p><p>Adjuntamos tus tickets y QR.</p>';
                try { sendMail($correo, $subject, $body, $ticketFiles); } catch (\Throwable $e) { /* ignorar en sandbox */ }
            }

            $pdo->commit();
            return ['ok' => true, 'pago_id' => $pagoId];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'No fue posible procesar el pago'];
        }
    }
}
