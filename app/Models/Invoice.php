<?php

namespace Keith\D6assesment\Models;

use PDO;

class Invoice extends Model
{
    protected $table = 'invoices';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function updateInvoice($id, $data)
    {
        return $this->update($id, $data);
    }

    public function findWithCustomer($id)
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

    public function findAllWithCustomers()
    {
        $sql = "SELECT i.*, c.name as customer_name
                FROM {$this->table} i
                LEFT JOIN customers c ON i.customer_id = c.id
                ORDER BY i.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByInvoiceNumber($invoiceNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE invoice_number = :invoice_number");
        $stmt->bindParam(':invoice_number', $invoiceNumber, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getNextInvoiceNumber()
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
