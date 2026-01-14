<?php

namespace Keith\D6assesment\Controllers;

use Keith\D6assesment\Database;

abstract class Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function errorResponse($message, $statusCode = 400)
    {
        $this->jsonResponse(['error' => $message], $statusCode);
    }

    protected function getRequestData()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return $data ?? [];
    }

    protected function validateRequired($data, $fields)
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
