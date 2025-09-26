<?php
namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail(string $to, string $subject, string $htmlBody, array $attachments = []): void
{
    $mail = new PHPMailer(true);
    try {
        // Config SMTP desde variables de entorno
        $smtpHost = getenv('SMTP_HOST') ?: '';
        $smtpUser = getenv('SMTP_USER') ?: '';
        $smtpPass = getenv('SMTP_PASS') ?: '';
        $smtpPort = getenv('SMTP_PORT') ?: '587';
        $smtpSecure = getenv('SMTP_SECURE') ?: 'tls';

        if ($smtpHost && $smtpUser && $smtpPass) {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = $smtpSecure;
            $mail->Port = (int)$smtpPort;
        } else {
            $mail->isMail(); // fallback
        }

        $from = getenv('MAIL_FROM') ?: 'no-reply@ticketera.local';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Ticketera';
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        foreach ($attachments as $path) {
            if (is_file($path)) {
                $mail->addAttachment($path);
            }
        }

        $mail->send();
    } catch (Exception $e) {
        // En sandbox, ignorar errores de correo
    }
}
