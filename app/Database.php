<?php

/**
 * Database Class
 *
 * Singleton class for managing database connections using PDO.
 * Ensures only one database connection exists throughout the application lifecycle.
 *
 * @package Keith\D6assesment
 */

namespace Keith\D6assesment;

use PDO;
use PDOException;

/**
 * Database singleton class
 */
class Database
{
    /**
     * Singleton instance
     *
     * @var Database|null
     */
    private static $instance = null;

    /**
     * PDO database connection
     *
     * @var PDO
     */
    private $connection;

    /**
     * Private constructor to prevent direct instantiation
     *
     * Establishes PDO connection with MySQL database using credentials
     * from config constants (DB_HOST, DB_NAME, DB_USER, DB_PASS)
     *
     * @throws PDOException If database connection fails
     */
    private function __construct()
    {
        try {
            $host = DB_HOST;
            $dbname = DB_NAME;
            $username = DB_USER;
            $password = DB_PASS;

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the singleton instance
     *
     * Creates a new instance if one doesn't exist, otherwise returns existing instance
     *
     * @return Database Singleton database instance
     */
    public static function getInstance():database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object
     *
     * @return PDO Active database connection
     */
    public function getConnection():pdo
    {
        return $this->connection;
    }

    /**
     * Prevent cloning of the singleton instance
     *
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * Prevent unserialization of the singleton instance
     *
     * @throws \Exception Always throws exception to prevent unserialization
     * @return void
     */
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
