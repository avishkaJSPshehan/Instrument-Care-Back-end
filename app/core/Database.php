<?php
namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private PDO $pdo;

    public function __construct(array $cfg)
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
            $cfg['host'], $cfg['name'], $cfg['charset'] ?? 'utf8mb4'
        );

        $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
