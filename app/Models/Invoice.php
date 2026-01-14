<?php

/**
 * Invoice Model
 *
 * Handles database operations for the invoices table.
 * Provides methods for invoice CRUD operations, customer joins,
 * status updates, and invoice number generation.
 *
 * @package Keith\D6assesment\Models
 */

namespace Keith\D6assesment\Models;

use PDO;

/**
 * Invoice model class
 */
class Invoice extends Model
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $table = 'invoices';

    /**
     * Create a new invoice record
     *
     * @param array $data Invoice data (invoice_number, customer_id, dates, totals, etc.)
     * @return string Last insert ID
     */
    public function create(array $data): string
    {
        return $this->insert($data);
    }

    /**
     * Update an existing invoice record
     *
     * @param int $id Invoice ID
     * @param array $data Invoice data to update
     * @return bool True on success, false on failure
     */
    public function updateInvoice(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Find an invoice by ID with customer information joined
     *
     * @param int $id Invoice ID
     * @return array|false Invoice with customer data or false if not found
     */
    public function findWithCustomer(int $id): array|false
    {
        $sql = "SELECT i.*, c.name as customer_name, c.email as customer_email, 
                       c.address as customer_address, c.phone as customer_phone
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE i.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get all invoices with customer names joined
     *
     * Orders by creation date descending (newest first)
     *
     * @return array Array of invoices with customer names
     */
    public function findAllWithCustomers(): array
    {
        $sql = "SELECT i.*, c.name as customer_name
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                ORDER BY i.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find an invoice by its unique invoice number
     *
     * @param string $invoiceNumber Invoice number (e.g., INV-0001)
     * @return array|false Invoice data or false if not found
     */
    public function findByInvoiceNumber(string $invoiceNumber): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE invoice_number = :invoice_number");
        $stmt->bindParam(':invoice_number', $invoiceNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Update the status of an invoice
     *
     * @param int $id Invoice ID
     * @param string $status New status (draft, sent, paid, overdue)
     * @return bool True on success, false on failure
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Generate the next sequential invoice number
     *
     * Format: INV-XXXX (e.g., INV-0001, INV-0002)
     * If no invoices exist, returns INV-0001
     *
     * @return string Next invoice number
     */
    public function getNextInvoiceNumber(): string
    {
        $stmt = $this->db->prepare("SELECT invoice_number FROM {$this->table} ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $lastInvoice = $stmt->fetch();

        if (!$lastInvoice) {
            return 'INV-0001';
        }

        $lastNumber = (int) substr($lastInvoice['invoice_number'], 4);
        $nextNumber = $lastNumber + 1;
        return 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
