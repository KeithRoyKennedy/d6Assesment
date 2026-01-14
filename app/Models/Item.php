<?php

/**
 * Item Model
 *
 * Handles database operations for the items table.
 * Provides methods for item CRUD operations, code lookup, and search functionality.
 *
 * @package Keith\D6assesment\Models
 */

namespace Keith\D6assesment\Models;

use PDO;

/**
 * Item model class
 */
class Item extends Model
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $table = 'items';

    /**
     * Create a new item record
     *
     * @param array $data Item data (code, description, unit_price, tax_rate)
     * @return string Last insert ID
     */
    public function create(array $data): string
    {
        return $this->insert($data);
    }

    /**
     * Update an existing item record
     *
     * @param int $id Item ID
     * @param array $data Item data to update
     * @return bool True on success, false on failure
     */
    public function updateItem(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Find an item by its unique code
     *
     * @param string $code Item code
     * @return array|false Item data or false if not found
     */
    public function findByCode(string $code): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = :code");
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Search items by description using LIKE query
     *
     * @param string $description Search term for item description
     * @return array Array of matching item records
     */
    public function searchByDescription(string $description): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE description LIKE :description");
        $searchTerm = '%' . $description . '%';
        $stmt->bindParam(':description', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
