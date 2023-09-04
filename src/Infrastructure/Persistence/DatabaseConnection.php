<?php

namespace App\Infrastructure\Persistence;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class DatabaseConnection
{
    /**
     * Connection
     *
     * @var PDO
     */
    protected PDO $conn;

    /**
     * PgConnection constructor.
     *
     * @param LoggerInterface $logger
     */
    function __construct(private readonly LoggerInterface $logger)
    {
        $conn_string = sprintf(
            "mysql:host=%s;port=%d;dbname=%s",
            DATABASE_HOST,
            DATABASE_PORT,
            DATABASE_NAME
        );

        try{
            $this->conn = new PDO($conn_string, DATABASE_USER, DATABASE_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch (PDOException $exception){
            $this->logger->error($exception->getMessage());
        }
    }

    public function runWithParams(string $query, $array = []): array|false
    {
        $this->logger->debug("Run query: " . $query);

        $stmt = $this->conn->prepare($query);
        $stmt->execute($array);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
