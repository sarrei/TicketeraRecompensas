<?php
namespace App\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

function generateQR(string $code, string $text): ?string
{
    $dir = __DIR__ . '/../../public/qr';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $file = $dir . '/' . preg_replace('/[^A-Za-z0-9\-_.]/', '_', $code) . '.png';

    $options = new QROptions([
        'version'      => 5,
        'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'     => QRCode::ECC_L,
        'scale'        => 6,
    ]);
    (new QRCode($options))->render($text, $file);

    return file_exists($file) ? $file : null;
}
