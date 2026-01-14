<?php

/**
 * Base Model Class
 *
 * Provides common database operations for all models including
 * CRUD operations and query building functionality.
 *
 * @package Keith\D6assesment\Models
 */

namespace Keith\D6assesment\Models;

use Keith\D6assesment\Database;
use PDO;

/**
 * Abstract base model class
 */
abstract class Model
{
    /**
     * Database connection instance
     *
     * @var \PDO
     */
    protected $db;

    /**
     * Database table name
     *
     * @var string
     */
    protected $table;

    /**
     * Constructor - initializes database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all records from the table
     *
     * @return array Array of all records
     */
    public function findAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find a single record by ID
     *
     * @param int $id Record ID
     * @return array|false Record data or false if not found
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Delete a record by ID
     *
     * @param int $id Record ID
     * @return bool True on success, false on failure
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Insert a new record into the table
     *
     * @param array $data Associative array of field names and values
     * @return string Last insert ID
     */
    protected function insert(array $data): string
    {
        $fields = array_keys($data);
        $values = array_values($data);

        $fieldList = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);

        $sql = "INSERT INTO {$this->table} ({$fieldList}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Update an existing record
     *
     * @param int $id Record ID
     * @param array $data Associative array of field names and values to update
     * @return bool True on success, false on failure
     */
    protected function update(int $id, array $data): bool
    {
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }

        $fieldList = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET {$fieldList} WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }
}
