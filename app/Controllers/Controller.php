<?php

/**
 * Base Controller Class
 *
 * Provides common functionality for all controllers including
 * database access, JSON responses, and request validation.
 *
 * @package Keith\D6assesment\Controllers
 */

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Database;

/**
 * Abstract base controller class
 */
abstract class Controller
{
    /**
     * Database connection instance
     *
     * @var \PDO
     */
    protected $db;

    /**
     * Constructor - initializes database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Send a JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode HTTP status code (default: 200)
     * @return void
     */
    protected function jsonResponse($data, $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send an error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code (default: 400)
     * @return void
     */
    protected function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse(['error' => $message], $statusCode);
    }

    /**
     * Get JSON data from request body
     *
     * @return array Decoded JSON data or empty array
     */
    protected function getRequestData(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return $data ?? [];
    }

    /**
     * Validate that required fields are present in data
     *
     * @param array $data The data to validate
     * @param array $fields Array of required field names
     * @return array Array of missing field names
     */
    protected function validateRequired(array $data, array $fields): array
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
