<?php

namespace Keith\D6assesment\Models;

use PDO;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function updateInvoiceItem($id, $data)
    {
        return $this->update($id, $data);
    }

    public function findByInvoiceId($invoiceId)
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

    public function deleteByInvoiceId($invoiceId)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE invoice_id = :invoice_id");
        $stmt->bindParam(':invoice_id', $invoiceId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function bulkInsert($items)
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $this->insert($item);
        }
        return $ids;
    }
}
