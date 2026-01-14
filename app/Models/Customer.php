<?php

/**
 * Customer Model
 *
 * Handles database operations for the customers table.
 * Provides methods for customer CRUD operations and search functionality.
 *
 * @package Keith\D6assesment\Models
 */

namespace Keith\D6assesment\Models;

use PDO;

/**
 * Customer model class
 */
class Customer extends Model
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * Create a new customer record
     *
     * @param array $data Customer data (name, email, phone, address)
     * @return string Last insert ID
     */
    public function create(array $data): string
    {
        return $this->insert($data);
    }

    /**
     * Update an existing customer record
     *
     * @param int $id Customer ID
     * @param array $data Customer data to update
     * @return bool True on success, false on failure
     */
    public function updateCustomer(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Search customers by name using LIKE query
     *
     * @param string $name Search term for customer name
     * @return array Array of matching customer records
     */
    public function searchByName(string $name): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name LIKE :name");
        $searchTerm = '%' . $name . '%';
        $stmt->bindParam(':name', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
