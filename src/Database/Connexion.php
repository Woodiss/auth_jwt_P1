<?php
namespace App\Database;

use PDO;
use RuntimeException;

final class Connexion
{
    private static ?PDO $pdo = null;
    private static bool $envLoaded = false;

    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        if (!self::$envLoaded) {
            self::loadEnv(self::projectRoot().'/.env');
            self::$envLoaded = true;
        }

        $driver  = $_ENV['DB_DRIVER']  ?? 'mysql';
        $host    = $_ENV['DB_HOST']    ?? '127.0.0.1';
        $port    = $_ENV['DB_PORT']    ?? '3306';
        $dbname  = $_ENV['DB_NAME']    ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
        $user    = $_ENV['DB_USER']    ?? '';
        $pass    = $_ENV['DB_PASS']    ?? '';

        if ($dbname === '') {
            throw new RuntimeException('DB_NAME manquant dans .env');
        }

        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=%s', $driver, $host, $port, $dbname, $charset);

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }

    private static function loadEnv(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException(".env introuvable à l’emplacement : $path");
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;

            // gère KEY=VALUE (VALUE peut contenir '=')
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            // enlève guillemets éventuels
            $v = trim($v, " \t\n\r\0\x0B\"'");
            $_ENV[$k] = $v;
            // Optionnel: putenv("$k=$v");
        }
    }

    private static function projectRoot(): string
    {
        // trouve le .ENV
        return dirname(__DIR__, 2);
    }
}
