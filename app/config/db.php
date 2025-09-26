<?php
namespace App\Config;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $conn = null;

    public static function getConnection(): PDO
    {
        if (self::$conn instanceof PDO) {
            return self::$conn;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $name = getenv('DB_NAME') ?: 'ticketing_mvp_final';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$conn = new PDO($dsn, $user, $pass, $options);
            return self::$conn;
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
}

