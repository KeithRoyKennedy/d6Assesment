<?php

/**
 * Invoice Item Model
 *
 * Handles database operations for the invoice_items table.
 * Provides methods for managing line items associated with invoices,
 * including bulk operations and item joins.
 *
 * @package Keith\D6assesment\Models
 */

namespace Keith\D6assesment\Models;

use PDO;

/**
 * Invoice item model class
 */
class InvoiceItem extends Model
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $table = 'invoice_items';

    /**
     * Create a new invoice item record
     *
     * @param array $data Invoice item data (invoice_id, item_id, description, quantity, etc.)
     * @return string Last insert ID
     */
    public function create(array $data): string
    {
        return $this->insert($data);
    }

    /**
     * Update an existing invoice item record
     *
     * @param int $id Invoice item ID
     * @param array $data Invoice item data to update
     * @return bool True on success, false on failure
     */
    public function updateInvoiceItem(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Find all line items for a specific invoice with item details joined
     *
     * @param int $invoiceId Invoice ID
     * @return array Array of invoice items with item codes and descriptions
     */
    public function findByInvoiceId(int $invoiceId): array
    {
        $sql = "SELECT ii.*, i.code as item_code, i.description as item_description
                FROM {$this->table} ii
                LEFT JOIN items i ON ii.item_id = i.id
                WHERE ii.invoice_id = :invoice_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Delete all line items for a specific invoice
     *
     * Used when updating invoice items - deletes all existing items before inserting new ones
     *
     * @param int $invoiceId Invoice ID
     * @return bool True on success, false on failure
     */
    public function deleteByInvoiceId(int $invoiceId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE invoice_id = :invoice_id");
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Insert multiple invoice items in bulk
     *
     * @param array $items Array of invoice item data arrays
     * @return array Array of inserted IDs
     */
    public function bulkInsert(array $items): array
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $this->insert($item);
        }
        return $ids;
    }
}
